<?php

use Illuminate\Support\Facades\Schema;
use \app\common\models\Flow;
use Illuminate\Database\Migrations\Migration;

class AddAuditToStatusFlowTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_status')) {
            return;
        }
        $this->audit();
    }

    private function audit()
    {
        /**
         * @var Flow $flow
         */
        $flow = \app\common\models\Flow::create([
            'name' => '后台审核',
            'code' => 'audit',
        ]);
        $flow->pushManyStatus([
            [
                'code' => 'waitAudit',
                'name' => '待审核',
                'order' => 0,

            ], [
                'name' => '已通过',
                'code' => 'passed',
                'order' => 10,

            ], [
                'name' => '已取消',
                'code' => 'canceled',
                'order' => -1,

            ], [
                'name' => '已拒绝',
                'code' => 'refused',
                'order' => -2,

            ],
        ]);
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
