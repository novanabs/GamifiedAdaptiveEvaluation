<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherClasses extends Model
{
    use HasFactory;

    protected $table = 'teacher_classes';
    protected $fillable = ['id_teacher', 'id_class'];

    // (4) belongsTo User (teacher)
    public function teacher()
    {
        return $this->belongsTo(User::class, 'id_teacher');
    }

    // (4) belongsTo Class
    public function class()
    {
        return $this->belongsTo(Classes::class, 'id_class');
    }
}
