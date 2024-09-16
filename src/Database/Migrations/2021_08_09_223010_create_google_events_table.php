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
        Schema::create('google_events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('google_id')->nullable();

            $table->integer('activity_id')->nullable()->unsigned();
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');

            $table->integer('google_calendar_id')->unsigned();
            $table->foreign('google_calendar_id')->references('id')->on('google_calendars')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('google_events');
    }
};
