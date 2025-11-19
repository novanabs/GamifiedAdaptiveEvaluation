<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    use HasFactory;

    protected $table = 'classes';
    protected $fillable = ['name', 'description', 'level','grade','semester', 'token', 'created_by'];

    // (5) belongsTo User
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // (3) Many to Many with Student
    public function students()
    {
        return $this->belongsToMany(User::class, 'student_classes', 'id_class', 'id_student');
    }

    // (4) Many to Many with Teacher
    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_classes', 'id_class', 'id_teacher');
    }

    // (6) hasMany Subject
    public function subjects()
    {
        return $this->hasMany(Subject::class, 'id_class');
    }
}
