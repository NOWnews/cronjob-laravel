<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewtalkFeed extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
	Schema::create('newtalk_feed', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            //文章編號
            $table->string('guid' , 60);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
	Schema::dropIfExists('newtalk_feed');
    }
}
