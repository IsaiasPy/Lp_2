<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laracasts\Flash\Flash;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function login(Request $request)
    {
         ##campos del formulario
        $input = $request->all();


        ##validacion de campos recibidos
        $validacion = Validator::make($request->all(), [
            'email'    => 'required',
            'password' => 'required',
        ], [
            'email.required'    => 'El campo email es obligatorio.',
            'password.required' => 'El campo password es obligatorio.',
        ]);

        // si la validacion falla
        if ($validacion->fails()) {
            return redirect('/login')
                ->withErrors($validacion)
                ->withInput();
        }
        ##credenciales
        $credenciales = [
            'email'    => $input['email'],
            'password' => $input['password']
        ];

        ##autenticacion estado
        $usuario = DB::selectOne('SELECT * FROM users WHERE email = ? AND cast(estado as integer) = 0', [$input['email']]);

        // si el usuario esta inactivo
        if (!empty($usuario)) {
            Flash::info('El usuario se encuentra inactivo.');
            return redirect('/')->withInput();
        }

        // intento de autenticacion con las credenciales recibidas
        if (Auth::attempt($credenciales)) {
            return redirect()->to($this->redirectTo);
        } else {
            Flash::info('El correo electrónico y/o la contraseña no son correctos.
            Por favor, intentá de nuevo.');
            return redirect('/login')->withInput();
        }

    }

    public function logout(Request $request)
    {
        // matar sesion
        Auth::logout();
        return redirect('/');
    }
}
