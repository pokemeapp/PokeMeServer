<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePokesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pokes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('prototype_id')->unsigned();
            $table->foreign('prototype_id')->references('id')->on('poke_prototypes');
            $table->integer('owner_id')->unsigned();
            $table->foreign('owner_id')->references('id')->on('users');
            $table->integer('target_id')->unsigned();
            $table->foreign('target_id')->references('id')->on('users');
            $table->string('response');
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
        Schema::dropIfExists('pokes');
    }
}
