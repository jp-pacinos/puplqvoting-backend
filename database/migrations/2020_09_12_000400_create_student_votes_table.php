<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentVotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('official_id')->constrained('officials')->cascadeOnDelete();
            $table->foreignId('history_id')->constrained('student_vote_histories')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_votes');
    }
}
