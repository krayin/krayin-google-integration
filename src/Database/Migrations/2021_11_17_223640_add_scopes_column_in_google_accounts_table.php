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
        Schema::table('google_accounts', function (Blueprint $table) {
            $table->json('scopes')->nullable();
        });

        DB::table('google_accounts')
            ->update([
                'google_accounts.scopes' => ['calendar'],
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('google_accounts', function (Blueprint $table) {
            $table->dropColumn('scopes');
        });
    }
};
