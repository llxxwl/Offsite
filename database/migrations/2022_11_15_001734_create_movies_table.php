<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('')->comment('电影名');
            $table->string('en_title')->default('')->comment('其他语言电影名');
            $table->string('cover')->default('')->comment('海报链接');
            $table->integer('duration')->default(0)->comment('影片时长');
            $table->string('release_time')->default('')->comment('上映时间');
            $table->float('score', 10, 1)->comment('评分');
            $table->text('profile')->comment('简介');
            $table->integer('rank')->default(0)->comment('影片排名');
            $table->tinyInteger('is_delete')->default(0)->comment('是否删除');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movies');
    }
};
