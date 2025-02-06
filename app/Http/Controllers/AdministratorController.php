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

        return response()->json(['totalElements' =>  $totalElements,'content' => $admin], 200);
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

        DB::beginTransaction();

        $user = User::create([
            'username' => $reqeuast->username,
            'password' => bcrypt($reqeuast->password),
        ]);

        DB::commit();

        return response()->json(['data' => $user], 201);
      }

      catch (ValidationException $e){
        return response()->json([
            'status' => 'invalid',
            'message' => $e->errors(),
            ] , 400);
      }
      catch (\Exception $e){
        DB::rollback();
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

        DB::beginTransaction();

        $user = User::findOrFail($id);
        $user->update($update);

        DB::commit();

        return response()->json(['status' => 'success' , 'username' => $user->username], 201);
       }catch(ValidationException $e)
       {
        DB::rollBack();
        return response()->json([
            'error' => 'Validation failed',
            'message' => $e->errors()
            ], 400);
       }catch(\Exception $e)
       {
        DB::rollback();
        Log::error("User update failed :" . $e->getMessage());
        return response()->json(['error' => 'User update failed'], 500);
       }
    }

    public function delete($id)
    {

        try{
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json(['message' => 'User deleted successfully'], 204);
        }
        catch(\Exception $e){
            return response()->json(['status' => 'not found' , 'message' => 'User not found'], 403);
        }
    }
}
