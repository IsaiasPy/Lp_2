<x-laravel-ui-adminlte::adminlte-layout>
<head>
    <title>LP2</title>
    
    <!-- Ícono de la pestaña -->
    <link rel="icon" href="https://avatars.githubusercontent.com/u/958072?v=4" type="image/x-icon">

    <!-- Fuente personalizada de Google Fonts (Nunito) -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS (para los estilos base) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* ============================
           Estilo general del body
           ============================ */
        body {
            font-family: 'Nunito', sans-serif; /* Fuente moderna */
            height: 100vh; /* Altura completa del viewport */
            display: flex;
            justify-content: center; /* Centra el contenido horizontalmente */
            align-items: center; /* Centra el contenido verticalmente */
            overflow: hidden; /* Elimina el scroll */
            background-color: #e9ecef; /* Fondo claro por defecto */
        }

        /* ============================
           Contenedor principal del formulario de login
           ============================ */
        .login-container {
            margin-top: 90px; /* Desplaza el contenedor hacia abajo */
            backdrop-filter: blur(10px); /* Aplica el efecto "glass" desenfocado */
            padding: 10px;
            border-radius: 10px; /* Bordes redondeados */
            box-shadow: 0 0 10px 10px rgba(2, 111, 212, 1); /* Sombra sutil */
            width: 100%;
            max-width: 400px; /* Ancho máximo */
            z-index: 10; /* Asegura que el formulario quede encima del fondo */
        }

        /* ============================
           Estilo del logo o título de la app
           ============================ */
        .login-box .login-logo a {
            font-size: 2.5rem;
            color: #2636a4d5; /* Color oscuro */
            text-transform: uppercase; /* Mayúsculas */
            letter-spacing: 1.5px; /* Espaciado entre letras */
            font-weight: bold;
            display: block;
            text-align: center;
            margin-bottom: 20px;
        }

        /* ============================
           Eliminar fondo, bordes y sombras de la tarjeta (.card)
           ============================ */
        .card, .login-card-body {
            background-color: transparent !important; /* Fondo transparente */
            border: none !important; /* Elimina los bordes */
            box-shadow: none !important; /* Elimina las sombras */
        }

        /* ============================
           Estilo de los campos de entrada (inputs)
           ============================ */
        .form-control {
            border-radius: 25px; /* Input redondeado */
            box-shadow: none; /* Sin sombra por defecto */
            height: 40px; /* Ajusta la altura de los inputs */
            padding: 0 15px; /* Relleno interno de los inputs */
        }

        /* ============================
           Icono al lado del input (email y contraseña)
           ============================ */
        .input-group-append .input-group-text {
            background-color: #4c4b6a; /* Color de fondo del icono */
            color: white; /* Color del icono */
            border-radius: 0 25px 25px 0; /* Bordes redondeados del icono */
        }

        /* ============================
           Estilo del botón de login
           ============================ */
        .btn-primary {
            background-color: #2266dbd5; /* Color de fondo */
            border-color: #2e4fabff; /* Color del borde */
            border-radius: 25px; /* Bordes redondeados */
            padding: 10px 20px; /* Tamaño del botón */
            width: 100%; /* Ocupa todo el ancho */
        }

        /* ============================
           Estilo del hover del botón (cuando pasa el mouse)
           ============================ */
        .btn-primary:hover {
            background-color: #2266dbff; /* Color más claro en hover */
            border-color: #2266dbd5; /* Color de borde más claro */
        }

        /* ============================
           Estilo del mensaje de bienvenida "Inicie Sesión"
           ============================ */
        .login-box-msg {
            color: #2266dbff; /* Color del texto */
            text-align: center; /* Centra el texto */
            font-size: 18px; /* Tamaño del texto */
            margin-bottom: 20px; /* Espaciado abajo */
        }

        /* ============================
           Espaciado para el "Olvidé mi contraseña" y "Registrarse"
           ============================ */
        .mt-12 {
            margin-top: 12px;
        }

        /* ============================
           Estilo de los enlaces de texto (Olvidé mi contraseña y Registrarse)
           ============================ */
        .text-center a {
            color: #2266dbff; /* Color del enlace */
            text-decoration: none; /* Sin subrayado */
        }

        /* ============================
           Estilo del hover de los enlaces
           ============================ */
        .text-center a:hover {
            text-decoration: underline; /* Subraya el enlace cuando se pasa el cursor */
        }

        /* ============================
           Estilo del checkbox "Recordar Mi Usuario"
           ============================ */
        .icheck-primary input[type="checkbox"]:checked + label {
            color: #4c4b6a; /* Color del texto cuando está seleccionado */
        }
    </style>
</head>

<body class="hold-transition login-page">
    <!-- Contenedor principal -->
    <div class="login-container">
        
        <!-- Logo con imagen desde URL -->
        <div class="login-logo">
            <a href="{{ url('/home') }}">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRRse1kvIN9Fzg5gMi6PiPfGHQZlzFREWI1Qg&s" 
                     alt="Lp2" 
                     style="height: 80px;">
            </a>
        </div>

        <!-- Tarjeta que contiene el formulario -->
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Inicie Sesión</p>

                <!-- Formulario de login -->
                <form method="post" action="{{ url('/login') }}">
                    @csrf

                    <!-- Campo de email -->
                    <div class="input-group mb-3">
                        <input type="text" name="email" value="{{ old('email') }}" placeholder="Usuario"
                            class="form-control @error('email') is-invalid @enderror">
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                        </div>
                        @error('email')
                            <span class="error invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Campo de contraseña -->
                    <div class="input-group mb-3">
                        <input type="password" name="password" placeholder="Contraseña"
                            class="form-control @error('password') is-invalid @enderror">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                        @error('password')
                            <span class="error invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Checkbox "Recordar mi Usuario" y botón de login -->
                    <div class="row">
                        <div class="col-8">
                            <div class="icheck-primary">
                                <input type="checkbox" id="remember">
                                <label for="remember">Recordar Mi Usuario</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">Acceder</button>
                        </div>
                    </div>
                </form>

                <!-- Enlaces adicionales: "Olvidé mi Contraseña" y "Registrarse" -->
                <p class="mt-12 text-center">
                    <a href="{{ route('password.request') }}">Olvidé mi Contraseña</a>
                </p>
                <p class="mt-12 text-center">
                    <a href="{{ route('register') }}" class="text-center">Registrarse</a>
                </p>
            </div>
        </div>
    </div> <!-- Cierre del contenedor principal -->
</body>
</x-laravel-ui-adminlte::adminlte-layout>
