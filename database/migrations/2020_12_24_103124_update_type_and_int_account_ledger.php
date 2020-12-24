<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTypeAndIntAccountLedger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::getConnection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        Schema::table('account_ledgers', function (Blueprint $table) {
            $table->decimal('debit', 15, 2)->change();
            $table->decimal('credit', 15, 2)->change();
            $table->decimal('balance', 15, 2)->change();
            $table->dropColumn('type');
            $table->enum('type', ['Dr', 'Cr']);
        });
    }
}
