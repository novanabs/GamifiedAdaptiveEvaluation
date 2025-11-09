<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityQuestion extends Model
{
    use HasFactory;

    protected $table = 'activity_question';
    protected $fillable = ['id_activity', 'id_question'];

    // (10) belongsTo Question
    public function question()
    {
        return $this->belongsTo(Question::class, 'id_question');
    }

    // belongsTo Activity
    public function activity()
    {
        return $this->belongsTo(Activity::class, 'id_activity');
    }
}
