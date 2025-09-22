@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1>Listado de Permisos</h1>
                </div>
                <div class="col-sm-6">
                    <a class="float-right btn btn-primary"
                       href="{{ route('permissions.create') }}">
                        <i class="fas fa-plus"></i> Nuevo
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="px-3 content">

        @include('sweetalert::alert')

        <div class="clearfix"></div>

        <div class="card">
            @include('permissions.table')
        </div>
    </div>

@endsection
