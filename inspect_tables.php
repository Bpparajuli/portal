<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$old = DB::connection('mysql_old');
$new = DB::connection('mysql');

echo "=== OLD users columns ===\n";
foreach ($old->select('SHOW COLUMNS FROM users') as $c) {
    $c = (array)$c;
    echo "  {$c['Field']} | {$c['Type']} | Null={$c['Null']} | Default={$c['Default']}\n";
}

echo "\n=== NEW users columns ===\n";
foreach ($new->select('SHOW COLUMNS FROM users') as $c) {
    $c = (array)$c;
    echo "  {$c['Field']} | {$c['Type']} | Null={$c['Null']} | Default={$c['Default']}\n";
}

echo "\n=== OLD role distribution (is_admin, is_agent) ===\n";
$roles = $old->select('SELECT is_admin, is_agent, COUNT(*) cnt FROM users GROUP BY is_admin, is_agent');
foreach ($roles as $r) echo "  is_admin={$r->is_admin} is_agent={$r->is_agent} count={$r->cnt}\n";

echo "\n=== NEW role distribution ===\n";
$roles2 = $new->select('SELECT role, COUNT(*) cnt FROM users GROUP BY role');
foreach ($roles2 as $r) echo "  role={$r->role} count={$r->cnt}\n";

echo "\n=== OLD students columns ===\n";
foreach ($old->select('SHOW COLUMNS FROM students') as $c) {
    $c = (array)$c;
    echo "  {$c['Field']} | {$c['Type']} | Null={$c['Null']}\n";
}

echo "\n=== NEW students columns ===\n";
foreach ($new->select('SHOW COLUMNS FROM students') as $c) {
    $c = (array)$c;
    echo "  {$c['Field']} | {$c['Type']} | Null={$c['Null']}\n";
}

echo "\n=== Sample old users (first 3) ===\n";
$samples = $old->select('SELECT id, name, email, is_admin, is_agent, role, slug FROM users LIMIT 3');
foreach ($samples as $s) echo json_encode((array)$s, JSON_PRETTY_PRINT) . "\n";

echo "\n=== Sample new users (first 3) ===\n";
$samples2 = $new->select('SELECT id, name, email, role, slug FROM users LIMIT 3');
foreach ($samples2 as $s) echo json_encode((array)$s, JSON_PRETTY_PRINT) . "\n";

echo "\n=== Check old students table: is user_id present? ===\n";
$oldStudentCols = array_column(array_map(fn($c) => (array)$c, $old->select('SHOW COLUMNS FROM students')), 'Field');
echo "Old student columns: " . implode(', ', $oldStudentCols) . "\n";

$newStudentCols = array_column(array_map(fn($c) => (array)$c, $new->select('SHOW COLUMNS FROM students')), 'Field');
echo "New student columns: " . implode(', ', $newStudentCols) . "\n";
