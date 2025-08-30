<!-- Descripcion Field -->
<div class="form-group col-sm-6">
    {!! Form::label('descripcion', 'Descripcion:') !!}
    {!! Form::text('descripcion', null, ['class' => 'form-control','required' ,'rows' => '3']) !!}
</div>

<!-- Direccion Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('direccion', 'Direccion:') !!}
    {!! Form::textarea('direccion', null, [
        'class' => 'form-control',
        'required',
        'placeholder' => 'Ingrese la Direccion de la Sucursal', 'rows' => '3'
    ]) !!}
</div>

<!-- Telefono Field -->
<div class="form-group col-sm-6">
    {!! Form::label('telefono', 'Telefono:') !!}
    {!! Form::text('telefono', null, [
        'class' => 'form-control',
        'required',
        'placeholder' => 'Ingrese el Telefono de la Sucursal',
    ]) !!}
</div>

<!-- Id Ciudad Field -->
<div class="form-group col-sm-6">
    {!! Form::label('id_ciudad', 'Ciudad:') !!}
    {!! Form::select('id_ciudad', $ciudades, null, [
        'class' => 'form-control',
        'required',
        'placeholder' => 'Seleccione la Ciudad',
    ]) !!}
</div>
