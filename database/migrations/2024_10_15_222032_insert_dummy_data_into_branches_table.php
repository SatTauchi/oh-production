<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class InsertDummyDataIntoBranchesTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        $branches = [
            ['id' => 1, 'name' => '中野支部'],
            ['id' => 2, 'name' => '杉並支部'],
            ['id' => 3, 'name' => '牛込支部'],
            ['id' => 4, 'name' => '四谷支部'],
            ['id' => 5, 'name' => '新宿支部'],
        ];

        foreach ($branches as $branch) {
            DB::table('branches')->insert($branch);
        }
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        DB::table('branches')->whereIn('id', [1, 2, 3, 4, 5])->delete();
    }
}