<?php
namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
//
    public function register(StoreUserRequest $request)
    {
        
       try {
        $response = $this->authService->register($request->validated());
        return response()->json($response, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registration failed'], 500);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            $response = $this->authService->login($credentials);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    public function user(Request $request)
    {
        $user = $this->authService->user();
        return response()->json($user);
    }

    public function logout(Request $request)
    {
        $response = $this->authService->logout();
        return response()->json($response);
    }
}