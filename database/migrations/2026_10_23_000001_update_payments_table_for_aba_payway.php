<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE payments MODIFY COLUMN qr_image_url LONGTEXT NULL');
        }
    }

    public function down()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE payments MODIFY COLUMN qr_image_url TEXT NULL');
        }
    }
};
