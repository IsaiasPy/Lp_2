<!-- Id Cliente Field -->
<div class="form-group col-sm-6">
    {!! Form::label('id_cliente', 'Id Cliente:') !!}
    {!! Form::number('id_cliente', null, ['class' => 'form-control']) !!}
</div>

<!-- Fecha Pedido Field -->
<div class="form-group col-sm-6">
    {!! Form::label('fecha_pedido', 'Fecha Pedido:') !!}
    {!! Form::text('fecha_pedido', null, ['class' => 'form-control','id'=>'fecha_pedido']) !!}
</div>

@push('page_scripts')
    <script type="text/javascript">
        $('#fecha_pedido').datepicker()
    </script>
@endpush

<!-- Total Pedido Field -->
<div class="form-group col-sm-6">
    {!! Form::label('total_pedido', 'Total Pedido:') !!}
    {!! Form::number('total_pedido', null, ['class' => 'form-control']) !!}
</div>

<!-- Id Sucursal Field -->
<div class="form-group col-sm-6">
    {!! Form::label('id_sucursal', 'Id Sucursal:') !!}
    {!! Form::number('id_sucursal', null, ['class' => 'form-control']) !!}
</div>

<!-- Estado Field -->
<div class="form-group col-sm-6">
    {!! Form::label('estado', 'Estado:') !!}
    {!! Form::text('estado', null, ['class' => 'form-control']) !!}
</div>