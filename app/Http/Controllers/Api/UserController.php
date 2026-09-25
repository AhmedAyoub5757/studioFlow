<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/staff",
     *     tags={"Staff"},
     *     summary="Create an internal staff account (Agency Manager / Super Admin only)",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"name","email","password","role"},
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="email", type="string", format="email"),
     *         @OA\Property(property="password", type="string", format="password"),
     *         @OA\Property(property="role", type="string",
     *             enum={"agency_manager","project_manager","developer","designer","qa","finance"})
     *     )),
     *     @OA\Response(response=201, description="Staff account created"),
     *     @OA\Response(response=403, description="Missing users.manage permission", @OA\JsonContent(ref="#/components/schemas/ForbiddenError"))
     * )
     */
    public function storeStaff(StoreStaffRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $role = Role::where('name', $request->role)->firstOrFail();
        $user->roles()->attach($role);

        return response()->json(['user' => $user->load('roles')], 201);
    }
}
