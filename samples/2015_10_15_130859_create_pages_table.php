<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('parent')->nullable()->unsigned();
            $table->boolean('active')->default(1);
            $table->boolean('hidden')->default(0);
            $table->boolean('home')->default(0);
            $table->string('title', 100);
            $table->string('view', 100)->nullable();
            $table->string('head')->nullable();
            $table->string('html_title', 65)->nullable();
            $table->string('slug', 100)->nullable();
            $table->text('description')->nullable();
            $table->date('date')->nullable();
            $table->longText('pictures')->nullable();
            $table->string('background')->nullable();
            $table->longText('body')->nullable();
            $table->integer('sort')->default(0)->unsigned();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['active', 'parent', 'sort']);
            $table->foreign('parent')->references('id')->on('pages');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pages');
    }
}
