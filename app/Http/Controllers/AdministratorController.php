<?php

namespace App\Http\Controllers;

use App\Models\Administrator;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Log;

class AdministratorController extends Controller
{
    public function index()
    {
        try {
            $admin = Administrator::all();
            $totalElements = count($admin);

        return response()->json(['totalElements' =>  $totalElements, 'content' => $admin], 200);
        }
        catch (\Exception $e) {
            Log::error('Failed to fetch administrators' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    public function store(Request $reqeuast)
    {
      try {
        $reqeuast->validate([
            'username' =>'required|min:4|max:60|unique:users,username',
            'password' => 'required|min:5',
        ]);

        $user = User::create([
            'username' => $reqeuast->username,
            'password' => bcrypt($reqeuast->password),
        ]);

        return response()->json(['status' => 'success' , 'username' => $user->username], 201);
      }

      catch (ValidationException $e){
        return response()->json([
            'status' => 'invalid',
            'message' => 'Username already exists',
            ] , 400);
      }
      catch (\Exception $e){
        Log::error("User creation failed :" . $e->getMessage());
        return response()->json(['error' => 'User creation failed'], 500);
      }

    }

    public function update(Request $reqeuast , $id)
    {
       try {
        $update = $reqeuast->validate([
            'username' =>'required|min:4|max:60|unique:users,username',
            'password' => 'required|min:5',
        ]);


        $user = User::findOrFail($id);
        $user->update($update);

        return response()->json(['status' => 'success' , 'username' => $user->username], 201);
       }catch(ValidationException $e)
       {
        return response()->json([
            'status' => 'invalid',
            'message' => 'Username already exists',
            ], 400);
       }catch(\Exception $e)
       {
        Log::error("User update failed :" . $e->getMessage());
        return response()->json(['error' => 'User update failed'], 500);
       }
    }


    public function delete($username)
    {


        try{
            $user = User::where('username' ,$username)->first();
            $user->delete();

            return response()->json(['message' => 'User deleted successfully'], 204);
        }
        catch(\Exception $e){
            return response()->json(['status' => 'not found' , 'message' => 'User not found'], 403);
        }
    }

    public function undelete($username)
    {
        try {
            $user = User::withTrashed()->where('username', $username)->first();
            $user->restore();

            return response()->json(["message" => "user unblock successfully"]);
        }
        catch(\Exception $e)
        {
            return response()->json(["status" => "not found", "message" => "User not found"]);
        }
    }
}
