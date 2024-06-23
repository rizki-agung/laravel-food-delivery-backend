<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //user register
    public function userRegister(Request $request)
    {
        $request->validate([
           'name' => 'required|string',
           'email' => 'required|email|unique:users,email',
           'password' => 'required|string|min:6',
           'phone' => 'required|string',
        ]);

        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        $data['roles'] = 'user';

        $user = User::create($data);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'data' => $user
        ]);
    }

    //login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized',
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'User logged in successfully',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }

    //logout
    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User logged out successfully',
        ]);
    }

    //restaurant register
    public function restaurantRegister(Request $request)
    {
        $request->validate([
           'name' => 'required|string',
           'email' => 'required|email|unique:users,email',
           'password' => 'required|string|min:6',
           'phone' => 'required|string',
           'restaurant_name' => 'required|string',
           'restaurant_address' => 'required|string',
           'photo' => 'required|image',
           'latlong' => 'required|string',
        ]);

        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        $data['roles'] = 'restaurant';

        $user = User::create($data);

        //check if photo is uploaded
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $filename = time() . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('images'), $filename);
            $user->photo = $filename;
            $user->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Restaurant registered successfully',
            'data' => $user
        ]);
    }

    //driver register
    public function driverRegister(Request $request)
    {
        $request->validate([
           'name' => 'required|string',
           'email' => 'required|email|unique:users,email',
           'password' => 'required|string|min:6',
           'phone' => 'required|string',
           'license_plate' => 'required|string',
           'photo' => 'required|image',
        ]);

        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        $data['roles'] = 'driver';

        $user = User::create($data);

        //check if photo is uploaded
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $filename = time() . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('images'), $filename);
            $user->photo = $filename;
            $user->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Driver registered successfully',
            'data' => $user
        ]);
    }

    //update latlong of user
    public function updateLatLong(Request $request)
    {
        $request->validate([
            'latlong' => 'required|string',
        ]);

        $data = $request->user();
        $data->latlong = $request->latlong;
        $data->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Latlong updated successfully',
            'data' => $data
        ]);
    }

    //get all restaurants
    public function getRestaurants()
    {
        $restaurants = User::where('roles', 'restaurant')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Get all restaurants successfully',
            'data' => $restaurants
        ]);
    }
}
