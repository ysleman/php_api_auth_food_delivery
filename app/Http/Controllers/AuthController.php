<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $username = $request->username;
        $email = $request->email;
        $address=$request->address;
        $password = $request->password;
        $firstname=$request->firstname;
        $lastname=$request->lastname;
        $phone=$request->phone;
        $birthdate=$request->birthdate;
        $temp_password=$request->temppassword;
        $img=$request->img;

        // Check if field is empty
        if (empty($username) or empty($email) or empty($password) or empty($firstname) or empty($lastname) or empty($phone) or empty($birthdate) 
        or empty($img) or empty($address)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
        }

        // Check if email is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['status' => 'error', 'message' => 'You must enter a valid email']);
        }

        // Check if password is greater than 5 character
        if (strlen($password) < 6) {
            return response()->json(['status' => 'error', 'message' => 'Password should be min 6 character']);
        }

        // Check if user already exist
        if (User::where('email', '=', $email)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'User already exists with this email']);
        }

        // Create new user
        try {
            $user = new User();
            $user->username = $request->username;
            $user->email = $request->email;
            $user->password = app('hash')->make($request->password);
            $user->firstname=$request->firstname;
            $user->lastname=$request->lastname;
            $user->address=$request->address;
            $user->phone=$request->phone;
            $user->birthDate=$request->birthdate;
            $user->img=$request->img;
            if ($user->save()) {
                return $this->login($request);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    

    /**
     * Log the user out (Invalidate the token).
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
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }


    public function loginAdmin(Request $request)
    {
        $username = $request->username;
        $password = $request->password;
        $admin=$request->admin;
        
        // Check if field is empty
        if (empty($username) or empty($password) or empty($admin)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
        }

        $credentials = request(['username', 'password','admin']);
        if (!$token = auth($guard='admin')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function upadtepassword(Request $request)
    {
        $username = $request->username;
        $tokentap = $request->tokentap;
        $newpassword = $request->newpassword;
        // Check if field is empty
        if (empty($username) or empty($tokentap) or empty($newpassword)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
        }

        // Check if email is valid
        if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['status' => 'error', 'message' => 'You must enter a valid email']);
        }

        

        // Check if user already exist
        if (User::where('username', '=', $username)->exists() && User::where('temppassword','=',$tokentap)->exists()) {
            try {
                $user = User::where('email',$username)->first();
                $user->tokentap=NULL;
                $user->password = app('hash')->make($request->newpassword);
                if ($user->save()) {
                    return response()->json(['status'=>'sucess','message'=>$user]);
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

        

        // Check if user already exist
        if (User::where('email', '=', $email)->exists()) {
            try {
                $user = User::where('email',$email)->first();
                $test="xxx@hello.com";
                $user->tokentap = $test;
                if ($user->save()) {
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