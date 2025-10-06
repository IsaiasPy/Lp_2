@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Stocks</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right"
                       href="{{ route('stocks.create') }}">
                       <i class="fas fa-plus"></i>
                        Agregar Stock
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('sweetalert::alert')

        <div class="clearfix"></div>
        @includeIf('layouts.buscador', ['url' => url()->current()])
        <div class="card">
            {{-- @include('stocks.table') --}}
        </div>
    </div>

@endsection
