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
        Schema::create('import_histories', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->integer('total_records');
            $table->integer('successful_records');
            $table->integer('failed_records');
            $table->json('failed_record_details')->nullable();
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
        Schema::dropIfExists('import_histories');
    }
};
