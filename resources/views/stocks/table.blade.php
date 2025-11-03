<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="stocks-table">
            <thead>
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">Producto</th>
                    <th class="text-center">Sucursal</th>
                    <th class="text-center">Cantidad de Stock</th>
                </tr>
            </thead>
            <tbody>
                {{-- 2. SE USA @forelse PARA MANEJAR UN ESTADO VACÍO --}}
                @forelse($stocks as $stock)
                    <tr>
                        <td class="text-center">{{ $stock->id_stock }}</td>
                        <td class="text-center">{{ $stock->producto }}</td>
                        <td class="text-center">{{ $stock->sucursal }}</td>
                        <td class="text-center">{{ $stock->cantidad }}</td>
                        <td class="text-center">
                            {{-- <div class="btn-group">
                                @can('stocks edit')
                                <a href="{{ route('stocks.edit', $stock->id_stock) }}" class="btn btn-info btn-xs" title="Editar Stock" data-toggle="tooltip">
                                    <i class="far fa-edit"></i>
                                </a>
                                @endcan
                                @can('stocks destroy')
                                {!! Form::open(['route' => ['stocks.destroy', $stock->id_stock], 'method' => 'delete', 'class' => 'd-inline']) !!}
                                    {!! Form::button('<i class="far fa-trash-alt"></i>', [
                                        'type' => 'submit',
                                        'class' => 'btn btn-danger btn-xs alert-delete',
                                        'title' => 'Eliminar Registro',
                                        'data-toggle' => 'tooltip',
                                        'data-mensaje' => 'el registro de stock Nro. '. $stock->id_stock
                                    ]) !!}
                                {!! Form::close() !!}
                                @endcan
                            </div> --}}
                        </td>
                    </tr>
                @empty
                    <tr>
                        {{-- Mensaje si no hay resultados --}}
                        <td colspan="5" class="text-center">No se encontraron registros que coincidan con la búsqueda.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        <div class="float-right">
            {{-- 4. SE ACTIVA LA PAGINACIÓN DE LARAVEL --}}
            @if($stocks->count() > 0)
                {{ $stocks->links() }}
            @endif
        </div>
    </div>
</div>