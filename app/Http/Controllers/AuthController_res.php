<?php

namespace App\Http\Controllers;

use App\Models\resturants;
use Illuminate\Http\Request;

class AuthController_res extends Controller
{    

    /**
     * Log the resturants out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function login(Request $request)
    {
        $username = $request->username;
        $password = $request->password;

        // Check if field is empty
        if (empty($username) or empty($password)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
        }

        $credentials = request(['username', 'password']);
        
        if (!$token = auth($guard='resturants')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized', 'message'=>$token], 401);
        }

        return $this->respondWithToken($token);
    }


    public function upadtepassword(Request $request)
    {
        $email = $request->email;
        $tokentap = $request->tokentap;
        $newpassword = $request->newpassword;
        // Check if field is empty
        if (empty($email) or empty($tokentap) or empty($newpassword)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
        }

        // Check if email is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['status' => 'error', 'message' => 'You must enter a valid email']);
        }

        

        // Check if resturants already exist
        if (resturants::where('email', '=', $email)->exists() && resturants::where('tokentap','=',$tokentap)->exists()) {
            try {
                $resturants = resturants::where('email',$email)->first();
                $resturants->tokentap=NULL;
                $resturants->password = app('hash')->make($request->newpassword);
                if ($resturants->save()) {
                    return response()->json(['status'=>'sucess','message'=>$resturants]);
               }
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
        else{
            return response()->json(['status' => 'error', 'message' => 'dont exist']);
        }
        
    }

    public function forgetpassword(Request $request)
    {
        $email = $request->email;

        // Check if field is empty
        if (empty($email)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
        }

        // Check if email is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['status' => 'error', 'message' => 'You must enter a valid email']);
        }

        

        // Check if resturants already exist
        if (resturants::where('email', '=', $email)->exists()) {
            try {
                $resturants = resturants::where('email',$email)->first();
                $test="xxx@hello.com";
                $resturants->tokentap = $test;
                if ($resturants->save()) {
                    return response()->json(['status' => 'sucess', 'message' => $test ]);
                }
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
        else{
            return response()->json(['status' => 'error', 'message' => 'dont exist']);
        }
        
    }
    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}