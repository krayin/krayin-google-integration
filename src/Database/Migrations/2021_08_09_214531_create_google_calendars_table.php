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
        Schema::create('google_calendars', function (Blueprint $table) {
            $table->increments('id');
            $table->string('google_id');
            $table->string('name');
            $table->string('color');
            $table->string('timezone');
            $table->string('is_primary')->default(0);

            $table->integer('google_account_id')->unsigned();
            $table->foreign('google_account_id')->references('id')->on('google_accounts')->onDelete('cascade');

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
        Schema::dropIfExists('google_calendars');
    }
};
