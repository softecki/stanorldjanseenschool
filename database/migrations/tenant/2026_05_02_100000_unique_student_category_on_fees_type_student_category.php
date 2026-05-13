<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('fees_type_student_category')) {
            return;
        }

        // Keep one row per student_category_id (lowest id wins) before adding unique index.
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('
                DELETE t1 FROM fees_type_student_category t1
                INNER JOIN fees_type_student_category t2
                    ON t1.student_category_id = t2.student_category_id
                    AND t1.id > t2.id
            ');
        } else {
            $keepIds = DB::table('fees_type_student_category')
                ->selectRaw('MIN(id) as id')
                ->groupBy('student_category_id')
                ->pluck('id');
            if ($keepIds->isNotEmpty()) {
                DB::table('fees_type_student_category')
                    ->whereNotIn('id', $keepIds->all())
                    ->delete();
            }
        }

        if ($this->hasStudentCategoryUniqueIndex()) {
            return;
        }

        Schema::table('fees_type_student_category', function (Blueprint $table) {
            $table->unique('student_category_id', 'ftsc_student_category_unique');
        });
    }

    private function hasStudentCategoryUniqueIndex(): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            $db = $connection->getDatabaseName();
            $rows = DB::select(
                'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
                [$db, 'fees_type_student_category', 'ftsc_student_category_unique']
            );

            return ! empty($rows);
        }

        if ($driver === 'sqlite') {
            $indexes = DB::select('PRAGMA index_list(fees_type_student_category)');
            foreach ($indexes as $index) {
                if (($index->name ?? '') === 'ftsc_student_category_unique') {
                    return true;
                }
            }

            return false;
        }

        return false;
    }

    public function down(): void
    {
        if (! Schema::hasTable('fees_type_student_category')) {
            return;
        }

        if (! $this->hasStudentCategoryUniqueIndex()) {
            return;
        }

        Schema::table('fees_type_student_category', function (Blueprint $table) {
            $table->dropUnique('ftsc_student_category_unique');
        });
    }
};
