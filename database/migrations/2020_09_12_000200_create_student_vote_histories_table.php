<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentVoteHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_vote_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('user_students')->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('sessions')->cascadeOnDelete();
            $table->timestamp('verified_at')->nullable();
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
        Schema::dropIfExists('student_vote_histories');
    }
}
