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
        Schema::create('application_message', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('application_id')->index(); // index optional, FK auto-creates
            $table->unsignedBigInteger('user_id')->index();

            $table->text('message')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable();
            $table->enum('type', ['agent', 'admin']);
            $table->timestamps();

            // Foreign keys
            $table->foreign('application_id')
                ->references('id')->on('applications')
                ->onUpdate('no action')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onUpdate('no action')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_message');
    }
};
