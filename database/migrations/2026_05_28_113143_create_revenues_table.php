<?php
// database/migrations/2024_01_01_000000_create_revenues_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('revenues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->enum('method', ['cash', 'bank_transfer', 'credit_card', 'cheque', 'online_payment']);
            $table->date('transaction_date');
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->string('receipt_file')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // Add revenue columns to students table
        Schema::table('students', function (Blueprint $table) {
            $table->decimal('expected_revenue', 12, 2)->default(0);
            $table->decimal('received_revenue', 12, 2)->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('revenues');
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['expected_revenue', 'received_revenue']);
        });
    }
};
