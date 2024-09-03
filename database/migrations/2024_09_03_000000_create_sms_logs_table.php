<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('mobile');
            $table->text('sms');
            $table->enum('status', ['scheduled', 'success', 'failed'])->default('scheduled');
            $table->timestamp('send_at')->nullable(); // NULL means immediate send, otherwise it's scheduled
            $table->timestamps();
        });

        // Separate table for tracking rate limits
        Schema::create('sms_rate_limits', function (Blueprint $table) {
            $table->id();
            $table->string('mobile');
            $table->integer('sent_count')->default(0);
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sms_logs');
        Schema::dropIfExists('sms_rate_limits');
    }
}
