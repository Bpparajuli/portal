<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('student_id')->index();
            $table->unsignedBigInteger('university_id')->index();
            $table->unsignedBigInteger('course_id')->nullable()->index();
            $table->unsignedBigInteger('agent_id')->index();

            $table->enum('application_status', [
                'Application started',
                'Application viewed by Admin',
                'Applied to University',
                'Need to give the test',
                'Accepted by the University',
                'Rejected by the University',
                'Applied to another university',
                'Application forwarded to embassy',
                'Is on waiting list on Embassy',
                'Visa Approved',
                'Visa Rejected',
                'Lost',
                'Withdrawn'
            ])->default('Application started');

            $table->string('application_number')->nullable()->unique();
            $table->string('sop_file')->nullable();
            $table->timestamps();

            $table->timestamp('withdrawn_at')->nullable();
            $table->string('withdraw_reason')->nullable();

            // 🔗 Foreign Keys
            $table->foreign('agent_id')
                ->references('id')->on('users')
                ->onUpdate('no action')
                ->onDelete('cascade');

            $table->foreign('course_id')
                ->references('id')->on('courses')
                ->onUpdate('no action')
                ->onDelete('set null');

            $table->foreign('student_id')
                ->references('id')->on('students')
                ->onUpdate('no action')
                ->onDelete('cascade');

            $table->foreign('university_id')
                ->references('id')->on('universities')
                ->onUpdate('no action')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
