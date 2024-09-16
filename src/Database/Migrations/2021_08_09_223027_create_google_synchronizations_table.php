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
        Schema::create('google_synchronizations', function (Blueprint $table) {
            $table->string('id');
            $table->morphs('synchronizable', 'google_synchronizations_type_id_index');
            $table->string('token')->nullable();
            $table->string('resource_id')->nullable();

            $table->datetime('expired_at')->nullable();
            $table->datetime('last_synchronized_at');
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
        Schema::dropIfExists('google_synchronizations');
    }
};
