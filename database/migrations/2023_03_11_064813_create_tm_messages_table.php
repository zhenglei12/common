<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTMMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tm_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('message_id')->nullable()->unique();
            $table->string('parent_subtask',50)->nullable()->index();
            $table->string('topic',50)->nullable(false)->index();
            $table->unsignedTinyInteger('type')->nullable(false);
            $table->json('data')->nullable();
            $table->tinyInteger('status')->nullable(false);
            $table->timestamp('reserved_at')->nullable();
            $table->unsignedTinyInteger('attempts')->nullable();
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
        Schema::dropIfExists('tm_messages');
    }
}
