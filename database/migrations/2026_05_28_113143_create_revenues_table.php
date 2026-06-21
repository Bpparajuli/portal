<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revenues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('method', ['cash', 'bank_transfer', 'credit_card', 'cheque', 'online_payment']);
            $table->date('transaction_date');
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->string('receipt_file')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenues');
    }
};
