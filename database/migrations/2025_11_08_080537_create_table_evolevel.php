<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_classes', function (Blueprint $table) {
            $table->unsignedBigInteger('id_student');
            $table->unsignedBigInteger('id_class');
            $table->timestamps();
        });
        Schema::create('teacher_classes', function (Blueprint $table) {
            $table->unsignedBigInteger('id_teacher');
            $table->unsignedBigInteger('id_class');
            $table->timestamps();
        });
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('level');
            $table->string('token')->unique();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });
        Schema::create('subject', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('id_class');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->string(column: 'title');
            $table->string(column: 'description');
            $table->unsignedBigInteger('id_subject');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string(column: 'title');
            $table->enum('status', ['basic', 'additional', 'remedial']);
            $table->enum('type', ['task', 'quiz']);
            $table->dateTime('deadline')->nullable();
            $table->decimal('result', 5, 2)->nullable();
            $table->enum('result_status', ['Pass', 'Remedial'])->nullable();
            $table->integer('poin')->default(0);
            $table->unsignedBigInteger('id_topic');
            $table->timestamps();
        });
        Schema::create('activity_question', function (Blueprint $table) {
            $table->unsignedBigInteger('id_activity');
            $table->unsignedBigInteger('id_question');
            $table->timestamps();
        });
        Schema::create('question', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['MultipleChoice', 'ShortAnswer']);
            $table->json('question');
            $table->json('MC_option')->nullable();
            $table->json('SA_answer')->nullable();
            $table->char('MC_answer')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });
        Schema::create('user_badge', function (Blueprint $table) {
            $table->unsignedBigInteger('id_student');
            $table->unsignedBigInteger('id_badge');
            $table->timestamps();
        });
        Schema::create('badge', function (Blueprint $table) {
            $table->id();
            $table->string(column: 'name');
            $table->string(column: 'description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('student_classes');
        Schema::dropIfExists('teacher_classes');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('subject');
        Schema::dropIfExists('topics');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('activity_question');
        Schema::dropIfExists('question');
        Schema::dropIfExists('user_badge');
        Schema::dropIfExists('badge');
    }
};
