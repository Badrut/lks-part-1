<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Score;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ScoreController extends Controller
{
    public function index(Request $request, $slug)
{
    $game = Game::with('users', 'game_version.score')->where('slug', $slug)->first();

    if (!$game) {
        return response()->json(['message' => 'Game not found'], 404);
    }

    $scores = [];
    foreach ($game->game_version as $version) {
        foreach ($version->score as $scoreModel) {
            $scores[] = [
                'username'  => $game->users->first()->username ?? 'N/A',
                'score'     => $scoreModel->score,
                'timestamp' => $scoreModel->created_at,
            ];
        }
    }

    return response()->json(['scores' => $scores], 200);
}

    public function store(Request $request, $slug)
    {

        $request->validate([
            'score' => 'required|numeric',
        ]);

        $user = auth()->user()->id;
        // dd($user);
        try {
            $score = Score::where('user_id', $user)->first();
            $game = Game::with('game_version.score')->where('slug' , $slug)->get();

            if($score)
            {
                $score->increment('score', $request->score);
            }

            else {
        foreach ($game as $g) {
                    $score = Score::create([
                        'user_id' => $user,
                        'game_version_id' => $g->game_version->id,
                        'score' => $request->score,
                    ]);

                }
            }
            return response()->json(['status' => 'success'] , 201);
        }catch (ValidationException $e) {
            return response()->json(['status' => 'invalid'] , 400);
        }



    }
}
