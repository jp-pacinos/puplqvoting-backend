<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_students', function (Blueprint $table) {
            $table->id()->startingValue(1);
            $table->string('student_number')->unique();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('middlename')->nullable();
            $table->string('suffix')->nullable();
            $table->enum('sex', ['MALE', 'FEMALE'])->default('MALE');
            $table->date('birthdate');
            $table->string('email')->unique();
            $table->boolean('can_vote')->default(true);
            $table->foreignId('course_id')->constrained('courses');
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
        Schema::dropIfExists('user_students');
    }
}
