<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityResult extends Model
{
    protected $table = 'activity_result';
    protected $fillable = [
        'id_activity',
        'id_user',
        'result',
        'result_status',
        'real_poin',
        'bonus_poin',
        'waktu_mengerjakan',
        'start_time', 
        'end_time'
    ];
    protected $dates = ['start_time', 'end_time'];
    public function nilaiSiswa()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function aktivitas()
    {
        return $this->belongsTo(Activity::class, 'id_activity');
    }

}
