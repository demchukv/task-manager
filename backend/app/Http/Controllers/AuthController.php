<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/register",
     *     operationId="register",
     *     tags={"Authentication"},
     *     summary="Register a new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *     )
     * )
     */
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'member',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 'User registered successfully', 201);
    }

    /**
     * @OA\Post(
     *     path="/login",
     *     operationId="login",
     *     tags={"Authentication"},
     *     summary="User login",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid login details",
     *     )
     * )
     */
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        if (!Auth::attempt($validated)) {
            return $this->errorResponse('Invalid login details', 401);
        }

        $user = User::where('email', $validated['email'])->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 'Login successful');
    }

    /**
     * @OA\Post(
     *     path="/logout",
     *     operationId="logout",
     *     tags={"Authentication"},
     *     summary="User logout",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logged out",
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return $this->successResponse(null, 'Successfully logged out');
    }

    /**
     * @OA\Get(
     *     path="/user",
     *     operationId="getAuthenticatedUser",
     *     tags={"Authentication"},
     *     summary="Get authenticated user details",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     )
     * )
     */
    public function me(Request $request)
    {
        return $this->successResponse($request->user());
    }
}
