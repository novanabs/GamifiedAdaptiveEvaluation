<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $table = 'activities';
    protected $fillable = [
        'title',
        'status',
        'type',
        'deadline',
        'id_topic',
        'addaptive',
        'durasi_pengerjaan',
        'jumlah_soal',
        'kkm'
    ];

    protected $casts = [
        'deadline' => 'datetime', 
    ];
    public function topic()
    {
        return $this->belongsTo(Topic::class, 'id_topic');
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'activity_question', 'id_activity', 'id_question');
    }

    // Satu activity punya banyak hasil (banyak siswa)
    public function activityResults()
    {
        return $this->hasMany(ActivityResult::class, 'id_activity');
    }

    // kalau mau singkat akses satu nilai (mis. latest) tetap bisa pakai hasOne, tetapi default adalah hasMany
    public function nilai()
    {
        return $this->hasMany(ActivityResult::class, 'id_activity');
    }
}
