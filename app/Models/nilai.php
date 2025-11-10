<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class nilai extends Model
{
    protected $table = 'nilai';
    protected $fillable = [
        'id_activity',
        'id_user',
        'result',
        'result_status',
        'poin',
    ];
    public function nilaiSiswa()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function aktivitas()
    {
        return $this->belongsTo(Activity::class, 'id_activity');
    }

}
