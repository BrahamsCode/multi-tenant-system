<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('database_servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('host');
            $table->string('port')->default('5432');
            $table->string('database')->comment('Base de datos principal del servidor');
            $table->string('username');
            $table->string('password');
            $table->integer('capacity')->default(0);
            $table->boolean('active')->default(true);
            $table->integer('priority')->default(10);
            $table->integer('tenant_count')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('database_servers');
    }
};
