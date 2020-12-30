<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdToAllTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_ledgers', function (Blueprint $table) {
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
        Schema::table('addresses', function (Blueprint $table) {
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
        Schema::table('inventories', function (Blueprint $table) {
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
        Schema::table('notes', function (Blueprint $table) {
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
        Schema::table('vouchers', function (Blueprint $table) {
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account_ledgers', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->removeColumn('company_id');
        });
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->removeColumn('company_id');
        });
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->removeColumn('company_id');
        });
        Schema::table('notes', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->removeColumn('company_id');
        });
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->removeColumn('company_id');
        });
    }
}
