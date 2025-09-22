@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-12">
                    <h1>
                    Crear Nuevo Permiso
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="px-3 content">

        @include('adminlte-templates::common.errors')

        <div class="card">

            {!! Form::open(['route' => 'permissions.store']) !!}

            <div class="card-body">

                <div class="row">
                    @include('permissions.fields')
                </div>

            </div>

            <div class="card-footer">
                {!! Form::submit('Grabar', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('permissions.index') }}" class="btn btn-default"> Cancelar </a>
            </div>

            {!! Form::close() !!}

        </div>
    </div>
@endsection
