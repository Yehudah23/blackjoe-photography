<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use App\Models\AdminModel;

class AdminController extends Controller
{
    
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        
        $admin = AdminModel::first();

        $valid = false;
        if ($admin && !empty($admin->password)) {
            if (Hash::check($request->password, $admin->password)) {
                $valid = true;
            }
        }

      
        if (! $valid) {
            $adminPassword = env('ADMIN_PASSWORD');
            if ($adminPassword && $request->password === $adminPassword) {
                $valid = true;
            }
        }

        if (! $valid) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        
        Session::put('admin_authenticated', true);
        Session::put('admin_login_time', now());

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'role' => 'admin',
                'authenticated' => true,
            ]
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Session::forget('admin_authenticated');
        Session::forget('admin_login_time');
        
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $request): JsonResponse
    {
        if (Session::get('admin_authenticated')) {
            return response()->json([
                'role' => 'admin',
                'authenticated' => true,
                'login_time' => Session::get('admin_login_time'),
            ]);
        }

        return response()->json(['authenticated' => false], 401);
    }

    
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'nullable|string',
            'new_password' => 'required|string|min:6',
        ]);

        $admin = AdminModel::first();

        
        if ($admin && $admin->password) {
            if ($request->filled('current_password')) {
                if (! Hash::check($request->input('current_password'), $admin->password)) {
                    return response()->json(['message' => 'Current password is incorrect'], 422);
                }
            }

            $admin->password = Hash::make($request->input('new_password'));
            $admin->save();
        } else {
            
            AdminModel::create([
                'name' => 'admin',
                'password' => Hash::make($request->input('new_password')),
            ]);
        }

        return response()->json(['message' => 'Password updated']);
    }
}
