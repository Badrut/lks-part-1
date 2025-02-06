<?php

namespace App\Http\Controllers;

use App\Models\Administrator;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{



    public function SignIn(Request $request)
    {

        try {
            $credentials = $request->validate([
                'username' => 'required|min:4|max:60',
                'password' => 'required|min:5',
            ]);

            DB::beginTransaction();

            if (Auth::guard('web')->attempt($credentials)) {
                $user = Auth::guard('web')->user();

                if (in_array($user->username, ['dev1', 'dev2'])) {
                    $token = $user->createToken('DevToken', ['dev'])->plainTextToken;

                    DB::commit();

                    return response()->json([
                        'token' => $token,
                        'dev' => $user
                    ], 200);
                }
                else {
                    $token = $user->createToken('UserToken')->plainTextToken;

                    DB::commit();

                    return response()->json([
                        'token' => $token,
                        'user' => $user
                    ], 200);
                }
            }

            if (Auth::guard('admin')->attempt($credentials)) {
                $admin = Auth::guard('admin')->user();
                $token = $admin->createToken('AdminToken')->plainTextToken;

                DB::commit();

                return response()->json([
                    'token' => $token,
                    'admin' => $admin
                ], 200);
            }

            DB::rollBack();
            return response()->json(['error' => 'Invalid credentials'], 401);

        }catch (ValidationException $e) {
            {
                DB::rollback();
                return response()->json([
                    'error' => 'Validation failed',
                    'message' => $e->errors()
                ] , 401);

            }
        }catch (\Exception $e) {
            DB::rollBack();
            Log::error("Login Failed: ". $e->getMessage());
            return response()->json(['error' => 'Failed to login' , 'message' => $e->getMessage()], 500);
        }
    }

    public function SignOut()
    {
        try{

            if(!Auth::user()) {
                throw new \Exception("User is not authenticated" , code: 401);
            }
            Auth::user()->currentAccessToken()->delete();

            return response()->json(['success' => 'bisa boyy'], 200);
        }
        catch (\Exception $e) {
           Log::error('Logout Failed' > $e->getMessage());
           return response()->json([
            'error' => 'Logout Failed',
            'message' => $e->getMessage()
           ], $e->getCode() ?: 500);
        }
    }

    public function  SignUp(Request $reqeuast)
    {
       try {
        $reqeuast->validate([
            'username' =>'required|min:4|max:60',
            'password' => 'required|min:5',
        ]);

        DB::beginTransaction();

        $user = User::create([
            'username' => $reqeuast->username,
            'password' => $reqeuast->password
        ]);

        $token = $user->createToken('tokens')->plainTextToken;

        DB::commit();

        return response()->json([
            'token' => $token,
            'user' => $user
        ], 201);
       }
       catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->errors()
            ], 401);

       } catch (\Exception $e) {
            DB::rollback();
            Log::error('Registration Failed' > $e->getMessage());
            return response()->json([
                'error' => 'User registration failed',
                'message' => $e->getMessage()
            ] , 500);
       }
    }


    public function SignUpAdmin(Request $reqeuast)
    {
      try {
        $reqeuast->validate([
            'username' =>'required|min:4|max:60',
            'password' => 'required|min:5',
        ]);

        DB::beginTransaction();

        $user = Administrator::create([
            'username' => $reqeuast->username,
            'password' => bcrypt($reqeuast->password),
        ]);

        $token = $user->createToken('AdminToken')->plainTextToken;

        DB::commit();

        return response()->json([
            'token' => $token,
            'user' => $user
        ], 201);
      } catch (ValidationException $e)
      {

        DB::rollback();
        return response()->json([
            'error' => 'Admin registration failed',
            'message' => $e->errors(),
        ], 401);

      }
      catch (\Exception $e) {

        DB::rollback();
        Log::error('admin registration failed:' . $e->getMessage());
        return response()->json([
            'error' => 'Admin registration failed',
           'message' => $e->getMessage()
        ] , 500);
      }
    }
}
