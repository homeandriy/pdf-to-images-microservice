<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticationController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(): JsonResponse
    {
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            // successfull authentication
            $user = User::find(Auth::user()->id);

            return response()
                ->json(
                    [
                        'success' => true,
                        'token'   => $user->createToken(
                            $user->email,
                            ['*'],
                            now()->addDays(20)
                        )->toArray(),
                        'user'    => $user->only(['id', 'name', 'email']),
                    ],
                    200
                );
        } else {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to authenticate.',
                ],
                401
            );
        }
    }

    /**
     * Destroy an authenticated session.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function destroy(Request $request)
    {
        if (Auth::user()) {
            $request->user()->token()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ], 200);
        }
        return response()->json([
            'success' => false,
            'message' => 'Failed to logout',
        ], 401);
    }
}
