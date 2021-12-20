<?php

use App\Models\InvoiceItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToInvoiceItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('type')->nullable();
        });

        InvoiceItem::each(function ($item) {
            $item->update([
                'type' => 'invoice',
            ]);
        });
    }
}
