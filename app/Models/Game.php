<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $guarded = ['id'];

    public function users()
    {

        return $this->belongsTo(User::class, 'created_by');
    }
    public function game_version()
    {
        return $this->belongsTo(GameVersion::class, 'id');
    }

}
