<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('broker_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('broker_id')->nullable();
            $table->string('broker_action')->nullable();
            $table->string('action_url')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('broker_id')
                ->references('id')
                ->on('brokers')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broker_actions');
    }
};
