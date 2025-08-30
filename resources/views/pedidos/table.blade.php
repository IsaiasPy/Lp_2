<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="pedidos-table">
            <thead>
            <tr>
                <th>Id Cliente</th>
                <th>Fecha Pedido</th>
                <th>Total Pedido</th>
                <th>Id Sucursal</th>
                <th>Estado</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($pedidos as $pedidos)
                <tr>
                    <td>{{ $pedidos->id_cliente }}</td>
                    <td>{{ $pedidos->fecha_pedido }}</td>
                    <td>{{ $pedidos->total_pedido }}</td>
                    <td>{{ $pedidos->id_sucursal }}</td>
                    <td>{{ $pedidos->estado }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['pedidos.destroy', $pedidos->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('pedidos.show', [$pedidos->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('pedidos.edit', [$pedidos->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $pedidos])
        </div>
    </div>
</div>
