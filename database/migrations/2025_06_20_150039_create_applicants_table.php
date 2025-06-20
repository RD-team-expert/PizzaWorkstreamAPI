<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('applicants', function (Blueprint $table) {
        $table->id();
        $table->uuid('uuid');
        $table->string('first_name');
        $table->string('last_name');
        $table->string('email')->unique();
        $table->string('phone');
        $table->string('name');
        $table->string('status')->nullable();
        $table->string('current_stage')->nullable();
        $table->date('application_date')->nullable();
        $table->string('hired_at')->nullable();
        $table->string('sms_phone_number')->nullable();
        $table->string('global_phone_number')->nullable();
        $table->string('language')->nullable();
        $table->string('referer_source')->nullable();
        $table->string('position_title')->nullable();
        $table->string('location_name')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicants');
    }
};
