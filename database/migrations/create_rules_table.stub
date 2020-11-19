<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateRulesTable extends Migration
{

    public function __construct()
    {
        $this->connection = config('casbin.default.adapter.connection');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(config('casbin.default.adapter.table_name'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('ptype')->nullable();
            $table->string('v0')->nullable();
            $table->string('v1')->nullable();
            $table->string('v2')->nullable();
            $table->string('v3')->nullable();
            $table->string('v4')->nullable();
            $table->string('v5')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists(config('casbin.default.adapter.table_name'));
    }

}
