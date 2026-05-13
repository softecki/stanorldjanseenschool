<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Transport fee logic expects one row per billing month; StudentRepository divides annual transport by 10.
     * Keep exactly 10 rows unless you change that divisor in code.
     */
    public function up(): void
    {
        if (Schema::hasTable('months_list')) {
            return;
        }

        Schema::create('months_list', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('name', 50);
        });

        $rows = [
            [1, 'September'],
            [2, 'October'],
            [3, 'November'],
            [4, 'December'],
            [5, 'January'],
            [6, 'February'],
            [7, 'March'],
            [8, 'April'],
            [9, 'May'],
            [10, 'June'],
        ];

        foreach ($rows as [$id, $name]) {
            DB::table('months_list')->insert(['id' => $id, 'name' => $name]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('months_list');
    }
};
