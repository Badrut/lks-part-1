<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameVersion;
use App\Models\Score;
use App\Models\User;
use Auth;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Str;

class GameController extends Controller
{
    public function index(Request $request, $page=0, $size=10 , $sortBy = 'title' , $sortDir = 'asc')
    {
        $game = Game::with('users','game_version.score')->orderBy($sortBy , $sortDir)->paginate($size,['*'] , 'page', $page);
        $data = [];


        $totalElements = count($game);

        try{

        foreach ($game as $game)
        {
            $scoreCount = $game->game_version->score;



            $data[] = [
                'slug' => $game->slug,
                'title' => $game->title,
                'description' => $game->description,
                'thumbnail' => '/games/'.$game->slug.'/'.$game->game_version->version.'/thumbnail.png',
                'uploadTimestamp' => $game->updated_at,
                'author' => $game->users->username,
                'scoreCount'=> $scoreCount[0]->score,
                'gamePath' => '/games/'.$game->slug.'/1/'
            ]
        ;
    }

    return response()->json([ 'page' => $page , 'size' => $size , 'totalElements' => $totalElements , 'content' => $data], 200);

        }
        catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()] , 500);
        }
    }
    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|min:3|max:60|unique:games,title',
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

            // $score = Score::create([
            //     'user_id' => Auth()->user()->id,
            //     'game_version_id' => $game_version->id,
            //     'score' =>  0,
            // ]);

            DB::commit();

            return response()->json(['status' => 'success', 'slug' => $game->slug ], 201);
        }
        catch (ValidationException $e) {

            DB::rollBack();

            return response()->json(['status' => 'invalid' , 'slug' => 'Game title already exists'], 400);
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
            return response()->json(['error' => $e->errors()] , );
        }
        catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $slug)
    {
        $game = Game::where('slug' , $slug)->first();

        $request->validate([
            'title' => 'required|min:3|max:60|unique:games,title,'.$game->id,
            'description' => 'required|max:200',
        ]);

        try {
            if(isset($game))
                {

                    DB::beginTransaction();

                    $valid = $request->validate([
                        'title' => 'required|min:3|max:60',
                        'description' => 'required|max:200',
                    ]);

                    DB::commit();

                    $game->update($valid);

                    return response()->json(['status' => 'success']);

                }
        }catch(ValidationException $e)
        {
            DB::rollBack();
            return response()->json(['error' => $e->errors()], 400);
        }

        catch(Exception $e)
        {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function author(Request $request , $username)
    {
        $user = User::with('game')->where('username' , $username)->get();

        $authorGames = [];
        $highscores = [];

        // dd($user->first()->username);
        foreach($user as $game)
        {
        // dd($game->game->game_version->score->first()->score);
         $authorGames[] = [
             'slug' => $game->game->slug,
             'title' => $game->game->title,
             'description' => $game->game->description,
         ];
         $highscores[] = [
            'game' =>
            [
                'slug' => $game->game->slug,
                'title' => $game->game->title,
                'description' => $game->game->description,
            ],
            'score' => $game->game->game_version->score->first()->score,
            'timestamp' => $game->game->game_version->score->first()->created_at,
         ];


        }

        return response()->json(['username' => $user->first()->username , 'registerTimestamp' => $user->first()->created_at ,  'authorGames' => $authorGames, 'highscores' => $highscores], 200);

        if (!$user) {
            return response()->json(['message' => 'Game not found'], 404);
        }

        return response()->json(['author' => $user], 200);
    }
}
