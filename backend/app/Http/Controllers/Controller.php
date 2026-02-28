<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Task Manager API",
 *     version="1.0.0",
 *     description="API documentation for Task Manager application",
 *     @OA\Contact(
 *         email="admin@example.com"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="/api",
 *     description="Main API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * 
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", example="john@example.com"),
 *     @OA\Property(property="role", type="string", example="member")
 * )
 * 
 * @OA\Schema(
 *     schema="Project",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Sample Project"),
 *     @OA\Property(property="description", type="string", example="Project description"),
 *     @OA\Property(property="owner", ref="#/components/schemas/User"),
 *     @OA\Property(property="tasks_count", type="integer", example=5)
 * )
 * 
 * @OA\Schema(
 *     schema="Task",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Fix bug"),
 *     @OA\Property(property="status", type="string", example="todo"),
 *     @OA\Property(property="priority", type="string", example="medium"),
 *     @OA\Property(property="assignee_id", type="integer", example=2),
 *     @OA\Property(property="assignee", ref="#/components/schemas/User")
 * )
 * 
 * @OA\Schema(
 *     schema="Comment",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="body", type="string", example="This is a comment"),
 *     @OA\Property(property="user", ref="#/components/schemas/User"),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 */
abstract class Controller
{
    use \App\Traits\ApiResponse;
}
