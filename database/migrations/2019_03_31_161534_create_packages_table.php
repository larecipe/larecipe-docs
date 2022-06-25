<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('author_name');
            $table->string('email');
            $table->string('package_name');
            $table->string('package_type');
            $table->string('packagist_namespace');
            $table->string('packagist_project_name')->unique();
            $table->string('url');
            $table->string('description');
            $table->string('photo')->nullable();
            $table->string('readme_link')->nullable();
            $table->boolean('approved')->default(false);
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
        Schema::dropIfExists('packages');
    }
}
