<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="compras-table">
            <thead>
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">Fecha</th>
                    <th class="text-center">Condición</th>
                    <th class="text-center">Factura</th>
                    <th class="text-center">Proveedor</th>
                    <th class="text-center">Usuario</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($compras as $compra)
                    <tr>
                        <td class="text-center">{{ $compra->id_compra }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($compra->fecha_compra)->format('d/m/Y') }}</td>
                        <td class="text-center">
                            {{-- Agregamos un badge para distinguir visualmente --}}
                            <span class="badge {{ $compra->condicion_compra == 'CONTADO' ? 'badge-success' : 'badge-info' }}">
                                {{ $compra->condicion_compra }}
                            </span>
                        </td>
                        <td class="text-center">{{ $compra->factura }}</td>
                        <td class="text-center">{{ $compra->proveedor }}</td>
                        <td class="text-center">{{ $compra->usuario }}</td>
                        <td class="text-center">
                            @if ($compra->estado == 'ANULADO')
                                <span class="badge badge-danger">{{ $compra->estado }}</span>
                            @else
                                <span class="badge badge-success">{{ $compra->estado ?? 'RECIBIDO' }}</span>
                            @endif
                        </td>
                        <td class="text-center">{{ number_format($compra->total ?? 0, 0, ',', '.') }}</td>
                        <td class="text-center">
                            {!! Form::open(['route' => ['compras.destroy', $compra->id_compra], 'method' => 'delete', 'class' => 'd-inline']) !!}
                            <div class="btn-group">
                                
                                {{-- ========================================================= --}}
                                {{-- NUEVO BOTÓN: IR A CUENTAS A PAGAR (Solo si es Crédito) --}}
                                {{-- ========================================================= --}}
                                @if($compra->condicion_compra == 'CREDITO')
                                    <a href="{{ route('cuentasapagar.index', ['buscar' => $compra->factura]) }}" 
                                       class="btn btn-warning btn-xs" 
                                       title="Ver Cuotas / Pagar Deuda">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </a>
                                @endif

                                <a href="{{ route('compras.show', $compra->id_compra) }}" class="btn btn-default btn-xs"
                                    title="Ver"><i class="far fa-eye"></i></a>
                                
                                {{-- Los botones de Editar y Anular solo aparecen si la compra NO está anulada --}}
                                @if ($compra->estado != 'ANULADO')
                                    <a href="{{ route('compras.edit', $compra->id_compra) }}"
                                        class="btn btn-info btn-xs" title="Editar"><i class="far fa-edit"></i></a>
                                    
                                    {{-- Formulario para anular de forma segura usando el método DELETE --}}
                                    {!! Form::button('<i class="far fa-trash-alt"></i>', [
                                        'type' => 'submit',
                                        'class' => 'btn btn-danger btn-xs alert-delete',
                                        'title' => 'Anular Compra',
                                        'data-mensaje' => 'la compra Nro. '. $compra->id_compra
                                    ]) !!}
                                @endif
                            </div>
                            {!! Form::close() !!}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">No hay compras registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        <div class="float-right">
            {{-- Se asegura de que la paginación funcione incluso con filtros de búsqueda --}}
            @if($compras instanceof \Illuminate\Pagination\LengthAwarePaginator)
                {{ $compras->withQueryString()->links() }}
            @endif
        </div>
    </div>
</div>