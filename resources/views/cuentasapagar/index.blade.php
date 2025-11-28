@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Cuentas a Pagar</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('sweetalert::alert')

        <div class="clearfix"></div>

        <div class="card">
            <div class="card-header">
                <form action="{{ route('cuentasapagar.index') }}" method="GET">
                    <div class="input-group">
                        <input type="text" 
                               name="buscar" 
                               class="form-control" 
                               placeholder="Buscar por proveedor, nro factura, importe..." 
                               value="{{ request('buscar') }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            @if(request('buscar'))
                                <a href="{{ route('cuentasapagar.index') }}" class="btn btn-danger">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                @include('cuentasapagar.table')
            </div>

            <div class="card-footer clearfix">
                <div class="float-right">
                    @if($cuentasapagar instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        {{ $cuentasapagar->links() }}
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection