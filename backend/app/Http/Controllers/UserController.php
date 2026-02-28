<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/users",
     *     operationId="getUsersList",
     *     tags={"Users"},
     *     summary="List all users (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search by name or email",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     )
     * )
     */
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return $this->errorResponse('Unauthorized', 403);
        }

        $query = User::query();

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%')
                ->orWhere('email', 'like', '%' . $request->q . '%');
        }

        $users = $query->limit(10)->get();

        return $this->successResponse(UserResource::collection($users));
    }
}
