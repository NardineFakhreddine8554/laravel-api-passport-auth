<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthenticationController extends Controller
{

    public function register(Request $request)
    {

        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed|min:6',
            'role' => 'required|exists:roles,name' // Add role validation
        ]);
        $role = Role::where('name', $request->role)->first();

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $role->id // Assign role ID based on the role name
        ];

        // $user['password'] = bcrypt($request->password);

        $user = User::create($user);

        // Generate access token
        $token = $user->createToken('passportToken')->accessToken;


        // Return user and token
        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201); //201 Created
    }

    public function login(Request $request)

    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials.'
            ], 401);
        }

        $user = Auth::user();
        // dd($user);
        // Load the user with the role
        $user = Auth::user()->load('role');
        $token = $user->createToken('passportToken')->accessToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        // Revoke the current access token
        if ($user && $user->token()) {
            $user->token()->revoke();
        }

        return response()->json([
            'message' => 'Successfully logged out.'
        ], 200);
    }


    public function user(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized access. Please login.'
            ], 401);
        }
        return response()->json([
            'user' => $user
        ], 200);
    }
}
