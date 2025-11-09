<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_other',
        'type_id_other',
        'nama',
        'email',
        'password',
        'role'
    ];

    // (1) User has many UserBadge
    public function userBadges()
    {
        return $this->hasMany(UserBadge::class, 'id_student');
    }

    // (3) Many to Many with Class (as Student)
    public function studentClasses()
    {
        return $this->belongsToMany(Classes::class, 'student_classes', 'id_student', 'id_class');
    }

    // (4) Many to Many with Class (as Teacher)
    public function teacherClasses()
    {
        return $this->belongsToMany(Classes::class, 'teacher_classes', 'id_teacher', 'id_class');
    }

    // (5) User has many Classes created by them
    public function createdClasses()
    {
        return $this->hasMany(Classes::class, 'created_by');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
