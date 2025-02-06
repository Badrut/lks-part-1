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
            $user = User::all();
            $totalElements = count($user);

            return response()->json(['totalElements' => $totalElements , 'data' => $user], 200);

        }
        catch (\Exception $e) {
            Log::error('Failed to fetch users' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
