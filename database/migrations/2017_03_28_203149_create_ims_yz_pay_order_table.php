<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzPayOrderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_pay_order')) {
            Schema::create('yz_pay_order', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid')->index('idx_uniacid');
                $table->integer('member_id')->index('idx_member_id');
                $table->string('int_order_no', 32)->nullable();
                $table->string('out_order_no', 32)->default('0')->index('idx_order_no');
                $table->boolean('status')->default(0);
                $table->integer('price');
                $table->boolean('type');
                $table->boolean('third_type');
                $table->integer('created_at')->default(0);
                $table->integer('updated_at')->default(0);
                $table->integer('deleted_at')->nullable();
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
		Schema::drop('yz_pay_order');
	}

}
