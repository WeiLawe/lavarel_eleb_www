<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');//订单ID
            $table->string('order_code');//订单编号
            $table->string('order_birth_time');//下单时间
            $table->integer('shop_id');//店铺id
            $table->string('shop_name');//店铺名字
            $table->string('shop_img');//店铺图片
            $table->decimal('order_price');//订单价格
            $table->string('province');//省
            $table->string('city');//市
            $table->string('area');//区
            $table->string('detail_address');//详细地址
            $table->string('receiver');//收货人
            $table->string('tel');//收货人电话
            $table->engine='InnoDB';
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
