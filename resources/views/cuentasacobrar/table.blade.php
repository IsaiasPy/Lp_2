    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" id="productos-table">
                <thead>
                    <tr>
                        <th style="text-align: center;">Cuenta Nº</th>
                        <th style="text-align: center;">Cliente</th>
                        <th style="text-align: center;">N° de Factura</th>
                        <th style="text-align: center;">Fecha de Venta</th>
                        <th style="text-align: center;">Monto Total</th>
                        <th style="text-align: center;">Estado</th>
                        <th style="text-align: center;">Fecha de Vencimiento</th>
                        <th style="text-align: center;">Cuota Nº</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cuentasacobrar as $fila)
                        <tr>
                            <td style="text-align: center;">{{ $fila->id_cuenta }}</td>
                            <td style="text-align: center;">{{ $fila->cliente }}</td>
                            <td style="text-align: center;">{{ $fila->factura_nro }}</td>
                            <td style="text-align: center;">{{ Carbon\Carbon::parse($fila->fecha_venta)->format('d/m/Y') }}</td>
                            <td style="text-align: center;">{{ $fila->importe }}</td>
                            <td style="text-align: center;">
                            @if ($fila->estado == 'PENDIENTE')
                                <span class="badge badge-warning">PENDIENTE</span>
                            @elseif ($fila->estado == 'COBRADO')
                                <span class="badge badge-success">COBRADO</span>
                            @else
                                <span class="badge badge-danger">{{ $fila->estado }}</span>
                            @endif
                        </td>
                            <td style="text-align: center;">{{ Carbon\Carbon::parse($fila->vencimiento)->format('d/m/Y') }}</td>
                            <td style="text-align: center;">{{ $fila->nro_cuotas }}</td>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer clearfix">
            <div class="float-right">
                @include('adminlte-templates::common.paginate', ['records' => $cuentasacobrar])
            </div>
        </div>
    </div>