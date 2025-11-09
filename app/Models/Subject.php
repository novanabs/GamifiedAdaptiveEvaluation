<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $table = 'subject';
    protected $fillable = ['name', 'id_class', 'created_by'];

    // (6) belongsTo Class
    public function class()
    {
        return $this->belongsTo(Classes::class, 'id_class');
    }

    // (7) hasMany Topics
    public function topics()
    {
        return $this->hasMany(Topic::class, 'id_subject');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
