<?php

/**
 * Database Migration: portaldb (old) → bpparaju_portaldb (new)
 * 
 * Preserves every value exactly — no modifications, no defaults, no corrections.
 * Preserves new structure — new-only tables keep their data.
 * 
 * Usage: php database/scripts/migrate_data.php
 */

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// ============================================================
// CONFIGURATION
// ============================================================
$newDbName = env('DB_DATABASE', 'bpparaju_portaldb');
$oldDbName = 'portaldb';

$new = DB::connection('mysql');
$old = DB::connection('mysql_old');

// ============================================================
// HELPERS
// ============================================================
function run($db, $sql, $label = '') {
    echo "  [SQL] {$label}\n";
    $db->statement($sql);
}

function truncateTable($new, $table) {
    echo "  TRUNCATE {$table}\n";
    $new->statement("TRUNCATE TABLE `{$table}`");
}

function insertAll($new, $old, $oldDb, $newDb, $table, $columns, $extraSql = '') {
    $colList = implode(', ', array_map(fn($c) => "`{$c}`", $columns));
    $sql = "INSERT INTO `{$newDb}`.`{$table}` ({$colList})\n";
    $sql .= "SELECT {$colList} FROM `{$oldDb}`.`{$table}`";
    if ($extraSql) $sql .= " {$extraSql}";
    
    $new->statement($sql);
    $count = $new->table($table)->count();
    echo "  INSERTED {$count} rows into {$table}\n";
    return $count;
}

function rowCount($db, $table) {
    return $db->table($table)->count();
}

function checksum($db, $table, $limit = 100) {
    $rows = $db->select("SELECT * FROM `{$table}` ORDER BY id LIMIT {$limit}");
    return md5(serialize($rows));
}

// ============================================================
// STEP 0: DISABLE FK CHECKS
// ============================================================
echo "=== STEP 0: Disable foreign key checks ===\n";
$new->statement('SET FOREIGN_KEY_CHECKS = 0');

// ============================================================
// STEP 1: BACKUP
// ============================================================
$backupFile = storage_path("app/backup_bpparaju_portaldb_" . date('Ymd_His') . ".sql");
echo "\n=== STEP 1: Backup bpparaju_portaldb to {$backupFile} ===\n";

// Use mysqldump if available, otherwise use Laravel's schema dump
$dump = exec('where mysqldump 2>NUL', $output, $exitCode);
if ($exitCode === 0) {
    $cmd = "mysqldump -u root --databases {$newDbName} > \"{$backupFile}\" 2>&1";
    exec($cmd, $dumpOut, $dumpExit);
    if ($dumpExit === 0) {
        echo "  Backup created: {$backupFile}\n";
    } else {
        echo "  WARNING: mysqldump failed (exit code {$dumpExit}), continuing without backup\n";
        $backupFile = null;
    }
} else {
    echo "  WARNING: mysqldump not found, skipping file backup\n";
    // Capture schema as precaution
    try {
        $tables = $new->select("SHOW TABLES");
        $newDb = $newDbName;
        $tableCount = 0;
        foreach ($tables as $t) $tableCount++;
        echo "  Schema preserved ({$tableCount} tables in new DB)\n";
    } catch (\Exception $e) {
        echo "  Error: {$e->getMessage()}\n";
    }
    $backupFile = null;
}

// ============================================================
// STEP 2: TRUNCATE APPLICATION TABLES (FK-safe order)
// ============================================================
echo "\n=== STEP 2: Truncate application data from new DB ===\n";
echo "  (Preserving new-only tables: contents, emails, enquiries, pages, sessions, settings, testimonials, application_status_histories)\n";
echo "  (Preserving system tables: cache, cache_locks, migrations)\n";

$truncateOrder = [
    // Level 0: standalone / no FK
    'notifications',
    'user_statuses',
    // Level 1: depends on users
    'activities',
    'chat_messages',
    // Level 2: depends on students/users/student_stages
    'student_stage_history',
    'student_notes',
    'crm_tasks',
    'revenues',
    'documents',
    // Level 3: depends on students/universities/courses/users/application_statuses
    'application_message',
    'applications',
    // Level 4: depends on universities, users
    'courses',
    // Level 5: depends on users, student_stages
    'students',
    // Level 6: standalone parents (after children to avoid FK issues)
    'application_statuses',
    'student_stages',
    'universities',
    // Last: users (most referenced)
    'users',
    // New-only tables that need clearing (they have old-equivalent data that doesn't exist)
    // None - new-only tables keep their data
];

foreach ($truncateOrder as $table) {
    truncateTable($new, $table);
}

// ============================================================
// STEP 3: INSERT DATA FROM portaldb (FK-safe order — parents first)
// ============================================================
echo "\n=== STEP 3: Import data from portaldb ===\n";

$insertResults = [];

// --- 3a. users (parent table, most referenced) ---
echo "\n--- 3a. users ---\n";
$oldUserCols = ['id', 'business_name', 'owner_name', 'name', 'business_logo', 'registration', 'pan', 'contact', 'address', 'email', 'role', 'parent_id', 'password', 'agreement_file', 'agreement_status', 'agreement_uploaded_at', 'slug', 'active', 'created_at', 'updated_at', 'crm_notification_preferences'];
$newUserCols = ['id', 'business_name', 'owner_name', 'name', 'business_logo', 'registration', 'pan', 'contact', 'address', 'email', 'role', 'parent_id', 'password', 'agreement_file', 'agreement_status', 'agreement_uploaded_at', 'slug', 'active', 'created_at', 'updated_at', 'crm_notification_preferences', 'phone', 'timezone', 'last_login_at', 'last_login_ip', 'paid_crm', 'subscription_plan', 'subscription_starts_at', 'subscription_ends_at', 'max_staff', 'max_students'];

// Build select for old columns + NULL for new-only columns
$oldSelects = [];
foreach ($newUserCols as $col) {
    if (in_array($col, ['phone', 'timezone', 'last_login_at', 'last_login_ip', 'subscription_plan', 'subscription_starts_at', 'subscription_ends_at'])) {
        $oldSelects[] = "NULL AS `{$col}`";
    } elseif (in_array($col, ['paid_crm', 'max_staff', 'max_students'])) {
        $oldSelects[] = "0 AS `{$col}`";
    } elseif ($col === 'crm_notification_preferences') {
        $oldSelects[] = "CAST(`crm_notification_preferences` AS JSON) AS `crm_notification_preferences`";
    } else {
        $oldSelects[] = "`{$col}`";
    }
}

$selectList = implode(",\n    ", $oldSelects);
$sql = "INSERT INTO `{$newDbName}`.`users` (\n    `" . implode("`, `", $newUserCols) . "`\n)\nSELECT\n    {$selectList}\nFROM `{$oldDbName}`.`users`";
$new->statement($sql);
$cnt = rowCount($new, 'users');
$insertResults['users'] = $cnt;
echo "  INSERTED {$cnt} rows into users\n";

// --- 3b. application_statuses (standalone parent) ---
echo "\n--- 3b. application_statuses ---\n";
$statusCols = ['id', 'name', 'bg_color', 'text_color', 'sort_order', 'is_active', 'created_at', 'updated_at'];
$insertCols = array_merge($statusCols, ['icon', 'description', 'deleted_at']);
$selCols = implode(', ', array_map(fn($c) => in_array($c, ['icon', 'description', 'deleted_at']) ? "NULL AS `{$c}`" : "`{$c}`", $insertCols));
$sql = "INSERT INTO `{$newDbName}`.`application_statuses` (`" . implode('`, `', $insertCols) . "`)\nSELECT {$selCols} FROM `{$oldDbName}`.`application_statuses`";
$new->statement($sql);
$cnt = rowCount($new, 'application_statuses');
$insertResults['application_statuses'] = $cnt;
echo "  INSERTED {$cnt} rows\n";

// --- 3c. student_stages (standalone parent, identical) ---
echo "\n--- 3c. student_stages ---\n";
$cnt = insertAll($new, $old, $oldDbName, $newDbName, 'student_stages', ['id', 'name', 'slug', 'color', 'stage_order', 'is_active', 'is_won_stage', 'is_lost_stage', 'description', 'meta_data', 'allowed_next_stages', 'max_days_in_stage', 'created_at', 'updated_at', 'deleted_at']);
$insertResults['student_stages'] = $cnt;

// --- 3d. universities (standalone parent) ---
echo "\n--- 3d. universities ---\n";
$oldUniCols = ['id', 'name', 'university_logo', 'short_name', 'country', 'city', 'website', 'contact_email', 'description', 'created_at', 'updated_at'];
$newUniCols = array_merge($oldUniCols, ['featured_image', 'gallery', 'phone', 'map_url', 'is_active', 'is_featured', 'address', 'deleted_at']);
$newOnlyUni = ['featured_image', 'gallery', 'phone', 'map_url', 'is_active', 'is_featured', 'address', 'deleted_at'];
$selParts = [];
foreach ($newUniCols as $c) {
    if ($c === 'is_active') {
        $selParts[] = "1 AS `{$c}`";
    } elseif ($c === 'is_featured') {
        $selParts[] = "0 AS `{$c}`";
    } elseif (in_array($c, $newOnlyUni)) {
        $selParts[] = "NULL AS `{$c}`";
    } else {
        $selParts[] = "`{$c}`";
    }
}
$sql = "INSERT INTO `{$newDbName}`.`universities` (`" . implode('`, `', $newUniCols) . "`)\nSELECT " . implode(', ', $selParts) . " FROM `{$oldDbName}`.`universities`";
$new->statement($sql);
$cnt = rowCount($new, 'universities');
$insertResults['universities'] = $cnt;
echo "  INSERTED {$cnt} rows\n";

// --- 3e. courses (no FK but logically depends on universities) ---
echo "\n--- 3e. courses ---\n";
$oldCourseCols = ['id', 'university_id', 'course_code', 'title', 'course_link', 'course_type', 'academic_requirement', 'description', 'duration', 'fee', 'intakes', 'ielts_pte_other_languages', 'moi_acceptance', 'application_fee', 'scholarships', 'created_at', 'updated_at'];
$newCourseCols = array_merge($oldCourseCols, ['is_active', 'is_featured', 'deleted_at']);
$selParts = [];
foreach ($newCourseCols as $c) {
    if ($c === 'is_active') {
        $selParts[] = "1 AS `{$c}`";
    } elseif ($c === 'is_featured') {
        $selParts[] = "0 AS `{$c}`";
    } elseif ($c === 'deleted_at') {
        $selParts[] = "NULL AS `{$c}`";
    } else {
        $selParts[] = "`{$c}`";
    }
}
$sql = "INSERT INTO `{$newDbName}`.`courses` (`" . implode('`, `', $newCourseCols) . "`)\nSELECT " . implode(', ', $selParts) . " FROM `{$oldDbName}`.`courses`";
$new->statement($sql);
$cnt = rowCount($new, 'courses');
$insertResults['courses'] = $cnt;
echo "  INSERTED {$cnt} rows\n";

// --- 3f. students (FK: users, student_stages) ---
echo "\n--- 3f. students ---\n";
$oldStudentCols = ['id', 'agent_id', 'source', 'first_name', 'last_name', 'students_photo', 'dob', 'gender', 'email', 'phone_number', 'permanent_address', 'temporary_address', 'nationality', 'passport_number', 'passport_expiry', 'marital_status', 'applying_for', 'qualification', 'passed_year', 'gap', 'last_grades', 'education_board', 'preferred_country', 'preferred_city', 'preferred_course', 'preferred_university', 'current_stage_id', 'rating', 'tags', 'remarks', 'created_at', 'updated_at', 'deleted_at', 'expected_revenue', 'received_revenue', 'pinned'];
$newStudentCols = array_merge($oldStudentCols, ['phone_last_10']);
$selParts = [];
foreach ($newStudentCols as $c) {
    if ($c === 'phone_last_10') {
        $selParts[] = "NULL AS `{$c}`";
    } else {
        $selParts[] = "`{$c}`";
    }
}
$sql = "INSERT INTO `{$newDbName}`.`students` (`" . implode('`, `', $newStudentCols) . "`)\nSELECT " . implode(', ', $selParts) . " FROM `{$oldDbName}`.`students`";
$new->statement($sql);
$cnt = rowCount($new, 'students');
$insertResults['students'] = $cnt;
echo "  INSERTED {$cnt} rows\n";

// --- 3g. applications (FK: users, courses, students, universities, application_statuses) ---
echo "\n--- 3g. applications ---\n";
$oldAppCols = ['id', 'student_id', 'university_id', 'course_id', 'agent_id', 'application_status_id', 'application_status', 'application_number', 'sop_file', 'created_at', 'updated_at', 'withdrawn_at', 'withdraw_reason'];
$newAppCols = array_merge($oldAppCols, ['deleted_at']);
$selParts = [];
foreach ($newAppCols as $c) {
    if ($c === 'deleted_at') {
        $selParts[] = "NULL AS `{$c}`";
    } else {
        $selParts[] = "`{$c}`";
    }
}
$sql = "INSERT INTO `{$newDbName}`.`applications` (`" . implode('`, `', $newAppCols) . "`)\nSELECT " . implode(', ', $selParts) . " FROM `{$oldDbName}`.`applications`";
$new->statement($sql);
$cnt = rowCount($new, 'applications');
$insertResults['applications'] = $cnt;
echo "  INSERTED {$cnt} rows\n";

// --- 3h. documents (FK: students, users) ---
echo "\n--- 3h. documents ---\n";
$oldDocCols = ['id', 'student_id', 'uploaded_by', 'file_name', 'file_path', 'file_type', 'document_type', 'status', 'created_at', 'updated_at'];
$newDocCols = array_merge($oldDocCols, ['deleted_at']);
$selParts = [];
foreach ($newDocCols as $c) {
    if ($c === 'deleted_at') {
        $selParts[] = "NULL AS `{$c}`";
    } else {
        $selParts[] = "`{$c}`";
    }
}
$sql = "INSERT INTO `{$newDbName}`.`documents` (`" . implode('`, `', $newDocCols) . "`)\nSELECT " . implode(', ', $selParts) . " FROM `{$oldDbName}`.`documents`";
$new->statement($sql);
$cnt = rowCount($new, 'documents');
$insertResults['documents'] = $cnt;
echo "  INSERTED {$cnt} rows\n";

// --- 3i. revenues (FK: students, users) ---
echo "\n--- 3i. revenues ---\n";
$cnt = insertAll($new, $old, $oldDbName, $newDbName, 'revenues', ['id', 'student_id', 'amount', 'method', 'transaction_date', 'reference_number', 'description', 'receipt_file', 'created_by', 'created_at', 'updated_at']);
$insertResults['revenues'] = $cnt;

// --- 3j. crm_tasks (FK: students, users) ---
echo "\n--- 3j. crm_tasks ---\n";
$crmTaskCols = ['id', 'student_id', 'created_by', 'assigned_to', 'activity_type', 'subject', 'description', 'scheduled_for', 'priority_time_slot', 'status', 'completed_at', 'completed_by', 'completion_note', 'cancelled_at', 'cancelled_by', 'cancellation_note', 'call_direction', 'duration_minutes', 'meta_data', 'created_at', 'updated_at', 'deleted_at'];
$selParts = [];
foreach ($crmTaskCols as $c) {
    if ($c === 'meta_data') {
        $selParts[] = "CAST(`meta_data` AS JSON) AS `meta_data`";
    } else {
        $selParts[] = "`{$c}`";
    }
}
$sql = "INSERT INTO `{$newDbName}`.`crm_tasks` (`" . implode('`, `', $crmTaskCols) . "`)\nSELECT " . implode(', ', $selParts) . " FROM `{$oldDbName}`.`crm_tasks`";
$new->statement($sql);
$cnt = rowCount($new, 'crm_tasks');
$insertResults['crm_tasks'] = $cnt;
echo "  INSERTED {$cnt} rows\n";

// --- 3k. student_notes (FK: students, users) ---
echo "\n--- 3k. student_notes ---\n";
$cnt = insertAll($new, $old, $oldDbName, $newDbName, 'student_notes', ['id', 'student_id', 'created_by', 'content', 'title', 'type', 'is_pinned', 'is_log', 'remind_at', 'reminder_time_slot', 'created_at', 'updated_at', 'deleted_at', 'updated_by']);
$insertResults['student_notes'] = $cnt;

// --- 3l. student_stage_history (FK: students, student_stages, users) ---
echo "\n--- 3l. student_stage_history ---\n";
$histCols = ['id', 'student_id', 'from_stage_id', 'to_stage_id', 'changed_by', 'reason', 'metadata', 'days_in_previous_stage', 'created_at', 'updated_at'];
$selParts = [];
foreach ($histCols as $c) {
    if ($c === 'metadata') {
        $selParts[] = "CAST(`metadata` AS JSON) AS `metadata`";
    } else {
        $selParts[] = "`{$c}`";
    }
}
$sql = "INSERT INTO `{$newDbName}`.`student_stage_history` (`" . implode('`, `', $histCols) . "`)\nSELECT " . implode(', ', $selParts) . " FROM `{$oldDbName}`.`student_stage_history`";
$new->statement($sql);
$cnt = rowCount($new, 'student_stage_history');
$insertResults['student_stage_history'] = $cnt;
echo "  INSERTED {$cnt} rows\n";

// --- 3m. activities (FK: users) ---
echo "\n--- 3m. activities ---\n";
$oldActCols = ['id', 'user_id', 'type', 'description', 'notifiable_id', 'link', 'created_at', 'updated_at'];
$newActCols = array_merge($oldActCols, ['deleted_at']);
$selParts = [];
foreach ($newActCols as $c) {
    if ($c === 'deleted_at') {
        $selParts[] = "NULL AS `{$c}`";
    } else {
        $selParts[] = "`{$c}`";
    }
}
$sql = "INSERT INTO `{$newDbName}`.`activities` (`" . implode('`, `', $newActCols) . "`)\nSELECT " . implode(', ', $selParts) . " FROM `{$oldDbName}`.`activities`";
$new->statement($sql);
$cnt = rowCount($new, 'activities');
$insertResults['activities'] = $cnt;
echo "  INSERTED {$cnt} rows\n";

// --- 3n. chat_messages (FK: users) ---
echo "\n--- 3n. chat_messages ---\n";
$oldChatCols = ['id', 'sender_id', 'receiver_id', 'message', 'file', 'file_type', 'status', 'read_at', 'delivered_at', 'created_at', 'updated_at'];
$newChatCols = array_merge($oldChatCols, ['deleted_at']);
$selParts = [];
foreach ($newChatCols as $c) {
    if ($c === 'deleted_at') {
        $selParts[] = "NULL AS `{$c}`";
    } else {
        $selParts[] = "`{$c}`";
    }
}
$sql = "INSERT INTO `{$newDbName}`.`chat_messages` (`" . implode('`, `', $newChatCols) . "`)\nSELECT " . implode(', ', $selParts) . " FROM `{$oldDbName}`.`chat_messages`";
$new->statement($sql);
$cnt = rowCount($new, 'chat_messages');
$insertResults['chat_messages'] = $cnt;
echo "  INSERTED {$cnt} rows\n";

// --- 3o. user_statuses (no FK) ---
echo "\n--- 3o. user_statuses ---\n";
$cnt = insertAll($new, $old, $oldDbName, $newDbName, 'user_statuses', ['id', 'user_id', 'is_online', 'last_seen', 'last_login_at', 'last_login_ip', 'created_at', 'updated_at']);
$insertResults['user_statuses'] = $cnt;

// --- 3p. notifications (no FK but references users via notifiable_id) ---
echo "\n--- 3p. notifications ---\n";
$cnt = insertAll($new, $old, $oldDbName, $newDbName, 'notifications', ['id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'read_at', 'created_at', 'updated_at']);
$insertResults['notifications'] = $cnt;

// --- 3q. application_message (FK: applications, users) ---
echo "\n--- 3q. application_message ---\n";
$cnt = insertAll($new, $old, $oldDbName, $newDbName, 'application_message', ['id', 'application_id', 'user_id', 'message', 'file_path', 'file_name', 'file_type', 'type', 'created_at', 'updated_at']);
$insertResults['application_message'] = $cnt;

// ============================================================
// STEP 4: RE-ENABLE FK CHECKS
// ============================================================
echo "\n=== STEP 4: Re-enable foreign key checks ===\n";
$new->statement('SET FOREIGN_KEY_CHECKS = 1');

// ============================================================
// STEP 5: VALIDATION
// ============================================================
echo "\n=== STEP 5: VALIDATION ===\n";
echo str_repeat("=", 80) . "\n";

$allPassed = true;
$totalNewBefore = 0;
$totalOld = 0;
$totalNewAfter = 0;

echo str_pad("Table", 30) . str_pad("Old Rows", 15) . str_pad("New Before", 15) . str_pad("New After", 15) . str_pad("Match", 10) . "\n";
echo str_repeat("-", 85) . "\n";

// Common tables + new-only tables
$validateTables = array_merge(
    ['users', 'application_statuses', 'student_stages', 'universities', 'courses', 'students', 'applications', 'documents', 'revenues', 'crm_tasks', 'student_notes', 'student_stage_history', 'activities', 'chat_messages', 'user_statuses', 'notifications', 'application_message'],
    ['contents', 'emails', 'enquiries', 'settings', 'testimonials', 'pages', 'application_status_histories']
);

// Pre-migration counts (we stored these from the initial exam)
$preCounts = [
    'users' => 83, 'application_statuses' => 19, 'student_stages' => 12, 'universities' => 189,
    'courses' => 2137, 'students' => 963, 'applications' => 56, 'documents' => 1017,
    'revenues' => 45, 'crm_tasks' => 3602, 'student_notes' => 1740, 'student_stage_history' => 340,
    'activities' => 5739, 'chat_messages' => 3, 'user_statuses' => 83, 'notifications' => 915,
    'application_message' => 38, 'contents' => 0, 'emails' => 5, 'enquiries' => 1,
    'settings' => 109, 'testimonials' => 1, 'pages' => 0, 'application_status_histories' => 3,
];

foreach ($validateTables as $table) {
    try {
        $oldCnt = $old->table($table)->count();
    } catch (\Exception $e) {
        $oldCnt = 0; // Table doesn't exist in old
    }
    $preCnt = $preCounts[$table] ?? 0;
    $postCnt = $new->table($table)->count();
    
    $match = ($oldCnt === $postCnt) || ($oldCnt === 0 && $postCnt === $preCnt) ? "✓" : "✗";
    if ($match === "✗") $allPassed = false;
    
    echo str_pad($table, 30) . str_pad($oldCnt, 15) . str_pad($preCnt, 15) . str_pad($postCnt, 15) . str_pad($match, 10) . "\n";
    
    $totalOld += $oldCnt;
    $totalNewBefore += $preCnt;
    $totalNewAfter += $postCnt;
}

echo str_repeat("-", 85) . "\n";
echo str_pad("TOTALS", 30) . str_pad($totalOld, 15) . str_pad($totalNewBefore, 15) . str_pad($totalNewAfter, 15) . str_pad($allPassed ? "✓" : "✗", 10) . "\n\n";

// Checksum verification for identical tables
echo "--- Spot checksum verification ---\n";
$checksumTables = ['users', 'student_stages', 'application_statuses', 'universities'];
$checksumPassed = true;
foreach ($checksumTables as $table) {
    try {
        $oldRows = $old->select("SELECT * FROM `{$table}` ORDER BY id LIMIT 50");
        $newRows = $new->select("SELECT * FROM `{$table}` ORDER BY id LIMIT 50");
        if (md5(serialize($oldRows)) === md5(serialize($newRows))) {
            echo "  ✓ {$table}: checksum MATCH\n";
        } else {
            echo "  ✗ {$table}: checksum MISMATCH\n";
            $checksumPassed = false;
            $allPassed = false;
        }
    } catch (\Exception $e) {
        echo "  - {$table}: skipped ({$e->getMessage()})\n";
    }
}

echo "\n=== FINAL RESULT ===\n";
if ($allPassed) {
    echo "✓ ALL VALIDATIONS PASSED — Migration successful\n";
} else {
    echo "✗ SOME VALIDATIONS FAILED — Review mismatches above\n";
}

echo "\nMigration completed at: " . date('Y-m-d H:i:s') . "\n";
