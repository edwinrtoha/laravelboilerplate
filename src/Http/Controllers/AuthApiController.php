<?php
namespace Edwinrtoha\Laravelboilerplate\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('authToken')->plainTextToken;
            return ApiController::response([
                'token' => $token
            ], Response::HTTP_OK);
        }

        return ApiController::response([], status:Response::HTTP_UNAUTHORIZED, errors: 'Not Authorized');
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();
        return ApiController::response($token, status:Response::HTTP_OK, message:'Successfully logged out');
    }
}