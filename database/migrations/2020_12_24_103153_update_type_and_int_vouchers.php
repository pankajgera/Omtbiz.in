<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTypeAndIntVouchers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::getConnection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        // Schema::table('vouchers', function (Blueprint $table) {
        //     $table->dropColumn('type');
        // });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->decimal('debit', 15, 2)->change();
            $table->decimal('credit', 15, 2)->change();
            $table->enum('type', ['Dr', 'Cr']);
        });
    }
}
