<div class="p-0 card-body">
    <div class="table-responsive">
        <table class="table" id="cuentasacobrar-table">
            <thead>
                <tr>
                    <th>Cuenta N°</th>
                    <th>Cliente</th>
                    <th>N° Factura</th>
                    <th>Fecha Venta</th>
                    <th>Cuota N°</th>
                    <th>Monto Cuota</th>
                    <th>Saldo Pendiente</th>
                    <th>Fecha Vencimiento</th>
                    <th>Estado</th>
                    <th colspan="3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cuentasacobrar as $fila)
                    <tr>
                        <td>{{ $fila->id_cuenta }}</td>
                        
                        <td>{{ $fila->cliente }}</td>
                        
                        <td>{{ $fila->factura_nro }}</td>
                        
                        <td>{{ \Carbon\Carbon::parse($fila->fecha_venta)->format('d/m/Y') }}</td>
                        
                        <td>{{ $fila->nro_cuotas }}</td>
                        
                        <td>{{ number_format($fila->importe, 0, ',', '.') }}</td>
                        
                        <td>{{ number_format($fila->saldo, 0, ',', '.') }}</td>
                        
                        <td>{{ \Carbon\Carbon::parse($fila->vencimiento)->format('d/m/Y') }}</td>
                        
                        <td>
                            @if($fila->estado == 'PENDIENTE')
                                <span class="badge badge-warning">{{ $fila->estado }}</span>
                            @elseif($fila->estado == 'CANCELADO')
                                <span class="badge badge-success">{{ $fila->estado }}</span>
                            @else
                                <span class="badge badge-danger">{{ $fila->estado }}</span>
                            @endif
                        </td>
                        
                        <td style="width: 120px">
                            <div class='btn-group'>
                                @if($fila->saldo > 0)
                                    <a href="{{ route('cobros.cxc.create', ['id_cuenta' => $fila->id_cuenta]) }}" 
                                       class='btn btn-warning btn-xs' 
                                       title="Cobrar Cuota">
                                        <i class="far fa-money-bill-alt"></i> Cobrar
                                    </a>
                                @else
                                    <span class="badge badge-secondary">Saldada</span>
                                @endif
                                
                                {{-- Aquí podrías agregar botones para ver la venta si lo deseas: --}}
                                {{-- <a href="{{ route('ventas.show', [$fila->id_venta]) }}" class='btn btn-default btn-xs'><i class="far fa-eye"></i></a> --}}
                                
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="clearfix card-footer">
        <div class="float-right">
            @include('adminlte-templates::common.paginate', ['records' => $cuentasacobrar])
        </div>
    </div>
</div>