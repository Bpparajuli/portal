<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove obsolete email settings (single-account IMAP + old notify_* keys).
        if (Schema::hasTable('settings')) {
            DB::table('settings')->whereIn('key', [
                'imap_enabled', 'imap_host', 'imap_port', 'imap_username',
                'imap_password', 'imap_encryption', 'imap_mailbox',
                'notify_portal_email', 'notify_admin_email',
                'notify_document_admin_email', 'notify_student_admin_email',
                'notify_application_admin_email', 'notify_enquiry_admin_email',
                'mail_driver', 'mail_reply_to_address', 'mail_reply_to_name',
            ])->delete();
        }

        // Seed the default typed mailboxes once.
        if (Schema::hasTable('mailboxes') && DB::table('mailboxes')->count() === 0) {
            $host = DB::table('settings')->where('key', 'mail_host')->value('value') ?: 'ideacs.com.np';

            $now = now();
            DB::table('mailboxes')->insert([
                $this->row('Portal', 'portal@ideacs.com.np', 'portal', $host, $now),
                $this->row('Admin', 'admin@ideacs.com.np', 'admin', $host, $now),
                $this->row('Student', 'student@ideacs.com.np', 'student', $host, $now),
                $this->row('Document', 'document@ideacs.com.np', 'document', $host, $now),
                $this->row('Application', 'application@ideacs.com.np', 'application', $host, $now),
            ]);
        }
    }

    private function row(string $label, string $email, string $category, string $host, $now): array
    {
        return [
            'label' => $label,
            'email' => $email,
            'category' => $category,
            'imap_host' => $host,
            'imap_port' => 993,
            'imap_username' => $email,
            'imap_password' => '',
            'imap_encryption' => 'ssl',
            'webmail_url' => 'https://mail.ideacs.com.np',
            'is_active' => true,
            'last_unread_count' => 0,
            'last_total_count' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    public function down(): void
    {
        // Nothing to restore.
    }
};
