<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Validator;

class PassportAuthController extends Controller {

    public function index() {

        try {

            $users = User::all();

            if (!$users) {

                return response()->json('No Hay Usuarios', 204);
            }

            Log::info('Consulta Todos los Usuarios');
            return response()->json($users, 200);
        } catch (\Exception $e) {

            Log::critical('Error: ' . $e->getMessage() . ' Code: ' . $e->getCode());
            return response()->json($e->getMessage(), 500);
        }
    }

    public function store(Request $request) {

        try {

            $validator = Validator::make($request->all(), [
                        'name' => 'required',
                        'last_name' => 'required',
                        'email' => 'required|email|unique:users,email',
                        'role' => 'required',
                        'identification_number' => 'required',
            ]);

            if ($validator->fails()) {
                Log::error('Falló la validación');
                return response()->json(['Falló la validación' => $validator->errors()], 422);
            }

            $roles = array("admin", "invited", "ADMIN", "INVITED");
            $permited = false;

            if (in_array($request->role, $roles)) {
                $permited = true;
            }

            if (!$permited) {
                Log::error('Intentando crear un rol no permitido');
                return response()->json('El rol no existe', 422);
            }

            $input = $request->all();

            $user = User::create([
                        'name' => $input['name'],
                        'last_name' => $input['last_name'],
                        'email' => $input['email'],
                        'identification_number' => $input['identification_number'],
                        'password' => Hash::make($input['identification_number']),
                        'is_active' => 1,
            ]);
   

            Log::info('Usuario Registrado: ' . $user);

            return response()->json($user, 200);
        } catch (\Exception $e) {

            Log::critical('Error: ' . $e->getMessage() . ' Code: ' . $e->getCode());
            return response()->json($e->getMessage(), 500);
        }
    }

    public function login(Request $request) {

        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {

            $user = Auth::user();

            $userToken = $user->createToken('Laravel');

            $token['token'] = $userToken->accessToken;

            Log::info('Usuario Logeado: ' . $user);

            return response()->json(['success' => true, 'data' => $user, 'message' => $token], 200);
        } else {

            Log::info('Logeo fallido: ' . request('email'));
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function logout(Request $request) {

        $request->user()->token()->revoke();

        Log::info('Usuario Deslogueado: ' . $request->user());

        return response()->json([
                    'message' => 'Usuario Deslogueado'
        ]);
    }

}
