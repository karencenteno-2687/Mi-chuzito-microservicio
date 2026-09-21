<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('code', 6);
            $table->string('type');           // email_2fa, sms_otp, account_activation
            $table->string('channel');        // email, sms
            $table->integer('attempts')->default(0);
            $table->integer('max_attempts')->default(5);
            $table->boolean('used')->default(false);
            $table->timestamp('expires_at');
            $table->timestamp('resend_after')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_codes');
    }
};