<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentClasses extends Model
{
    use HasFactory;

    protected $table = 'student_classes';
    protected $fillable = ['id_student', 'id_class'];

    // (3) belongsTo User (student)
    public function student()
    {
        return $this->belongsTo(User::class, 'id_student');
    }

    // (3) belongsTo Class
    public function class()
    {
        return $this->belongsTo(Classes::class, 'id_class');
    }
}
