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
        // Update orders table
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_number')) {
                $table->string('order_number')->unique()->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('orders', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->default(0.00)->after('order_number');
            }
            if (!Schema::hasColumn('orders', 'delivery_fee')) {
                $table->decimal('delivery_fee', 10, 2)->default(0.00)->after('subtotal');
            }
            if (!Schema::hasColumn('orders', 'discount')) {
                $table->decimal('discount', 10, 2)->default(0.00)->after('delivery_fee');
            }
            if (!Schema::hasColumn('orders', 'delivery_method')) {
                $table->string('delivery_method')->default('standard')->after('shipping_address');
            }
            if (!Schema::hasColumn('orders', 'phone')) {
                $table->string('phone')->nullable()->after('delivery_method');
            }
            if (!Schema::hasColumn('orders', 'note')) {
                $table->text('note')->nullable()->after('phone');
            }
        });

        // Update payments table
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'currency')) {
                $table->string('currency')->default('USD')->after('amount');
            }
            if (!Schema::hasColumn('payments', 'bank_provider')) {
                $table->string('bank_provider')->nullable()->after('currency'); // ABA, ACLEDA, BAKONG, CARD
            }
            if (!Schema::hasColumn('payments', 'qr_string')) {
                $table->text('qr_string')->nullable()->after('bank_provider');
            }
            if (!Schema::hasColumn('payments', 'qr_image_url')) {
                $table->text('qr_image_url')->nullable()->after('qr_string');
            }
            if (!Schema::hasColumn('payments', 'notes')) {
                $table->text('notes')->nullable()->after('qr_image_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['order_number', 'subtotal', 'delivery_fee', 'discount', 'delivery_method', 'phone', 'note']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['currency', 'bank_provider', 'qr_string', 'qr_image_url', 'notes']);
        });
    }
};
