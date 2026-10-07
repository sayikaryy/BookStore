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
        if (!Schema::hasTable('inventory_logs')) {
            Schema::create('inventory_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('type'); // 'stock_in', 'stock_out', 'adjustment', 'purchase', 'order_sale', 'order_cancel'
                $table->integer('quantity'); // positive or negative
                $table->integer('previous_stock');
                $table->integer('new_stock');
                $table->decimal('unit_cost', 10, 2)->nullable();
                $table->string('reference_number')->nullable(); // PO or Order number
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_logs');
    }
};
