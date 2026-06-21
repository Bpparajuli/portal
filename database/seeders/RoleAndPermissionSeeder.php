<?php

namespace Database\Seeders;

use App\Models\ApplicationStatus;
use App\Models\Setting;
use App\Models\StudentStage;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedApplicationStatuses();
        $this->seedStudentStages();
        $this->seedSettings();
    }

    protected function seedApplicationStatuses(): void
    {
        $statuses = [
            ['name' => 'Application Started',      'sort_order' => 1, 'bg_color' => '#3b82f6', 'text_color' => '#ffffff'],
            ['name' => 'Documents Submitted',      'sort_order' => 2, 'bg_color' => '#8b5cf6', 'text_color' => '#ffffff'],
            ['name' => 'Application Submitted',    'sort_order' => 3, 'bg_color' => '#f59e0b', 'text_color' => '#ffffff'],
            ['name' => 'Offer Received',           'sort_order' => 4, 'bg_color' => '#10b981', 'text_color' => '#ffffff'],
            ['name' => 'Visa Applied',              'sort_order' => 5, 'bg_color' => '#06b6d4', 'text_color' => '#ffffff'],
            ['name' => 'Visa Approved',             'sort_order' => 6, 'bg_color' => '#22c55e', 'text_color' => '#ffffff'],
            ['name' => 'Visa Rejected',             'sort_order' => 7, 'bg_color' => '#ef4444', 'text_color' => '#ffffff'],
            ['name' => 'Enrolled',                  'sort_order' => 8, 'bg_color' => '#1a0262', 'text_color' => '#ffffff'],
            ['name' => 'Lost',                      'sort_order' => 9, 'bg_color' => '#6b7280', 'text_color' => '#ffffff'],
        ];

        foreach ($statuses as $status) {
            try {
                ApplicationStatus::firstOrCreate(
                    ['name' => $status['name']],
                    $status
                );
            } catch (\Throwable $e) {
                // skip if already exists
            }
        }
    }

    protected function seedStudentStages(): void
    {
        $stages = [
            ['name' => 'New Lead',       'stage_order' => 1, 'color' => '#3b82f6'],
            ['name' => 'Contacted',       'stage_order' => 2, 'color' => '#8b5cf6'],
            ['name' => 'Follow Up',       'stage_order' => 3, 'color' => '#f59e0b'],
            ['name' => 'Documentation',   'stage_order' => 4, 'color' => '#06b6d4'],
            ['name' => 'Ready to Apply',  'stage_order' => 5, 'color' => '#10b981'],
            ['name' => 'Applied',         'stage_order' => 6, 'color' => '#1a0262'],
            ['name' => 'Enrolled',        'stage_order' => 7, 'color' => '#22c55e'],
            ['name' => 'Lost',            'stage_order' => 8, 'color' => '#6b7280'],
        ];

        foreach ($stages as $stage) {
            try {
                StudentStage::firstOrCreate(
                    ['name' => $stage['name']],
                    $stage
                );
            } catch (\Throwable $e) {
                // skip if already exists
            }
        }
    }

    protected function seedSettings(): void
    {
        $settings = [
            ['key' => 'site_name',              'value' => 'Idea Consultancy',          'group' => 'general',  'type' => 'string'],
            ['key' => 'site_email',             'value' => 'info@ideaconsultancy.com',  'group' => 'general',  'type' => 'string'],
            ['key' => 'site_phone',             'value' => '+1234567890',               'group' => 'general',  'type' => 'string'],
            ['key' => 'address',                'value' => '123 Business Park',         'group' => 'general',  'type' => 'string'],
            ['key' => 'max_students_free_plan', 'value' => '50',                        'group' => 'agent',    'type' => 'number'],
            ['key' => 'max_staff_free_plan',    'value' => '3',                         'group' => 'agent',    'type' => 'number'],
            ['key' => 'crm_enabled',            'value' => 'true',                      'group' => 'features', 'type' => 'boolean'],
            ['key' => 'mail_enabled',           'value' => 'false',                     'group' => 'email',    'type' => 'boolean'],
            ['key' => 'mail_host',              'value' => config('mail.mailers.smtp.host', '127.0.0.1'), 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_port',              'value' => (string) config('mail.mailers.smtp.port', '587'), 'group' => 'email', 'type' => 'number'],
            ['key' => 'mail_username',          'value' => '',                          'group' => 'email',    'type' => 'string'],
            ['key' => 'mail_password',          'value' => '',                          'group' => 'email',    'type' => 'string'],
            ['key' => 'mail_encryption',        'value' => config('mail.mailers.smtp.encryption', 'tls'), 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_from_address',      'value' => config('mail.from.address', 'hello@example.com'), 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_from_name',         'value' => config('mail.from.name', 'Portal'), 'group' => 'email', 'type' => 'string'],
            ['key' => 'imap_enabled',           'value' => 'false',                     'group' => 'email',    'type' => 'boolean'],
            ['key' => 'imap_host',              'value' => '',                          'group' => 'email',    'type' => 'string'],
            ['key' => 'imap_port',              'value' => '993',                       'group' => 'email',    'type' => 'number'],
            ['key' => 'imap_username',          'value' => '',                          'group' => 'email',    'type' => 'string'],
            ['key' => 'imap_password',          'value' => '',                          'group' => 'email',    'type' => 'string'],
            ['key' => 'imap_encryption',        'value' => 'ssl',                       'group' => 'email',    'type' => 'string'],
            ['key' => 'imap_mailbox',           'value' => 'INBOX',                     'group' => 'email',    'type' => 'string'],
            ['key' => 'dashboard_widgets',     'value' => json_encode([
                'stat_cards' => true, 'application_pipeline' => true, 'recent_students' => true,
                'recent_applications' => true, 'monthly_chart' => true, 'activity_feed' => true,
            ]), 'group' => 'dashboard', 'type' => 'json'],
            ['key' => 'welcome_message',       'value' => 'Welcome to the admin dashboard. Manage your portal from here.', 'group' => 'dashboard', 'type' => 'text'],
        ];

        foreach ($settings as $setting) {
            try {
                Setting::firstOrCreate(
                    ['key' => $setting['key']],
                    $setting
                );
            } catch (\Throwable $e) {
                // skip if already exists
            }
        }
    }
}
