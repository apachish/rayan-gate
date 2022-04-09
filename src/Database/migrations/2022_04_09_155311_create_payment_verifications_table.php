<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentVerificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_verifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('RefID');
            $table->string('Status');
            $table->bigInteger('payment_request_id')->unsigned();
            $table->foreign('payment_request_id')
                ->references('id')
                ->on('payment_requests')
                ->onDelete('cascade')
                ->onUpdate('cascade');
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
        Schema::dropIfExists('payment_verification', function(Blueprint $table){
                $table->dropForeign('payment_verification_payment_request_id_foreign');
        });

    }
}
