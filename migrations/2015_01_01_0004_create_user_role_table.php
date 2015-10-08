<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserRoleTable extends Migration
{

    public function up()
    {
        Schema::create('user_role', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('role_id')->unsigned();

            $table->unique(['user_id', 'role_id']);
        });
    }

    public function down()
    {
        Schema::drop('user_role');
    }
}