<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePortfolioTable extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        Schema::create('portfolios', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category')->nullable();
            $table->string('file_path');
            $table->integer('size')->nullable();
            $table->string('mime_type')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_video')->default(false);
            $table->timestamps();
        });
    }


    //  @return void
    public function down()
    {
        Schema::dropIfExists('portfolios');
    }
}
