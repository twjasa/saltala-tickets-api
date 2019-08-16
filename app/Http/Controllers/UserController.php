<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\UserResource;
use Carbon\Carbon;
use App\User;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function login(Request $request)
    {
        # code...
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'fail' => true,
                'message' => 'Datos erroneos',
                'errors' => $validator->errors()
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if ($user) {
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                // Authentication passed...
                $auth_user = $request->user();
                $previous_tokens = $auth_user->tokens;
                if (count($previous_tokens) > 0) {
                    // dd('hola');
                    foreach ($previous_tokens as $invalid_token) {
                        $invalid_token->revoke();
                    }
                }
                $scope = $request->user()->role;
                // dd($scope);
                $tokenResult = $auth_user->createToken('Personal Access Token', [$scope]);
                $token = $tokenResult->token;
                $token->save();

                return response()->json([
                    'access_token' => $tokenResult->accessToken,
                    'token_type' => 'Bearer',
                    'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString(),
                    'user' => ['data' => auth()->user()]
                ]);
            } else {
                return response()->json([
                    'fail' => true,
                    'message' => 'La combinación de correo y contraseña, no es correcta'
                ], 400);
            }
        }
        return response()->json([
            'fail' => true,
            'message' => 'Datos erroneos'
        ], 400);
    }

    public function signup(Request $request)
    {
        try {
            //validate Request data 
            $validator = Validator::make($request->all(), [
                'email' => 'required|unique:users|max:255',
                'name' => 'required|max:25',
                'lastname' => 'required|max:25',
                'password' => 'required|max:255',
                'role' => 'required',
            ]);
            if ($validator->passes()) {
                //User
                $user = new User();
                $user->email = strtolower($request->email);
                $user->password = bcrypt($request->password);
                $user->name = strtolower($request->name);
                $user->role = strtolower($request->role);

                DB::transaction(function () use ($user) {
                    $user->save();
                });

                return new UserResource(User::find($user->id));
            } else {
                return response()->json([
                    "error" => true,
                    "message" => 'Datos Inválidos',
                    "errors" => $validator->errors()
                ], 400);
            }
        } catch (\Exception $e) {
            return  response()->json([
                'fail' => true,
                'errors' => 'Error al intentar crear el usuario intenta nuevamente',
                "errors" => $e->getMessage()
            ], 400);
        }
    }

    public function logout(Request $request)
    {
        # code...
        // dd($request->user());
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}
