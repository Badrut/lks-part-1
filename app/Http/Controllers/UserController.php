<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Log;

class UserController extends Controller
{
    public function index()
    {
        try {
            $user = User::withTrashed()->get();
            $totalElements = count($user);
            $data = [];

            foreach ($user as $u) {

                $data[] = [
                    'username' => $u->username,
                    'last_login_at' => $u->last_login_at,
                    'created_at' => $u->created_at,
                    'updated_at' => $u->updated_at,
                    'deleted_at' => $u->deleted_at,
                ];
            }
            return response()->json(['totalElements' => $totalElements , 'content' => $data], 200);

        }
        catch (\Exception $e) {
            Log::error('Failed to fetch users' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function block($id)
    {

    }
}
