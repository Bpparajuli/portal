<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Schedule;

Artisan::command('migrate:old-data', function () {
    $old = DB::connection('mysql_old');
    $new = DB::connection('mysql');
    $newDb = config('database.connections.mysql.database');

    $this->info('=== Migrating data from portaldb → ' . $newDb . ' ===');

    // ================================================================
    // Tables to migrate (in FK-safe order — parents first)
    // ================================================================

    $tables = [
        [
            'table' => 'users',
            'map' => [
                'id' => 'id', 'business_name' => 'business_name', 'owner_name' => 'owner_name',
                'name' => 'name', 'business_logo' => 'business_logo', 'registration' => 'registration',
                'pan' => 'pan', 'contact' => 'contact', 'address' => 'address',
                'email' => 'email', 'role' => 'role', 'parent_id' => 'parent_id',
                'password' => 'password', 'agreement_file' => 'agreement_file',
                'agreement_status' => 'agreement_status', 'agreement_uploaded_at' => 'agreement_uploaded_at',
                'slug' => 'slug', 'active' => 'active', 'created_at' => 'created_at',
                'updated_at' => 'updated_at',
            ],
            'defaults' => [
                'phone' => null, 'timezone' => 'UTC',
                'last_login_at' => null, 'last_login_ip' => null,
                'paid_crm' => 0, 'subscription_plan' => null,
                'subscription_starts_at' => null, 'subscription_ends_at' => null,
                'max_staff' => 0, 'max_students' => 0,
            ],
            'transform' => function ($row) {
                $prefs = $row->crm_notification_preferences ?? null;
                if ($prefs && is_string($prefs)) {
                    $decoded = json_decode($prefs, true);
                    $row->crm_notification_preferences = $decoded ?: $prefs;
                } else {
                    $row->crm_notification_preferences = null;
                }
                return $row;
            },
        ],
        [
            'table' => 'student_stages',
            'map' => ['id' => 'id', 'name' => 'name', 'slug' => 'slug', 'color' => 'color',
                'stage_order' => 'stage_order', 'is_active' => 'is_active',
                'is_won_stage' => 'is_won_stage', 'is_lost_stage' => 'is_lost_stage',
                'description' => 'description', 'deleted_at' => 'deleted_at',
                'created_at' => 'created_at', 'updated_at' => 'updated_at',
            ],
            'defaults' => [
                'meta_data' => null, 'allowed_next_stages' => null, 'max_days_in_stage' => null,
            ],
        ],
        [
            'table' => 'universities',
            'map' => ['id' => 'id', 'name' => 'name', 'university_logo' => 'university_logo',
                'short_name' => 'short_name', 'country' => 'country', 'city' => 'city',
                'website' => 'website', 'contact_email' => 'contact_email',
                'description' => 'description', 'created_at' => 'created_at',
                'updated_at' => 'updated_at',
            ],
            'defaults' => [
                'featured_image' => null, 'gallery' => null,
                'phone' => null, 'map_url' => null, 'is_active' => 1,
                'is_featured' => 0, 'address' => null, 'deleted_at' => null,
            ],
        ],
        [
            'table' => 'courses',
            'map' => ['id' => 'id', 'university_id' => 'university_id',
                'course_code' => 'course_code', 'title' => 'title',
                'course_link' => 'course_link', 'course_type' => 'course_type',
                'academic_requirement' => 'academic_requirement',
                'description' => 'description', 'duration' => 'duration',
                'fee' => 'fee', 'intakes' => 'intakes',
                'ielts_pte_other_languages' => 'ielts_pte_other_languages',
                'moi_acceptance' => 'moi_acceptance',
                'application_fee' => 'application_fee', 'scholarships' => 'scholarships',
                'created_at' => 'created_at', 'updated_at' => 'updated_at',
            ],
            'defaults' => ['is_active' => 1, 'is_featured' => 0, 'deleted_at' => null],
        ],
        [
            'table' => 'students',
            'map' => ['id' => 'id', 'agent_id' => 'agent_id', 'source' => 'source',
                'first_name' => 'first_name', 'last_name' => 'last_name',
                'students_photo' => 'students_photo', 'dob' => 'dob',
                'gender' => 'gender', 'email' => 'email',
                'phone_number' => 'phone_number',
                'permanent_address' => 'permanent_address',
                'temporary_address' => 'temporary_address',
                'nationality' => 'nationality', 'passport_number' => 'passport_number',
                'passport_expiry' => 'passport_expiry',
                'marital_status' => 'marital_status', 'applying_for' => 'applying_for',
                'qualification' => 'qualification', 'passed_year' => 'passed_year',
                'gap' => 'gap', 'last_grades' => 'last_grades',
                'education_board' => 'education_board',
                'preferred_country' => 'preferred_country',
                'preferred_city' => 'preferred_city',
                'preferred_course' => 'preferred_course',
                'preferred_university' => 'preferred_university',
                'current_stage_id' => 'current_stage_id', 'rating' => 'rating',
                'tags' => 'tags', 'remarks' => 'remarks', 'pinned' => 'pinned',
                'expected_revenue' => 'expected_revenue',
                'received_revenue' => 'received_revenue',
                'deleted_at' => 'deleted_at',
                'created_at' => 'created_at', 'updated_at' => 'updated_at',
            ],
            'defaults' => ['phone_last_10' => null],
        ],
        [
            'table' => 'application_statuses',
            'map' => ['id' => 'id', 'name' => 'name', 'bg_color' => 'bg_color',
                'text_color' => 'text_color', 'sort_order' => 'sort_order',
                'is_active' => 'is_active', 'created_at' => 'created_at',
                'updated_at' => 'updated_at',
            ],
            'defaults' => ['icon' => null, 'description' => null, 'deleted_at' => null],
        ],
        [
            'table' => 'applications',
            'map' => ['id' => 'id', 'student_id' => 'student_id',
                'university_id' => 'university_id', 'course_id' => 'course_id',
                'agent_id' => 'agent_id',
                'application_status_id' => 'application_status_id',
                'application_status' => 'application_status',
                'application_number' => 'application_number', 'sop_file' => 'sop_file',
                'created_at' => 'created_at', 'updated_at' => 'updated_at',
                'withdrawn_at' => 'withdrawn_at', 'withdraw_reason' => 'withdraw_reason',
            ],
            'defaults' => ['deleted_at' => null],
        ],
        [
            'table' => 'application_message',
            'map' => ['id' => 'id', 'application_id' => 'application_id',
                'user_id' => 'user_id', 'message' => 'message',
                'file_path' => 'file_path', 'file_name' => 'file_name',
                'file_type' => 'file_type', 'type' => 'type',
                'created_at' => 'created_at', 'updated_at' => 'updated_at',
            ],
            'defaults' => [],
        ],
        [
            'table' => 'documents',
            'map' => ['id' => 'id', 'student_id' => 'student_id',
                'uploaded_by' => 'uploaded_by', 'file_name' => 'file_name',
                'file_path' => 'file_path', 'file_type' => 'file_type',
                'document_type' => 'document_type', 'status' => 'status',
                'created_at' => 'created_at', 'updated_at' => 'updated_at',
            ],
            'defaults' => ['deleted_at' => null],
        ],
        [
            'table' => 'activities',
            'map' => ['id' => 'id', 'user_id' => 'user_id', 'type' => 'type',
                'description' => 'description', 'notifiable_id' => 'notifiable_id',
                'link' => 'link', 'created_at' => 'created_at',
                'updated_at' => 'updated_at',
            ],
            'defaults' => ['deleted_at' => null],
        ],
        [
            'table' => 'notifications',
            'map' => ['id' => 'id', 'type' => 'type',
                'notifiable_type' => 'notifiable_type',
                'notifiable_id' => 'notifiable_id', 'data' => 'data',
                'read_at' => 'read_at', 'created_at' => 'created_at',
                'updated_at' => 'updated_at',
            ],
            'defaults' => [],
        ],
        [
            'table' => 'chat_messages',
            'map' => ['id' => 'id', 'sender_id' => 'sender_id',
                'receiver_id' => 'receiver_id', 'message' => 'message',
                'file' => 'file', 'file_type' => 'file_type', 'status' => 'status',
                'delivered_at' => 'delivered_at', 'read_at' => 'read_at',
                'created_at' => 'created_at', 'updated_at' => 'updated_at',
            ],
            'defaults' => ['deleted_at' => null],
        ],
        [
            'table' => 'user_statuses',
            'map' => ['id' => 'id', 'user_id' => 'user_id',
                'is_online' => 'is_online', 'last_seen' => 'last_seen',
                'created_at' => 'created_at', 'updated_at' => 'updated_at',
            ],
            'defaults' => ['last_login_at' => null, 'last_login_ip' => null],
        ],
        [
            'table' => 'crm_tasks',
            'map' => ['id' => 'id', 'student_id' => 'student_id',
                'created_by' => 'created_by', 'assigned_to' => 'assigned_to',
                'activity_type' => 'activity_type', 'subject' => 'subject',
                'description' => 'description', 'scheduled_for' => 'scheduled_for',
                'priority_time_slot' => 'priority_time_slot', 'status' => 'status',
                'completed_at' => 'completed_at', 'completed_by' => 'completed_by',
                'completion_note' => 'completion_note',
                'cancelled_at' => 'cancelled_at', 'cancelled_by' => 'cancelled_by',
                'cancellation_note' => 'cancellation_note',
                'call_direction' => 'call_direction',
                'duration_minutes' => 'duration_minutes',
                'meta_data' => 'meta_data',
                'created_at' => 'created_at', 'updated_at' => 'updated_at',
                'deleted_at' => 'deleted_at',
            ],
            'defaults' => [],
        ],
        [
            'table' => 'student_notes',
            'map' => ['id' => 'id', 'student_id' => 'student_id',
                'created_by' => 'created_by', 'content' => 'content',
                'type' => 'type', 'is_pinned' => 'is_pinned',
                'remind_at' => 'remind_at',
                'reminder_time_slot' => 'reminder_time_slot',
                'deleted_at' => 'deleted_at',
                'created_at' => 'created_at', 'updated_at' => 'updated_at',
            ],
            'defaults' => ['updated_by' => null, 'title' => null, 'is_log' => 0],
        ],
        [
            'table' => 'student_stage_history',
            'map' => ['id' => 'id', 'student_id' => 'student_id',
                'from_stage_id' => 'from_stage_id', 'to_stage_id' => 'to_stage_id',
                'changed_by' => 'changed_by', 'reason' => 'reason',
                'metadata' => 'metadata',
                'days_in_previous_stage' => 'days_in_previous_stage',
                'created_at' => 'created_at', 'updated_at' => 'updated_at',
            ],
            'defaults' => [],
        ],
        [
            'table' => 'revenues',
            'map' => ['id' => 'id', 'student_id' => 'student_id',
                'amount' => 'amount', 'method' => 'method',
                'transaction_date' => 'transaction_date',
                'reference_number' => 'reference_number',
                'description' => 'description', 'receipt_file' => 'receipt_file',
                'created_by' => 'created_by', 'created_at' => 'created_at',
                'updated_at' => 'updated_at',
            ],
            'defaults' => [],
        ],
    ];

    // ================================================================
    // STEP 1: Clear shared tables (reverse order to respect FKs)
    // ================================================================

    $clearTables = array_reverse(array_column($tables, 'table'));

    $this->info('Clearing existing data from shared tables...');
    $new->statement('SET FOREIGN_KEY_CHECKS=0');
    foreach ($clearTables as $table) {
        $count = $new->table($table)->count();
        if ($count > 0) {
            $new->table($table)->truncate();
            $this->line("  ✓ {$table}: {$count} rows cleared");
        } else {
            $this->line("  - {$table}: already empty");
        }
    }
    $new->statement('SET FOREIGN_KEY_CHECKS=1');
    $this->info('All shared tables cleared.');

    // ================================================================
    // STEP 2: Migrate data from old DB
    // ================================================================

    $this->newLine();
    $this->info('Migrating data from portaldb...');

    $totalMigrated = 0;

    $new->statement('SET FOREIGN_KEY_CHECKS=0');

    foreach ($tables as $cfg) {
        $table = $cfg['table'];
        $map = $cfg['map'];
        $defaults = $cfg['defaults'];
        $transform = $cfg['transform'] ?? null;

        $this->line("  → {$table}...");

        if (!Schema::connection('mysql_old')->hasTable($table)) {
            $this->warn("    Table not found in old DB, skipping.");
            continue;
        }

        $oldRows = $old->table($table)->orderBy('id')->get();
        $count = $oldRows->count();

        if ($count === 0) {
            $this->line("    0 rows");
            continue;
        }

        $inserted = 0;
        $batch = [];

        foreach ($oldRows as $row) {
            if ($transform) {
                $row = $transform($row);
            }

            $data = [];
            foreach ($map as $oldCol => $newCol) {
                if ($oldCol === null || !property_exists($row, $oldCol)) {
                    continue;
                }
                $data[$newCol] = $row->$oldCol;
            }

            foreach ($defaults as $col => $val) {
                if (!array_key_exists($col, $data)) {
                    $data[$col] = $val;
                }
            }

            $batch[] = $data;
            $inserted++;

            // Insert in batches of 500
            if (count($batch) >= 500) {
                $new->table($table)->insert($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $new->table($table)->insert($batch);
        }

        $totalMigrated += $inserted;
        $this->line("    {$inserted} rows migrated");
    }

    $new->statement('SET FOREIGN_KEY_CHECKS=1');

    // ================================================================
    // STEP 3: Reset auto-increment to max id + 1
    // ================================================================

    $this->newLine();
    $this->info('Resetting auto-increment values...');
    foreach ($tables as $cfg) {
        $table = $cfg['table'];
        $maxId = (int) $new->table($table)->max('id');
        if ($maxId) {
            $new->statement("ALTER TABLE `{$table}` AUTO_INCREMENT = " . ($maxId + 1));
        }
    }

    $this->newLine();
    $this->info("=== DONE. {$totalMigrated} total rows migrated ===");
})->purpose('Clear new DB and migrate all data from old portaldb');

// ─── Email sync schedule ───
Schedule::command('emails:sync')->everyFiveMinutes()->withoutOverlapping();
