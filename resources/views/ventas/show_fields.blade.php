<!-- Id Apertura Field -->
<div class="col-sm-4">
    {!! Form::label('id_apertura', 'Nro Apertura Caja:') !!}
    <p>{{ $ventas->id_apertura }}</p>
</div>

<!-- Id Cliente Field -->
<div class="col-sm-4">
    {!! Form::label('id_cliente', 'Cliente:') !!}
    <p>{{ $ventas->cliente }}</p>
</div>

<!-- Total Field -->
<div class="col-sm-4">
    {!! Form::label('total', 'Total:') !!}
    <p>{{ number_format($ventas->total, 0, ',', '.') }}</p>
</div>

<!-- Fecha Venta Field -->
<div class="col-sm-4">
    {!! Form::label('fecha_venta', 'Fecha Venta:') !!}
    <p>{{ \Carbon\Carbon::parse($ventas->fecha_venta)->format('d/m/Y') }}</p>
</div>

<!-- Factura Nro Field -->
<div class="col-sm-4">
    {!! Form::label('factura_nro', 'Factura Nro:') !!}
    <p>{{ $ventas->factura_nro }}</p>
</div>

<!-- User Id Field -->
<div class="col-sm-4">
    {!! Form::label('user_id', 'Vendedor:') !!}
    <p>{{ $ventas->usuario }}</p>
</div>

<!-- Detalle Venta -->
<div class="col-sm-12">
    <h4>Detalle Venta</h4>
    <table class="table table-bordered table-striped table-hover">
        <thead>
            <tr>
                <th>Código Producto</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detalle_venta as $detalle)
                <tr>
                    <td>{{ $detalle->id_producto }}</td>
                    <td>{{ $detalle->descripcion }}</td>
                    <td>{{ $detalle->cantidad }}</td>
                    <td>{{ number_format($detalle->precio, 0, ',', '.') }}</td>
                    <td>{{ number_format($detalle->precio * $detalle->cantidad, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>