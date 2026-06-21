<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$old = DB::connection('mysql_old');
$new = DB::connection('mysql');

$oldTables = array_column($old->select('SHOW TABLES'), 'Tables_in_portaldb');
$newTables = array_column($new->select('SHOW TABLES'), 'Tables_in_bpparaju_portaldb');

$common = array_intersect($oldTables, $newTables);
sort($common);

echo "=== DATABASE COMPARISON ===\n\n";

echo "OLD-ONLY TABLES:\n";
$onlyOld = array_diff($oldTables, $newTables);
foreach ($onlyOld as $t) echo "  - $t\n";
echo "\n";

echo "NEW-ONLY TABLES:\n";
$onlyNew = array_diff($newTables, $oldTables);
foreach ($onlyNew as $t) echo "  - $t\n";
echo "\n";

echo "COMMON TABLES (" . count($common) . "):\n";
echo implode(', ', $common) . "\n\n";

echo "=== COLUMN COMPARISON ===\n\n";

foreach ($common as $table) {
    if (in_array($table, ['cache', 'cache_locks', 'migrations', 'sessions'])) continue;

    $oldCols = $old->select("SHOW COLUMNS FROM `$table`");
    $newCols = $new->select("SHOW COLUMNS FROM `$table`");

    $oldNames = [];
    $newNames = [];
    $oldTypes = [];
    $newTypes = [];

    foreach ($oldCols as $c) { $c = (array)$c; $oldNames[] = $c['Field']; $oldTypes[$c['Field']] = $c; }
    foreach ($newCols as $c) { $c = (array)$c; $newNames[] = $c['Field']; $newTypes[$c['Field']] = $c; }

    $onlyOld = array_diff($oldNames, $newNames);
    $onlyNew = array_diff($newNames, $oldNames);

    echo "=== $table ===\n";
    echo "  Old: " . count($oldCols) . " cols | New: " . count($newCols) . " cols\n";

    if ($onlyOld) echo "  [OLD-ONLY] " . implode(', ', $onlyOld) . "\n";
    if ($onlyNew) echo "  [NEW-ONLY] " . implode(', ', $onlyNew) . "\n";

    // Compare types for shared columns
    $shared = array_intersect($oldNames, $newNames);
    foreach ($shared as $f) {
        $diffs = [];
        foreach (['Type', 'Null', 'Default', 'Extra', 'Key'] as $k) {
            $o = $oldTypes[$f][$k] ?? '';
            $n = $newTypes[$f][$k] ?? '';
            if ($o !== $n) {
                $diffs[] = "$k($o -> $n)";
            }
        }
        if ($diffs) echo "    $f: " . implode(' | ', $diffs) . "\n";
    }

    // Row counts
    $oldCount = $old->table($table)->count();
    $newCount = $new->table($table)->count();
    echo "  Rows: Old=$oldCount | New=$newCount\n";
    echo "\n";
}

echo "=== DATA TABLES TO MIGRATE ===\n\n";

$dataTables = array_diff($common, ['cache', 'cache_locks', 'migrations', 'sessions']);
echo "Will migrate data for:\n";
foreach ($dataTables as $t) {
    $oldCount = $old->table($t)->count();
    echo "  - $t ($oldCount rows)\n";
}
