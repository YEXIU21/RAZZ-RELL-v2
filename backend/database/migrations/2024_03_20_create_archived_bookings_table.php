<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('archived_bookings', function (Blueprint $table) {
            $table->id();
            $table->integer('original_booking_id');
            $table->integer('user_id')->unsigned();
            $table->integer('package_id')->unsigned();
            $table->string('email');
            $table->string('phone');
            $table->string('full_name');
            $table->date('event_date');
            $table->time('event_time');
            $table->string('venue_name');
            $table->string('special_requests')->nullable();
            $table->integer('event_duration')->default(1);
            $table->string('payment_method');
            $table->decimal('total_price', 15, 2)->default(0);
            $table->boolean('terms_accepted')->default(0);
            $table->enum('status', ['pending', 'confirmed', 'ongoing', 'preparing', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('archived_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('archived_bookings');
    }
}; 