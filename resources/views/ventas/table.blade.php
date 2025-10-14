<div class="p-0 card-body">
    <div class="table-responsive">
        <table class="table" id="ventas-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ci/Ruc</th>
                    <th>Cliente</th>
                    <th>Fecha Venta</th>
                    <th>Factura Nro</th>
                    <th>Condición Venta</th>
                    <th>Total</th>
                    <th>Usuario</th>
                    <th>Estado</th>
                    <th colspan="3">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ventas as $venta)
                    <tr>
                        <td>{{ $venta->id_venta }}</td>
                        <td>{{ $venta->clie_ci }}</td>
                        <td>{{ $venta->cliente }}</td>
                        <td>{{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y') }}</td>
                        <td>{{ $venta->factura_nro }}</td>
                        <td>{{ $venta->condicion_venta }}</td>
                        <td>{{ number_format($venta->total, 0, ',', '.') }}</td>
                        <td>{{ $venta->usuario }}</td>
                        <td>
                            <span class="badge bg-{{ $venta->estado == 'COMPLETADO' ? 'success' : 'danger' }}">
                                {{ $venta->estado }}
                            </span>
                        </td>
                        <td style="width: 120px">
                            {!! Form::open(['route' => ['ventas.destroy', $venta->id_venta], 'method' => 'delete']) !!}
                            <div class='btn-group'>
                            <!-- Boton cobros -->
                                <a href="{{ route('cobros.index', ['id_venta' => $venta->id_venta]) }}" class='btn btn-warning btn-xs'>
                                    <i class="far fa-money-bill-alt"></i>
                                </a>
                                <a href="{{ route('ventas.show', [$venta->id_venta]) }}" class='btn btn-default btn-xs'>
                                    <i class="far fa-eye"></i>
                                </a>
                                <!-- utilizamos url en vez de route porque no tenemos un nombre de ruta -->
                                <a href="{{ url('imprimir-factura/' . $venta->id_venta) }}"
                                    class='btn btn-success btn-xs'>
                                    <i class="fas fa-print"></i>
                                </a>
                                <!-- validacion para mostrar los botones de borrar y editar si la venta es anulada -->
                                @if ($venta->estado != 'ANULADO')
                                    <a href="{{ route('ventas.edit', [$venta->id_venta]) }}"
                                        class='btn btn-default btn-xs'>
                                        <i class="far fa-edit"></i>
                                    </a>
                                    {!! Form::button('<i class="far fa-trash-alt"></i>', [
                                        'type' => 'submit',
                                        'class' => 'btn btn-danger btn-xs',
                                        'onclick' => "return confirm('Desea anular la venta?')",
                                    ]) !!}
                                @endif
                            </div>
                            {!! Form::close() !!}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="clearfix card-footer">
        <div class="float-right">
            {{-- @include('adminlte-templates::common.paginate', ['records' => $ventas]) --}}
        </div>
    </div>
</div>