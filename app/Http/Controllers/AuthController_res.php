<?php

namespace App\Http\Controllers;

use App\Models\resturants;
use Illuminate\Http\Request;

class AuthController_res extends Controller
{
    public function register(Request $request)
    {
        $name = $request->name;
        $username = $request->username;
        $password = $request->password;
        $phone=$request->phone;
        $rating=$request->rating;
        $address=$request->address;
       
        // Check if field is empty
        if (empty($name) or empty($username) or empty($password) or empty($phone) or empty($address) or empty($rating) ) {
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

        // Check if resturants already exist
        if (resturants::where('username', '=', $username)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'resturants already exists with this email']);
        }

        // Create new resturants
        try {
            $resturants = new resturants();
            $resturants->name = $request->name;
            $resturants->username = $request->username;
            $resturants->phone=$request->phone;
            $resturants->rating=$request->rating;
            $resturants->address=$request->address;
            $resturants->password = app('hash')->make($request->password);
            if ($resturants->save()) {
                return $this->login($request);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    

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