<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDonationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_hidden')->default(false);

            $table->string('source');
            $table->bigInteger('external_id');
            $table->string('from');
            $table->bigInteger('amount');
            $table->bigInteger('commission');
            $table->text('text');
            $table->text('admin_comment')->default('');
            $table->string('status');
            $table->jsonb('additional_data')->default('[]');

            $table->timestamp('paid_at');
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
        Schema::dropIfExists('donations');
    }
}
