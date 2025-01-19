<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('package_name');
            $table->text('package_description');
            $table->decimal('package_price', 12, 2);
            $table->boolean('price_per_day')->default(true);
            $table->decimal('price_increase_per_day', 12, 2)->default(0);
            $table->decimal('additional_price_percentage', 5, 2)->default(0);
            $table->integer('packs');
            $table->string('package_type');
            $table->text('package_inclusion');
            $table->string('package_image')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('flag', ['0', '1'])->default('0');
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
        Schema::dropIfExists('packages');
    }
};
