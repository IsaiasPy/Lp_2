@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7" style="margin-top: 2%">
                <div class="box">
                    <h3 class="box-title" style="padding: 2%">Verifica tu correo</h3>

                    <div class="box-body">
                        @if (session('resent'))
                            <div class="alert alert-success" role="alert"> Se ha enviado un nuevo enlace de verificación a
                                tu correo.
                            </div>
                        @endif
                        <p>Antes de continuar, por favor revisa tu correo para obtener un enlace de verificación.</p>
                            <a href="#"
                               onclick="event.preventDefault(); document.getElementById('resend-form').submit();">
                                Haz click aqui para reenviar el correo.
                            </a>
                            <form id="resend-form" action="{{ route('verification.resend') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection