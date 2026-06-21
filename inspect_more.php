<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$old = DB::connection('mysql_old');
$new = DB::connection('mysql');

$tables = ['crm_tasks', 'student_notes', 'notifications', 'documents', 'revenues', 'activities', 'application_message', 'universities', 'courses'];

foreach ($tables as $table) {
    $oldCols = $old->select("SHOW COLUMNS FROM `$table`");
    $newCols = $new->select("SHOW COLUMNS FROM `$table`");

    echo "=== $table ===\n";

    // Build full maps
    $oldMap = [];
    $newMap = [];
    foreach ($oldCols as $c) { $c = (array)$c; $oldMap[$c['Field']] = $c; }
    foreach ($newCols as $c) { $c = (array)$c; $newMap[$c['Field']] = $c; }

    $oldNames = array_keys($oldMap);
    $newNames = array_keys($newMap);

    $onlyOld = array_diff($oldNames, $newNames);
    $onlyNew = array_diff($newNames, $oldNames);

    if ($onlyOld) echo "  OLD-ONLY: " . implode(', ', $onlyOld) . "\n";
    if ($onlyNew) echo "  NEW-ONLY: " . implode(', ', $onlyNew) . "\n";

    // Shared columns type diffs
    $shared = array_intersect($oldNames, $newNames);
    foreach ($shared as $f) {
        $diffs = [];
        foreach (['Type', 'Null', 'Default', 'Extra'] as $k) {
            $o = $oldMap[$f][$k] ?? '';
            $n = $newMap[$f][$k] ?? '';
            if ($o !== $n) {
                $diffs[] = "$k($o -> $n)";
            }
        }
        if ($diffs) echo "  $f: " . implode(' | ', $diffs) . "\n";
    }

    echo "  Rows: Old={$old->table($table)->count()} | New={$new->table($table)->count()}\n\n";
}

// Check foreign key structure in new DB
echo "=== Foreign Keys in NEW DB ===\n";
$fks = $new->select("
    SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = 'bpparaju_portaldb'
      AND REFERENCED_TABLE_NAME IS NOT NULL
    ORDER BY TABLE_NAME, ORDINAL_POSITION
");
foreach ($fks as $fk) {
    echo "  {$fk->TABLE_NAME}.{$fk->COLUMN_NAME} -> {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
}

// Check old crm_tasks for meta_data sample
echo "\n=== Sample old crm_tasks meta_data (first 3 non-null) ===\n";
$samples = $old->select("SELECT id, meta_data FROM crm_tasks WHERE meta_data IS NOT NULL LIMIT 3");
foreach ($samples as $s) echo "  id={$s->id}: meta_data=" . var_export($s->meta_data, true) . "\n";

echo "\n=== Sample new crm_tasks meta_data (first 3 non-null) ===\n";
$samples2 = $new->select("SELECT id, meta_data FROM crm_tasks WHERE meta_data IS NOT NULL LIMIT 3");
foreach ($samples2 as $s) echo "  id={$s->id}: meta_data=" . var_export($s->meta_data, true) . "\n";

// Check student_notes for is_log column
echo "\n=== Old student_notes: is_log values ===\n";
$logCounts = $old->select("SELECT is_log, COUNT(*) cnt FROM student_notes GROUP BY is_log");
foreach ($logCounts as $r) echo "  is_log={$r->is_log} count={$r->cnt}\n";

echo "\n=== New student_notes: is_log values ===\n";
$logCounts2 = $new->select("SELECT is_log, COUNT(*) cnt FROM student_notes GROUP BY is_log");
foreach ($logCounts2 as $r) echo "  is_log={$r->is_log} count={$r->cnt}\n";
