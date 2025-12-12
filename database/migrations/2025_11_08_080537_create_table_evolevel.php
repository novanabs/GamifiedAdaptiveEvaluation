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
            $table->enum('level', ['SD', 'Mi', 'SMP', 'Mts', 'SMA', 'SMK', 'MA', 'PT']);
            $table->enum('grade', ['1', '2', '3', '4']);
            $table->enum('semester', ['odd', 'even']);
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
            $table->enum('addaptive', ['yes', 'no']);
            $table->enum('status', ['basic', 'additional', 'remedial']);
            $table->enum('type', ['task', 'quiz']);
            $table->integer('durasi_pengerjaan')->nullable();
            $table->dateTime('deadline')->nullable();
            $table->integer('jumlah_soal')->nullable();
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
            $table->enum('difficulty', ['mudah', 'sedang', 'sulit']);
            $table->unsignedBigInteger('id_topic');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });
        Schema::create('user_badge', function (Blueprint $table) {
            $table->unsignedBigInteger('id_student');
            $table->unsignedBigInteger('id_badge');
            $table->unsignedBigInteger('id_class')->nullable();
            $table->timestamps();
        });
        Schema::create('badge', function (Blueprint $table) {
            $table->id();
            $table->string(column: 'name');
            $table->string(column: 'description');
            $table->string(column: 'path_icon');
            $table->timestamps();
        });
        Schema::create('activity_result', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_activity');
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->enum('result_status', ['Pass', 'Remedial'])->nullable();
            $table->decimal('result', 5, 2)->nullable();
            $table->integer('real_poin')->default(0)->nullable();
            $table->integer('bonus_poin')->default(0)->nullable();
            $table->integer('waktu_mengerjakan')->nullable();
            $table->integer('total_benar')->nullable();
            $table->boolean('status_benar')->default(false)->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->timestamps();
        });
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string(column: 'name');
            $table->integer(column: 'value');
        });
        // --- activity_packages
        Schema::create('activity_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_activity')->comment('sumber activity');
            $table->unsignedBigInteger('created_by')->nullable()->comment('guru yang membuat paket');
            $table->unsignedBigInteger('id_class')->nullable()->comment('kelas sumber (opsional)');
            $table->string('title')->nullable();
            $table->string('filename')->comment('path file JSON di storage');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('id_activity');
            $table->index('created_by');
            $table->index('id_class');
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
        Schema::dropIfExists('activity_result');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('activity_packages');


    }
};
