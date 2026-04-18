<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('fees_assign_childrens', 'collect_status')) {
            Schema::table('fees_assign_childrens', function (Blueprint $table) {
                $table->tinyInteger('collect_status')->default(1)->after('id')->comment('1=active, 0=cancelled');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('fees_assign_childrens', 'collect_status')) {
            Schema::table('fees_assign_childrens', function (Blueprint $table) {
                $table->dropColumn('collect_status');
            });
        }
    }
};
