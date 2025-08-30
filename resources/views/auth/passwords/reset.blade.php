<x-laravel-ui-adminlte::adminlte-layout>
<head>
    <title>LP2 - Restablecer Contraseña</title>
    
    <!-- Ícono de la pestaña -->
    <link rel="icon" href="https://avatars.githubusercontent.com/u/958072?v=4" type="image/x-icon">

    <!-- Fuente personalizada -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* ============================ */
        /* Mismo estilo que login/register/email */
        body { font-family: 'Nunito', sans-serif; height: 100vh; display: flex; justify-content: center; align-items: center; overflow: hidden; background-color: #e9ecef; }
        .login-container { margin-top: 90px; backdrop-filter: blur(10px); padding: 10px; border-radius: 10px; box-shadow: 0 0 10px 10px rgba(2, 111, 212, 1); width: 100%; max-width: 400px; z-index: 10; }
        .login-box .login-logo a { font-size: 2.5rem; color: #2636a4d5; text-transform: uppercase; letter-spacing: 1.5px; font-weight: bold; display: block; text-align: center; margin-bottom: 20px; }
        .card, .login-card-body { background-color: transparent !important; border: none !important; box-shadow: none !important; }
        .form-control { border-radius: 25px; box-shadow: none; height: 40px; padding: 0 15px; }
        .input-group-append .input-group-text { background-color: #4c4b6a; color: white; border-radius: 0 25px 25px 0; }
        .btn-primary { background-color: #2266dbd5; border-color: #2e4fabff; border-radius: 25px; padding: 10px 20px; width: 100%; }
        .btn-primary:hover { background-color: #2266dbff; border-color: #2266dbd5; }
        .login-box-msg { color: #2266dbff; text-align: center; font-size: 18px; margin-bottom: 20px; }
        .mt-12 { margin-top: 12px; }
        .text-center a { color: #2266dbff; text-decoration: none; }
        .text-center a:hover { text-decoration: underline; }
    </style>
</head>

<body class="hold-transition login-page">
    <div class="login-container">

        <!-- Logo -->
        <div class="login-logo">
            <a href="{{ url('/home') }}">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRRse1kvIN9Fzg5gMi6PiPfGHQZlzFREWI1Qg&s"
                     alt="Lp2" style="height: 80px;">
            </a>
        </div>

        <!-- Card con formulario -->
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Restablecer Contraseña</p>

                <!-- Formulario de reset password -->
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <!-- Email -->
                    <div class="input-group mb-3">
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="Correo"
                               class="form-control @error('email') is-invalid @enderror" required>
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                        </div>
                        @error('email')
                        <span class="error invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Nueva contraseña -->
                    <div class="input-group mb-3">
                        <input type="password" name="password" placeholder="Nueva contraseña"
                               class="form-control @error('password') is-invalid @enderror" required>
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-lock"></span></div>
                        </div>
                        @error('password')
                        <span class="error invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Confirmar nueva contraseña -->
                    <div class="input-group mb-3">
                        <input type="password" name="password_confirmation" placeholder="Confirmar contraseña"
                               class="form-control" required>
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-lock"></span></div>
                        </div>
                    </div>

                    <!-- Botón de actualizar contraseña -->
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">
                                Restablecer contraseña
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Link volver a login -->
                <p class="mt-12 text-center">
                    <a href="{{ route('login') }}">Volver a iniciar sesión</a>
                </p>

            </div>
        </div>
    </div>
</body>
</x-laravel-ui-adminlte::adminlte-layout>