<?php

namespace App\Http\Controllers;

use App\Models\delivery_drivers;
use Illuminate\Http\Request;

class AuthController_Del extends Controller
{
   

    /**
     * Log the delivery_drivers out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth($guard='delivery_drivers')->logout();

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
        
        if (!$token = auth($guard='delivery_drivers')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized', 'message'=>$token], 401);
        }

        return $this->respondWithToken($token);
    }


    public function upadtepassword(Request $request)
    {
        $email = $request->email;
        $newpassword = $request->newpassword;
        // Check if field is empty
        if (empty($email) or empty($newpassword)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
        }

        // Check if email is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['status' => 'error', 'message' => 'You must enter a valid email']);
        }

        

        // Check if delivery_drivers already exist
        if (delivery_drivers::where('email', '=', $email)->exists()) {
            try {
                $delivery_drivers = delivery_drivers::where('email',$email)->first();
                $delivery_drivers->password = app('hash')->make($request->newpassword);
                if ($delivery_drivers->save()) {
                    return response()->json(['status'=>'sucess','message'=>$delivery_drivers]);
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

        

        // Check if delivery_drivers already exist
        if (delivery_drivers::where('email', '=', $email)->exists()) {
            try {
                $delivery_drivers = delivery_drivers::where('email',$email)->first();
                $test="xxx@hello.com";
                $delivery_drivers->tokentap = $test;
                if ($delivery_drivers->save()) {
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