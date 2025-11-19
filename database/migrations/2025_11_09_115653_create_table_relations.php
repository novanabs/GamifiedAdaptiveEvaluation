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
        // (1) users → user_badge (1 to M)
        Schema::table('user_badge', function (Blueprint $table) {
            $table->foreign('id_student')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        // (2) user_badge → badge (M to 1)
        Schema::table('user_badge', function (Blueprint $table) {
            $table->foreign('id_badge')
                ->references('id')
                ->on('badge')
                ->onDelete('cascade');
        });

        // (3) classes ↔ student_classes (M to M)
        Schema::table('student_classes', function (Blueprint $table) {
            $table->foreign('id_student')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('id_class')
                ->references('id')
                ->on('classes')
                ->onDelete('cascade');
        });

        // (4) classes ↔ teacher_classes (M to M)
        Schema::table('teacher_classes', function (Blueprint $table) {
            $table->foreign('id_teacher')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('id_class')
                ->references('id')
                ->on('classes')
                ->onDelete('cascade');
        });

        // (5) users → classes (1 to M)
        Schema::table('classes', function (Blueprint $table) {
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        // (6) classes → subject (1 to M)
        Schema::table('subject', function (Blueprint $table) {
            $table->foreign('id_class')
                ->references('id')
                ->on('classes')
                ->onDelete('cascade');
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        // (7) subject → topics (1 to M)
        Schema::table('topics', function (Blueprint $table) {
            $table->foreign('id_subject')
                ->references('id')
                ->on('subject')
                ->onDelete('cascade');
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        // (8) topics → activities (1 to M)
        Schema::table('activities', function (Blueprint $table) {
            $table->foreign('id_topic')
                ->references('id')
                ->on('topics')
                ->onDelete('cascade');
        });

        // (9) activities ↔ question (M to M)
        Schema::table('activity_question', function (Blueprint $table) {
            $table->foreign('id_activity')
                ->references('id')
                ->on('activities')
                ->onDelete('cascade');
            $table->foreign('id_question')
                ->references('id')
                ->on('question')
                ->onDelete('cascade');
        });

        // (10) question ↔ activity_question (1 to 1)
        // Sudah diwakili oleh foreign key `id_question` di atas

        //user dengan nilai
        Schema::table('activity_result', function (Blueprint $table) {
            $table->foreign('id_user')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('id_activity')
                ->references('id')
                ->on('activities')
                ->onDelete('cascade');
        });

        //question dengan topic
        Schema::table('question', function (Blueprint $table) {
            $table->foreign('id_topic')
                ->references('id')
                ->on('topics')
                ->onDelete('cascade');
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_badge', function (Blueprint $table) {
            $table->dropForeign(['id_student']);
            $table->dropForeign(['id_badge']);
        });

        Schema::table('student_classes', function (Blueprint $table) {
            $table->dropForeign(['id_student']);
            $table->dropForeign(['id_class']);
        });

        Schema::table('teacher_classes', function (Blueprint $table) {
            $table->dropForeign(['id_teacher']);
            $table->dropForeign(['id_class']);
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        Schema::table('subject', function (Blueprint $table) {
            $table->dropForeign(['id_class']);
            $table->dropForeign(['created_by']);
        });

        Schema::table('topics', function (Blueprint $table) {
            $table->dropForeign(['id_subject']);
            $table->dropForeign(['created_by']);
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['id_topic']);
        });

        Schema::table('activity_question', function (Blueprint $table) {
            $table->dropForeign(['id_activity']);
            $table->dropForeign(['id_question']);
        });
        Schema::table('activity_result', function (Blueprint $table) {
            $table->dropForeign(['id_user']);
            $table->dropForeign(['id_badge']);
        });
        Schema::table('question', function (Blueprint $table) {
            $table->dropForeign(['id_topic']);
            $table->dropForeign(['created_by']);
        });
    }
};
