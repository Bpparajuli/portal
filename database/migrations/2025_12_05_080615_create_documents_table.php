<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('uploaded_by');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->string('document_type')->nullable();
            $table->enum('status', ['uploaded', 'missing', 'reviewed', 'downloaded'])->default('uploaded');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'document_type']);
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('uploaded_by')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
