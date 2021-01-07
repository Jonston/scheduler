<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_schedule', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('day_id');
            $table->unsignedBigInteger('user_id');
            $table->string('time');
            $table->timestamps();

            $table->foreign('day_id')->references('id')->on('weekly_schedule')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('daily_schedule', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['day_id']);
        });

        Schema::dropIfExists('daily_schedule');
    }
}
