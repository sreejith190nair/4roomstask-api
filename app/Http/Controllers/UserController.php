<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), User::$registerRules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'contact'    => $request->contact,
            'password'   => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user'    => $user,
        ], 201);
    }

    public function signIn(Request $request){
        $validator = Validator::make($request->all(), [
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$token = JWTAuth::attempt($request->only('email', 'password'))) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'message' => 'Login successful',
            'token'   => $token,
        ], 200);
    }

    public function getAllUsers(Request $request){

        $authUserId = Auth::id();
        $users = User::where('id', '!=', $authUserId)->get();

        return response()->json([
            'message' => 'Users Fetched Successfully',
            'users' => $users
        ], 200);
    }

    public function getCurrentUser(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            return response()->json([
                'status' => 'success',
                'data' => $user
            ], 200);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token is invalid or expired'
            ], 401);
        }
    }


    public function getUserById($id){
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['user' => $user], 200);
    }

    public function updateUserById(Request $request, $id){

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        $authUser = Auth::id();
        if ($authUser === (int) $id){
            return response()->json([
                'status' => 'error',
                'message' => 'User cannot update their own account'
            ], 400);
        }

        $validator = Validator::make($request->all(), User::updateRules($id));
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user->update([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'contact'    => $request->contact,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User Updated successfully',
            'user' => $user,
            'auth' => $authUser
        ]);
    }

    public function deleteUserById($id){
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        $authUser = Auth::id();
        if ($authUser === (int) $id){
            return response()->json([
                'status' => 'error',
                'message' => 'User cannot delete their own account'
            ], 400);
        }

        $user->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully'
        ]);
    }

}
