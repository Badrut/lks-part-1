<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameVersion extends Model
{
    protected $guarded = ['id'];

    public function score()
    {
        return $this->hasMany(Score::class , 'game_version_id');
    }

    public function game()
    {
        return $this->belongsTo(Game::class , 'id');
    }
}
