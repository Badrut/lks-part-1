<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameVersion;
use App\Models\Score;
use Auth;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Str;

class GameController extends Controller
{
    public function index()
    {
        $game = Game::with('users','game_version.score')->orderBy('title', 'ASC')->paginate(10);
        $data = [];
        foreach ($game as $game) {
            $scoreCount = $game->game_version->score->count();



            $data[] = [
                'slug' => $game->slug,
                'title' => $game->title,
                'description' => $game->description,
                'thumbnail' => $game->game_version->storage_path,
                'uploadTimestamp' => $game->updated_at,
                'author' => $game->users->id,
                'scoreCount'=> $scoreCount,

            ]
        ;}

    return response()->json(['games' => $data], 200);
    }
    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|min:3|max:60',
                'description' => 'required|max:200',
            ]);

            DB::beginTransaction();

            $slug = Str::slug($request->title);
            $game  = Game::create([
                'title' => $request->title,
                'slug' => $slug,
                'description' => $request->description,
                'created_by' => Auth()->user()->id,
            ]);

            $game_version  = GameVersion::create([
                'game_id' => $game->id,
                'version' => 'v1',
                'storage_path' => $request->title.'/'.$slug.'/v1/',
            ]);

            $score = Score::create([
                'user_id' => Auth()->user()->id,
                'game_version_id' => $game_version->id,
                'score' =>  0,
            ]);

            DB::commit();

            return response()->json(['message' => 'Game created successfully', 'game' => $game ], 201);
        }
        catch (ValidationException $e) {

            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 500);
        }
        catch (Exception $e) {

            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 201);
        }
    }

    public function show(Request $request, $slug)
    {
        try {
            $game = Game::where('slug', $slug)->first();

            if (!$game) {
                return response()->json(['message' => 'Game not found'], 404);
            }

            return response()->json(['game' => $game], 200);
        }
        catch (ValidationException $e) {
            return response()->json([])
        }
        catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $slug)
    {
        $auth = auth()->user()->id();
        $game = Game::where('created_by', $auth)->first();
        dd($auth);
        if(isset($game))
        {
            $valid = $request->validate([
                'title' => 'required|min:3|max:60',
                'description' => 'required|max:200',
            ]);
            dd($valid);
        }
        else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
}
