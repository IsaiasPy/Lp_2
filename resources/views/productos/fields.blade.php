<!-- Descripcion Field -->
<div class="form-group col-sm-6">
    {!! Form::label('descripcion', 'Descripcion:') !!}
    {!! Form::text('descripcion', null, [
        'class' => 'form-control',
        'required',
        'placeholder' => 'Ingrese la descripción del producto',
    ]) !!}
</div>

<!-- Precio Field -->
<div class="form-group col-sm-6">
    {!! Form::label('precio', 'Precio:') !!}
    {!! Form::text('precio', isset($productos) ? number_format($productos->precio, 0, ',', '.') : null, [
        'class' => 'form-control',
        'required',
        'placeholder' => 'Ingrese el precio del producto',
        'onkeyup' => 'format(this)',
    ]) !!}
</div>

<!-- Tipo Iva Field -->
<div class="form-group col-sm-6">
    {!! Form::label('tipo_iva', 'Tipo Iva:') !!}
    {!! Form::select('tipo_iva', $tipo_iva, null, [
        'class' => 'form-control',
        'required',
        'placeholder' => 'Seleccione el tipo de IVA',
    ]) !!}
</div>

<!-- Id Marca Field -->
<div class="form-group col-sm-6">
    {!! Form::label('id_marca', 'Marca:') !!}
    {!! Form::select('id_marca', $marcas, null, [
        'class' => 'form-control',
        'required',
        'placeholder' => 'Seleccione una marca',
    ]) !!}
</div>
