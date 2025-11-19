<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    use HasFactory;

    protected $table = 'topics';
    protected $fillable = ['title', 'description', 'id_subject', 'created_by'];

    // (7) belongsTo Subject
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'id_subject');
    }

    // (8) hasMany Activities
    public function activities()
    {
        return $this->hasMany(Activity::class, 'id_topic');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'id_topic');
    }
}
