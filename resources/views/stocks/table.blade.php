<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="stocks-table">
            <thead>
            <tr>
                <th>Producto</th>
                <th>Sucursal</th>
                <th>Cantidad</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($stocks as $stock)
                <tr>
                    <td>{{ $stock->id_producto }}</td>
                    <td>{{ $stock->id_sucursal }}</td>
                    <td>{{ $stock->cantidad }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['stocks.destroy', $stock->id_stock], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('stocks.show', [$stock->id_stock]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('stocks.edit', [$stock->id_stock]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-edit"></i>
                            </a>
                            {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return confirm('Are you sure?')"]) !!}
                        </div>
                        {!! Form::close() !!}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        <div class="float-right">
            {{-- @include('adminlte-templates::common.paginate', ['records' => $stocks]) --}}
        </div>
    </div>
</div>
