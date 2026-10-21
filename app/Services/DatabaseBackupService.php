<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

class DatabaseBackupService
{
    public function create(): string
    {
        $schema = DB::getSchemaBuilder();
        $tables = $schema->getTableListing(null, false);
        sort($tables);
        $snapshot = DB::transaction(function () use ($tables, $schema) {
            $data = [];
            foreach ($tables as $table) {
                $rows = [];
                foreach (DB::table($table)->cursor() as $row) {
                    // Base64 keeps binary database values intact without unserialization.
                    $rows[] = array_map(fn ($value) => is_string($value) ? ['base64' => base64_encode($value)] : $value, (array) $row);
                }
                $data[$table] = ['columns' => $schema->getColumnListing($table), 'rows' => $rows];
            }

            return $data;
        });
        $publicFiles = [];
        $publicRoot = storage_path('app/public');
        if (is_dir($publicRoot)) {
            foreach (File::allFiles($publicRoot) as $file) {
                if ($file->isLink()) {
                    continue;
                }
                $publicFiles[$file->getRelativePathname()] = base64_encode(File::get($file->getPathname()));
            }
        }
        $payload = json_encode([
            'format' => 1, 'created_at' => now()->toIso8601String(),
            'tables' => $snapshot, 'public_files' => $publicFiles,
        ], JSON_THROW_ON_ERROR);
        $directory = config('backups.path');
        File::ensureDirectoryExists($directory, 0700);
        $path = $directory.'/clinic-'.now('UTC')->format('Ymd-His').'-'.bin2hex(random_bytes(4)).'.backup';
        $encrypted = Crypt::encryptString($payload);
        if (File::put($path.'.tmp', $encrypted, true) === false) {
            throw new RuntimeException('Backup write failed.');
        }
        chmod($path.'.tmp', 0600);
        File::move($path.'.tmp', $path);
        $this->inspect($path);

        return $path;
    }

    public function inspect(string $path): array
    {
        if (! is_file($path) || is_link($path)) {
            throw new RuntimeException('Backup is not a regular file.');
        }
        $payload = json_decode(Crypt::decryptString(File::get($path)), true, 512, JSON_THROW_ON_ERROR);
        if (($payload['format'] ?? null) !== 1 || ! is_array($payload['tables'] ?? null) || ! is_array($payload['public_files'] ?? null)) {
            throw new RuntimeException('Invalid backup format.');
        }
        $schema = DB::getSchemaBuilder();
        $expected = $schema->getTableListing(null, false);
        $actual = array_keys($payload['tables']);
        sort($expected);
        sort($actual);
        if ($expected !== $actual) {
            throw new RuntimeException('Backup schema differs from this installation. Restore using the matching code and migrations.');
        }
        foreach ($payload['tables'] as $table => &$data) {
            $columns = $schema->getColumnListing($table);
            if ($columns !== ($data['columns'] ?? null) || ! is_array($data['rows'] ?? null)) {
                throw new RuntimeException('Backup columns differ for '.$table.'.');
            }
            foreach ($data['rows'] as &$row) {
                $keys = array_keys($row);
                $sortedColumns = $columns;
                sort($keys);
                sort($sortedColumns);
                if ($keys !== $sortedColumns) {
                    throw new RuntimeException('Invalid row in '.$table.'.');
                }
                foreach ($row as &$value) {
                    if (is_array($value)) {
                        if (array_keys($value) !== ['base64'] || ! is_string($value['base64']) || ($decoded = base64_decode($value['base64'], true)) === false) {
                            throw new RuntimeException('Invalid encoded database value.');
                        }
                        $value = $decoded;
                    }
                }
                unset($value);
            }
            unset($row);
        }
        unset($data);
        foreach ($payload['public_files'] as $name => $encoded) {
            if (! is_string($name) || str_contains($name, '..') || str_contains($name, '\\') || str_starts_with($name, '/') || ! is_string($encoded) || base64_decode($encoded, true) === false) {
                throw new RuntimeException('Invalid uploaded-file path or content.');
            }
        }

        return $payload;
    }

    public function restore(string $path): void
    {
        $payload = $this->inspect($path);
        $schema = DB::getSchemaBuilder();
        $oldFiles = [];
        try {
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('PRAGMA defer_foreign_keys = ON');
            }
            $schema->withoutForeignKeyConstraints(function () use ($payload, &$oldFiles, $schema) {
                DB::transaction(function () use ($payload, &$oldFiles, $schema) {
                    foreach (array_keys($payload['tables']) as $table) {
                        DB::table($table)->delete();
                    }
                    foreach ($payload['tables'] as $table => $data) {
                        foreach (array_chunk($data['rows'], 50) as $rows) {
                            DB::table($table)->insert($rows);
                        }
                        if (DB::table($table)->count() !== count($data['rows'])) {
                            throw new RuntimeException('Restored row count mismatch.');
                        }
                    }
                    // Verify referential integrity before committing, including on MySQL.
                    foreach (array_keys($payload['tables']) as $table) {
                        foreach ($schema->getForeignKeys($table) as $foreign) {
                            $query = DB::table($table.' as child');
                            foreach ($foreign['columns'] as $column) {
                                $query->whereNotNull('child.'.$column);
                            }
                            $missing = $query->whereNotExists(function ($parent) use ($foreign) {
                                $parent->selectRaw('1')->from($foreign['foreign_table'].' as parent');
                                foreach ($foreign['columns'] as $index => $column) {
                                    $parent->whereColumn('parent.'.$foreign['foreign_columns'][$index], 'child.'.$column);
                                }
                            })->exists();
                            if ($missing) {
                                throw new RuntimeException('Broken foreign key in '.$table.'.');
                            }
                        }
                    }
                    foreach ($payload['public_files'] as $name => $encoded) {
                        $target = storage_path('app/public/'.$name);
                        // Never follow filesystem links during a restore.
                        for ($part = $target; $part !== storage_path('app'); $part = dirname($part)) {
                            if (is_link($part)) {
                                throw new RuntimeException('Restore path contains a symbolic link.');
                            }
                        }
                        $oldFiles[$target] = is_file($target) ? File::get($target) : null;
                        File::ensureDirectoryExists(dirname($target));
                        if (File::put($target, base64_decode($encoded, true), true) === false) {
                            throw new RuntimeException('Could not restore uploaded file.');
                        }
                    }
                });
            });
        } catch (\Throwable $exception) {
            foreach ($oldFiles as $file => $contents) {
                if ($contents === null) {
                    File::delete($file);
                } else {
                    File::put($file, $contents);
                }
            }
            throw $exception;
        }
    }
}
