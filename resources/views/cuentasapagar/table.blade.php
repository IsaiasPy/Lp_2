<div class="table-responsive">
    <table class="table" id="cuentasapagar-table">
        <thead>
            <tr>
                <th>Cuenta N°</th> {{-- ID único de la deuda --}}
                <th>Proveedor</th>
                <th>N° Factura</th>
                <th>Fecha Compra</th>
                <th>Cuota N°</th>
                <th>Monto Cuota</th>
                <th>Saldo Pendiente</th>
                <th>Fecha Vencimiento</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @if(count($cuentasapagar) > 0)
                @foreach ($cuentasapagar as $fila)
                    <tr>
                        <td>{{ $fila->id_cta }}</td>
                        
                        <td>{{ $fila->proveedor }}</td>
                        <td>{{ $fila->factura }}</td>
                        <td>{{ \Carbon\Carbon::parse($fila->fecha_compra)->format('d/m/Y') }}</td>
                        
                        <td class="text-center">{{ $fila->nro_cuenta }}</td>
                        
                        <td>{{ number_format($fila->importe, 0, ',', '.') }}</td>
                        
                        <td style="font-weight: bold; color: #dc3545;">
                            {{ number_format($fila->saldo, 0, ',', '.') }}
                        </td>
                        
                        <td>{{ \Carbon\Carbon::parse($fila->vencimiento)->format('d/m/Y') }}</td>
                        
                        <td>
                            @if($fila->estado == 'PENDIENTE')
                                <span class="badge badge-warning">PENDIENTE</span>
                            @elseif($fila->estado == 'CANCELADO')
                                <span class="badge badge-success">CANCELADO</span>
                            @elseif($fila->estado == 'ANULADO')
                                <span class="badge badge-secondary">ANULADO</span>
                            @else
                                <span class="badge badge-danger">{{ $fila->estado }}</span>
                            @endif
                        </td>
                        
                        <td style="width: 120px">
                            <div class='btn-group'>
                                @if($fila->saldo > 0 && $fila->estado != 'ANULADO')
                                    <a href="{{ route('pagosproveedor.cxp.create', ['id_cta' => $fila->id_cta]) }}" 
                                       class='btn btn-success btn-xs' 
                                       title="Pagar Cuota">
                                        <i class="fas fa-hand-holding-usd"></i> Pagar
                                    </a>
                                @elseif($fila->saldo <= 0 && $fila->estado == 'CANCELADO')
                                    <span class="badge badge-light"><i class="fas fa-check"></i> Pagado</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="10" class="text-center text-muted">
                        No hay cuentas a pagar pendientes.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>