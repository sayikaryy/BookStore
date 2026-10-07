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
        Schema::table('books', function (Blueprint $table) {
            if (!Schema::hasColumn('books', 'rating')) {
                $table->decimal('rating', 3, 2)->default(4.50)->after('cover_image');
            }
            if (!Schema::hasColumn('books', 'rating_count')) {
                $table->integer('rating_count')->default(25)->after('rating');
            }
            if (!Schema::hasColumn('books', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('rating_count');
            }
            if (!Schema::hasColumn('books', 'low_stock_threshold')) {
                $table->integer('low_stock_threshold')->default(5)->after('stock');
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
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['rating', 'rating_count', 'is_featured', 'low_stock_threshold']);
        });
    }
};
