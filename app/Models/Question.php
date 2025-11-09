<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $table = 'question';
    protected $fillable = ['type', 'question', 'MC_option', 'SA_answer', 'MC_answer', 'created_by'];

    // (9) manyToMany with Activity
    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'activity_question', 'id_question', 'id_activity');
    }

    // (10) oneToOne with ActivityQuestion
    public function activityQuestion()
    {
        return $this->hasOne(ActivityQuestion::class, 'id_question');
    }
}
