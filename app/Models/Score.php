<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    protected $guarded = ['id'];

    public function GameVersion()
    {
        return $this->belongsTo(GameVersion::class , 'game_version_id');
    }
}
