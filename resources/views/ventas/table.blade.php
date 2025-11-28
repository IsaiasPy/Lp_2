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
                        <td>
                            <span class="badge {{ $venta->condicion_venta == 'CONTADO' ? 'badge-success' : 'badge-info' }}">
                                {{ $venta->condicion_venta }}
                            </span>
                        </td>
                        <td>{{ number_format($venta->total, 0, ',', '.') }}</td>
                        <td>{{ $venta->usuario }}</td>
                        <td>
                            <span class="badge bg-{{ $venta->estado == 'COMPLETADO' ? 'info' : ($venta->estado == 'PAGADO' ? 'success' : 'danger') }}">
                                {{ $venta->estado }}
                            </span>
                        </td>
                        <td style="width: 120px">
                            {!! Form::open(['route' => ['ventas.destroy', $venta->id_venta], 'method' => 'delete']) !!}
                            <div class='btn-group'>
                                
                                {{-- LÓGICA DE BOTONES DE COBRO --}}
                                
                                {{-- 1. Botón COBRAR: Solo para ventas al CONTADO que no estén pagadas ni anuladas --}}
                                @if($venta->condicion_venta == 'CONTADO' && $venta->estado <> 'ANULADO' && $venta->estado <> 'PAGADO')
                                    <a href="{{ route('cobros.index', ["id_venta" => $venta->id_venta]) }}" 
                                       class='btn btn-warning btn-xs' 
                                       title="Cobrar Venta Contado">
                                        <i class="far fa-money-bill-alt"></i>
                                    </a>
                                @endif

                                {{-- 2. Botón VER CUOTAS: Solo para ventas a CRÉDITO --}}
                                @if($venta->condicion_venta == 'CREDITO')
                                    {{-- Redirige a Cuentas a Cobrar filtrando por el Nro de Factura --}}
                                    <a href="{{ route('cuentasacobrar.index', ['buscar' => $venta->factura_nro]) }}" 
                                       class='btn btn-info btn-xs' 
                                       title="Ver Cuotas / Pagar">
                                        <i class="fas fa-list-ol"></i>
                                    </a>
                                @endif

                                <a href="{{ route('ventas.show', [$venta->id_venta]) }}" class='btn btn-default btn-xs' title="Ver Detalle">
                                    <i class="far fa-eye"></i>
                                </a>
                                
                                @if($venta->estado <> 'ANULADO') 
                                    <a href="{{ url('imprimir-factura/' . $venta->id_venta) }}"
                                        class='btn btn-success btn-xs' title="Imprimir">
                                        <i class="fas fa-print"></i>
                                    </a>
                                @endif
                                
                                @if ($venta->estado != 'ANULADO')
                                    @if($venta->estado != 'PAGADO')
                                        <a href="{{ route('ventas.edit', [$venta->id_venta]) }}"
                                            class='btn btn-default btn-xs' title="Editar">
                                            <i class="far fa-edit"></i>
                                        </a>
                                    @endif
                                   
                                    {!! Form::button('<i class="far fa-trash-alt"></i>', [
                                        'type' => 'submit',
                                        'class' => 'btn btn-danger btn-xs',
                                        'onclick' => "return confirm('Desea anular la venta?')",
                                        'title' => 'Anular Venta'
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
            @include('adminlte-templates::common.paginate', ['records' => $ventas])
        </div>
    </div>
</div>