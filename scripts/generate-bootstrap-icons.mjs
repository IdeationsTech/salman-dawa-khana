import { readdir, readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';

const dynamicIcons = [
    'speedometer2',
    'person-vcard',
    'calendar-check',
    'prescription',
    'bag-check',
    'cash-coin',
    'receipt',
    'bar-chart-line',
    'bell',
    'people',
    'clock-history',
    'gear',
    'chevron-double-right',
    'hospital',
    'box-arrow-right',
];

async function collectSourceFiles(directory) {
    const results = [];

    for (const entry of await readdir(directory, { withFileTypes: true })) {
        const fullPath = path.join(directory, entry.name);

        if (entry.isDirectory()) {
            results.push(...await collectSourceFiles(fullPath));
        } else if (
            entry.isFile()
            && (entry.name.endsWith('.blade.php') || entry.name.endsWith('.js'))
        ) {
            results.push(fullPath);
        }
    }

    return results;
}

const names = new Set(dynamicIcons);

for (const file of [
    ...await collectSourceFiles('resources/views'),
    ...await collectSourceFiles('resources/js'),
]) {
    const source = await readFile(file, 'utf8');

    for (const match of source.matchAll(/\bbi\s+bi-([a-z0-9-]+)/g)) {
        names.add(match[1]);
    }
}

const icons = {};

for (const name of [...names].sort()) {
    const file = path.join('node_modules/bootstrap-icons/icons', `${name}.svg`);
    const source = await readFile(file, 'utf8');
    const opening = source.match(/<svg[^>]*viewBox="([^"]+)"[^>]*>/);

    if (!opening) {
        throw new Error(`Could not read Bootstrap icon viewBox: ${name}`);
    }

    const contentStart = source.indexOf(opening[0]) + opening[0].length;
    const contentEnd = source.lastIndexOf('</svg>');

    if (contentEnd < contentStart) {
        throw new Error(`Could not read Bootstrap icon paths: ${name}`);
    }

    icons[name] = {
        viewBox: opening[1],
        markup: source.slice(contentStart, contentEnd).trim(),
    };
}

await writeFile(
    'resources/js/generated-bootstrap-icons.js',
    `export default ${JSON.stringify(icons)};\n`,
);

console.log(`Generated ${Object.keys(icons).length} Bootstrap SVG icons for mobile.`);
