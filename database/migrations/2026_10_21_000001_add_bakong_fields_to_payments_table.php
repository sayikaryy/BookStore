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
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'md5_hash')) {
                $table->string('md5_hash')->nullable()->after('qr_string');
            }
            if (!Schema::hasColumn('payments', 'deeplink')) {
                $table->text('deeplink')->nullable()->after('md5_hash');
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
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'md5_hash')) {
                $table->dropColumn('md5_hash');
            }
            if (Schema::hasColumn('payments', 'deeplink')) {
                $table->dropColumn('deeplink');
            }
        });
    }
};
