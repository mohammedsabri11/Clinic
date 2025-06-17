<?php
namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(array $data)
    {
        DB::beginTransaction();
        try {
            $user = $this->userRepository->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $role = \App\Models\Role::firstOrCreate(['name' => $data['role']]);
            $user->roles()->attach($role->id);

            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();
            return [
                'user' => $user->load('roles'),
                'token' => $token,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function login(array $credentials)
    {
        if (!Auth::attempt($credentials)) {
            throw new \Exception('Invalid credentials', 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->load('roles'),
            'token' => $token,
        ];
    }

    public function logout()
    {
        $user = Auth::user();
        $user->currentAccessToken()->delete();
        return ['message' => 'Logged out'];
    }

    public function user()
    {
        return Auth::user()->load('roles');
    }
}