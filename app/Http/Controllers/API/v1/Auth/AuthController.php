<?php

namespace App\Http\Controllers\API\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\v1\Auth\LoginRequest;
use App\Http\Resources\API\v1\Auth\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Schema(
 *   schema="User",
 *   type="object",
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="email", type="string", format="email"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class AuthController extends Controller
{
    /**
     * @param LoginRequest $request
     * @return JsonResponse
     *
     * @OA\Post(
     * path="/api/auth/login",
     * summary="Авторизация пользователя",
     * tags={"Auth"},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"email","password"},
     * @OA\Property(property="email", type="string", example="admin@eff-mobile.com"),
     * @OA\Property(property="password", type="string", example="12345678")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Успешная авторизация",
     * @OA\JsonContent(
     * @OA\Property(property="token", type="string"),
     * @OA\Property(property="token_type", type="string", example="Bearer"),
     * @OA\Property(property="user", ref="#/components/schemas/User")
     * )
     * ),
     * @OA\Response(response=401, description="Неверные данные")
    * )
    */
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Неверно введенные данные!',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user->tokens()->where('name', 'authToken')->delete();
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @OA\Post(
     *      path="/api/auth/logout",
     *      summary="Выход из системы",
     *      security={{"bearerAuth":{}}},
     *      tags={"Auth"},
     *      @OA\Response(response=200, description="Вы вышли из системы")
     *  )
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Вы вышли из системы',
        ]);
    }
}
