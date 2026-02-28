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
