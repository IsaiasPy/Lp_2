<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="stocks-table">
            <thead>
            <tr>
                <th>#</th>
                <th>Producto</th>
                <th>Sucursal</th>
                <th>Cantidad</th>
            </tr>
            </thead>
            <tbody>
            @foreach($stocks as $stock)
                <tr>
                    <td>{{ $stock->id_stock }}</td>
                    <td>{{ $stock->producto }}</td>
                    <td>{{ $stock->sucursal }}</td>
                    <td>{{ $stock->cantidad }}</td>
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
