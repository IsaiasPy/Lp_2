@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Reporte de Cargos</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        <div class="clearfix"></div>
        <!-- card de filtros -->
        <div class="card">
            <div class="card-body p-3">

                <h3>Filtros</h3>
                <div class="row">
                    <div class="form-group col-sm-2">
                        {!! Form::label('desde', 'Codigo Desde:') !!}
                        {!! Form::text('desde', request()->get('desde', null), [
                            'class' => 'form-control',
                            'placeholder' => 'Ingrese el código',
                            'id' => 'desde',
                        ]) !!}
                    </div>

                    <div class="form-group col-sm-2">
                        {!! Form::label('hasta', 'Codigo Hasta:') !!}
                        {!! Form::text('hasta', request()->get('hasta', null), [
                            'class' => 'form-control',
                            'placeholder' => 'Ingrese el código',
                            'id' => 'hasta',
                        ]) !!}
                    </div>

                    <div class="form-group col-sm-2">
                        {!! Form::label('ciudad', 'Ciudad:') !!}
                        {!! Form::select('ciudad', $ciudades, request()->get('ciudad', null), [
                            'class' => 'form-control',
                            'placeholder' => 'Ingrese una ciudad',
                            'id' => 'ciudad',
                        ]) !!}
                    </div>

                    <div class="form-group col-sm-3">
                        <button class="btn btn-success" type="button" data-toggle="tooltip" data-placement="top"
                            title="Buscar" id="btn-consultar" style="margin-top:32px">
                            <i class="fas fa fa-search"></i>
                        </button>

                        <button class="btn btn-default" type="button" data-toggle="tooltip" title="Limpiar" id="btn-limpiar"
                            style="margin-top:32px">
                            <i class="fas fa fa-eraser"></i>
                        </button>

                        <button class="btn btn-primary" id="btn-exportar" type="button" data-toggle="tooltip"
                            title="Exportar" style="margin-top:32px">
                            <i class="fas fa-print"></i> PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- fin filtros -->

        <div class="card">
            <div class="card-body p-0">
                <div class="table-bordered">
                    <table class="table table-bordered" id="cargos-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Clientes</th>
                                <th>Fecha Nac.</th>
                                <th>Edad</th>
                                <th>Telefono</th>
                                <th>Nro CI</th>
                                <th>Direccion</th>
                                <th>Ciudad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($clientes as $cliente)
                                <tr>
                                    <td>{{ $cliente->id_cliente }}</td>
                                    <td>{{ $cliente->clie_nombre . ' ' . $cliente->clie_apellido }}</td>
                                    <td>{{ \Carbon\Carbon::parse($cliente->cli_fecha_nac)->format('d/m/Y')}}</td>
                                    <td>{{ $cliente->edad }}</td>
                                    <td>{{ $cliente->clie_telefono }}</td>
                                    <td>{{ $cliente->clie_ci }}</td>
                                    <td>{{ $cliente->clie_direccion }}</td>
                                    <td>{{ $cliente->ciudad }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {

            $('[data-toggle="tooltip"]').tooltip();
            // boton para generar consulta al controlador reportes funcion rpt_cargos
             $('#btn-consultar').click(function(e) {
                // Aquí puedes agregar la lógica para generar el reporte
                e.preventDefault();
                window.location.href = '{{ url('reporte-clientes') }}?desde=' + $('#desde').val() +
                    '&hasta=' + $('#hasta').val() + '&ciudad=' + $('#ciudad').val();
            });

            // boton para generar la exportación a pdf del reporte
            $('#btn-exportar').click(function(e) {
                // Aquí puedes agregar la lógica para exportar el reporte
                e.preventDefault();
                window.open('{{ url('reporte-clientes') }}?desde=' + $('#desde').val() + '&hasta=' + $('#hasta').val() + 
                '&exportar=pdf' + '&ciudad=' + $('#ciudad').val(), '_blank');
            });
        });
        $('#btn-limpiar').click(function(e) {
            e.preventDefault();
            $('#desde').val('');
            $('#hasta').val('');
            $('#ciudad').val('');
            window.location.href = '{{ url('reporte-clientes') }}';
        })
    </script>
@endpush
