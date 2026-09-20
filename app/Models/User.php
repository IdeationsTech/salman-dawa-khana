<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'user_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = true;

    const UPDATED_AT = null;

    protected $fillable = [
        'clinic_id',
        'role_id',
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'clinic_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function recordedVisits()
    {
        return $this->hasMany(Visit::class, 'recorded_by_user_id', 'user_id');
    }

    public function createdPrescriptions()
    {
        return $this->hasMany(Prescription::class, 'created_by_user_id', 'user_id');
    }

    public function createdBills()
    {
        return $this->hasMany(Bill::class, 'created_by_user_id', 'user_id');
    }

    public function receivedPayments()
    {
        return $this->hasMany(Payment::class, 'received_by_user_id', 'user_id');
    }

    public function recordedExpenses()
    {
        return $this->hasMany(Expense::class, 'recorded_by_user_id', 'user_id');
    }

    public function ledgerEntries()
    {
        return $this->hasMany(LedgerEntry::class, 'created_by_user_id', 'user_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'user_id', 'user_id');
    }
}