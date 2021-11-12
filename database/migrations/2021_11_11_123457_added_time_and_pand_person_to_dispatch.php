<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddedTimeAndPandPersonToDispatch extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dispatches', function (Blueprint $table) {
            $table->string('time')->after('date_time');
            $table->string('person')->nullable()->after('transport');
        });
    }
}
