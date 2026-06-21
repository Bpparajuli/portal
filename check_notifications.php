<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$old = DB::connection('mysql_old');
$new = DB::connection('mysql');

echo "=== OLD notifications columns ===\n";
foreach ($old->select('SHOW COLUMNS FROM notifications') as $c) {
    $c = (array)$c;
    echo "  {$c['Field']} | {$c['Type']} | Null={$c['Null']} | Default={$c['Default']}\n";
}

echo "\n=== NEW notifications columns ===\n";
foreach ($new->select('SHOW COLUMNS FROM notifications') as $c) {
    $c = (array)$c;
    echo "  {$c['Field']} | {$c['Type']} | Null={$c['Null']} | Default={$c['Default']}\n";
}

echo "\n=== OLD user_statuses columns ===\n";
foreach ($old->select('SHOW COLUMNS FROM user_statuses') as $c) {
    $c = (array)$c;
    echo "  {$c['Field']} | {$c['Type']} | Null={$c['Null']} | Default={$c['Default']}\n";
}

echo "\n=== Check old FKs in portaldb ===\n";
$fks = $old->select("
    SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = 'portaldb'
      AND REFERENCED_TABLE_NAME IS NOT NULL
    ORDER BY TABLE_NAME, ORDINAL_POSITION
");
foreach ($fks as $fk) {
    echo "  {$fk->TABLE_NAME}.{$fk->COLUMN_NAME} -> {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
}

// Check all tables in old for any with is_log field
echo "\n=== All columns named 'is_log' across old tables ===\n";
$tables = array_column($old->select('SHOW TABLES'), 'Tables_in_portaldb');
foreach ($tables as $t) {
    $cols = array_column(array_map(fn($c) => (array)$c, $old->select("SHOW COLUMNS FROM `$t`")), 'Field');
    if (in_array('is_log', $cols)) {
        echo "  $t has is_log\n";
        $vals = $old->select("SELECT is_log, COUNT(*) cnt FROM `$t` GROUP BY is_log");
        foreach ($vals as $v) echo "    is_log={$v->is_log} count={$v->cnt}\n";
    }
}
