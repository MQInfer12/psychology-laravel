<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $email = request('email');
        $autorizado = DB::select("SELECT estado FROM users WHERE email='$email'");
        
        if($autorizado != []) {
            if(!$autorizado[0]->estado) {
                return response()->json(['error' => 'No se puede ingresar a esta cuenta'], 403);
            }
        }

        $credentials = request(['email', 'password']);
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Correo o contraseña incorrectos'], 401);
        }

        return $this->respondWithToken($token);
    }
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $usuario = auth()->user();
        if($usuario->perfil != null) {
            $usuario->perfil = stream_get_contents($usuario -> perfil);
        }
        
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        $cookie = Cookie::forget('jwt');
        return response()->json(['message' => 'Successfully logged out'])->withCookie($cookie);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $cookie = cookie('jwt', $token, 60 * 24);
        return response()->json([
            'message' => "Logged succesfully",
        ])->withCookie($cookie);
    }

    public function register(Request $request) {
        $validator = Validator::make($request -> all(), [
            'nombre' => 'required',
            'email' => 'required',
            'password' => 'required',
            'genero' => 'required',
            'edad' => 'required',
            'id_sede' => 'required',
            'id_rol' => 'required',
            'estado' => 'required',
        ]);
        
        if($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create(array_merge(
            $validator->validate(),
            ['password' => bcrypt($request->password)]
        ));
    
        return response()->json([
            'message' => '¡Registro correcto!',
            'user' => $user
        ], 201);
    }
}