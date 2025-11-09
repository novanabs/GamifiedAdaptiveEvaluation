<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    protected $table = 'badge';
    protected $fillable = ['name', 'description'];

    // (2) hasMany UserBadge
    public function userBadges()
    {
        return $this->hasMany(UserBadge::class, 'id_badge');
    }
}
