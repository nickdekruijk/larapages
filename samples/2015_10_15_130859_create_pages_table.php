<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->string('title',100);
            $table->string('head')->nullable();
            $table->string('html_title',65)->nullable();
            $table->string('url',100)->nullable();
            $table->text('description')->nullable();
            $table->date('date')->nullable();
            $table->string('picture')->nullable();
            $table->longText('body')->nullable();
            $table->integer('sort')->default(0)->unsigned();
            $table->softDeletes();
            $table->datetime('published_at')->nullable();
            $table->index(['active','parent','sort']);
            $table->foreign('parent')->references('id')->on('pages');
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
        Schema::drop('pages');
    }
}
