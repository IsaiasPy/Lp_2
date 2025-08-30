<!-- Id Cliente Field -->
<div class="col-sm-12">
    {!! Form::label('id_cliente', 'Id Cliente:') !!}
    <p>{{ $pedidos->id_cliente }}</p>
</div>

<!-- Fecha Pedido Field -->
<div class="col-sm-12">
    {!! Form::label('fecha_pedido', 'Fecha Pedido:') !!}
    <p>{{ $pedidos->fecha_pedido }}</p>
</div>

<!-- Total Pedido Field -->
<div class="col-sm-12">
    {!! Form::label('total_pedido', 'Total Pedido:') !!}
    <p>{{ $pedidos->total_pedido }}</p>
</div>

<!-- Id Sucursal Field -->
<div class="col-sm-12">
    {!! Form::label('id_sucursal', 'Id Sucursal:') !!}
    <p>{{ $pedidos->id_sucursal }}</p>
</div>

<!-- Estado Field -->
<div class="col-sm-12">
    {!! Form::label('estado', 'Estado:') !!}
    <p>{{ $pedidos->estado }}</p>
</div>

