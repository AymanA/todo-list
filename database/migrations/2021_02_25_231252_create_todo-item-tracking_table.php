<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTodoItemTrackingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('item_id');
            $table->timestamp('start_time')->useCurrent();
            $table->timestamp('stop_time')->useCurrentOnUpdate()->nullable();
            $table->boolean('tracking')->default('1');
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
        Schema::dropIfExists('todo');
    }
}
