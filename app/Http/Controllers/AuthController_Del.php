<?php

namespace App\Http\Controllers;

use App\Models\delivery_drivers;
use Illuminate\Http\Request;

class AuthController_Del extends Controller
{
    public function register(Request $request)
    {
        $name = $request->fullname;
        $username = $request->username;
        $password = $request->password;
        $phone=$request->phone;
       
        // Check if field is empty
        if (empty($name) or empty($username) or empty($password) or empty($phone) ) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
        }

        // // Check if email is valid
        // if (!filter_var($username)) {
        //     return response()->json(['status' => 'error', 'message' => 'You must enter a valid email']);
        // }

        // Check if password is greater than 5 character
        if (strlen($password) < 6) {
            return response()->json(['status' => 'error', 'message' => 'Password should be min 6 character']);
        }

        // Check if delivery_drivers already exist
        if (delivery_drivers::where('username', '=', $username)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'delivery_drivers already exists ']);
        }

        // Create new delivery_drivers
        try {
            $delivery_drivers = new delivery_drivers();
            $delivery_drivers->full_Name = $request->fullname;
            $delivery_drivers->username = $request->username;
            $delivery_drivers->phone=$request->phone;
            $delivery_drivers->password = app('hash')->make($request->password);
            if ($delivery_drivers->save()) {
                return $this->login($request);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    

    /**
     * Log the delivery_drivers out (Invalidate the token).
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