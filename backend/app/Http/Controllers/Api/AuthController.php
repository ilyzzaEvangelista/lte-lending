<?php

namespace App\Http\Controllers\Api;

use App\Models\Admin;
use App\Models\Customer;
use App\Support\ActivityLogger;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'age' => ['required', 'integer', 'min:13', 'max:120'],
            'gender' => ['required', 'string', 'in:male,female,other,prefer_not_to_say'],
            'address' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:60', 'unique:customers,email'],
            'contact' => ['required', 'string', 'max:20'],
            'username' => ['required', 'string', 'max:60', 'unique:customers,username', 'regex:/^[a-zA-Z0-9._-]+$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $customer = Customer::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'age' => $data['age'],
            'gender' => $data['gender'],
            'address' => $data['address'],
            'email' => $data['email'],
            'contact' => $data['contact'],
            'username' => $data['username'],
            'password' => $data['password'],
            'status' => 1,
        ]);

        ActivityLogger::record($customer, 'Customer registered', $customer->username);

        $token = $customer->createToken('spa')->plainTextToken;

        return response()->json([
            'user' => $this->customerPayload($customer),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $customer = Customer::where('email', $credentials['email'])->first();

        if (! $customer || ! Hash::check($credentials['password'], $customer->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if ((int) $customer->status !== 1) {
            throw ValidationException::withMessages([
                'email' => ['This account is disabled.'],
            ]);
        }

        $customer->tokens()->delete();
        $token = $customer->createToken('spa')->plainTextToken;

        ActivityLogger::record($customer, 'Customer login', $customer->username);

        return response()->json([
            'user' => $this->customerPayload($customer),
            'token' => $token,
        ]);
    }

    public function adminLogin(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ]);

        $admin = Admin::where('username', $credentials['username'])->first();

        if (! $admin || ! Hash::check($credentials['password'], $admin->password)) {
            throw ValidationException::withMessages([
                'username' => [__('auth.failed')],
            ]);
        }

        $admin->tokens()->delete();
        $token = $admin->createToken('spa')->plainTextToken;

        ActivityLogger::record($admin, 'Admin login', $admin->username);

        return response()->json([
            'user' => $this->adminPayload($admin),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request): JsonResponse
    {
        $actor = $request->user();

        if ($actor instanceof Customer) {
            return response()->json(['user' => $this->customerPayload($actor)]);
        }

        if ($actor instanceof Admin) {
            return response()->json(['user' => $this->adminPayload($actor)]);
        }

        return response()->json(['user' => null]);
    }

    private function customerPayload(Customer $c): array
    {
        return [
            'type' => 'customer',
            'id' => $c->id,
            'first_name' => $c->first_name,
            'last_name' => $c->last_name,
            'age' => $c->age,
            'gender' => $c->gender,
            'name' => $c->fullName(),
            'email' => $c->email,
            'username' => $c->username,
            'address' => $c->address,
            'contact' => $c->contact,
            'status' => $c->status,
        ];
    }

    private function adminPayload(Admin $a): array
    {
        return [
            'type' => 'admin',
            'id' => $a->id,
            'username' => $a->username,
        ];
    }
}
