<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBadge extends Model
{
    use HasFactory;

    protected $table = 'user_badge';
    protected $fillable = ['id_student', 'id_badge'];

    // (1) belongsTo User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_student');
    }

    // (2) belongsTo Badge
    public function badge()
    {
        return $this->belongsTo(Badge::class, 'id_badge');
    }
}
