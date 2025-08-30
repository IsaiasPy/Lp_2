<!-- Clie Nombre Field -->
<div class="form-group col-sm-6">
    {!! Form::label('clie_nombre', 'Nombre:') !!}
    {!! Form::text('clie_nombre', null, [
        'class' => 'form-control',
        'required',
        'placeholder' => 'Ingrese el nombre',
    ]) !!}
</div>

<!-- Cli Apellido Field -->
<div class="form-group col-sm-6">
    {!! Form::label('clie_apellido', 'Apellido:') !!}
    {!! Form::text('clie_apellido', null, [
        'class' => 'form-control',
        'required',
        'placeholder' => 'Ingrese el apellido',
    ]) !!}
</div>

<!-- Clie Ci Field -->
<div class="form-group col-sm-6">
    {!! Form::label('clie_ci', 'Nro Documento:') !!}
    {!! Form::text('clie_ci', null, [
        'class' => 'form-control',
        'required',
        'placeholder' => 'Ingrese el número de documento',
    ]) !!}
</div>

<!-- Clie Telefono Field -->
<div class="form-group col-sm-6">
    {!! Form::label('clie_telefono', 'Teléfono:') !!}
    {!! Form::text('clie_telefono', null, [
        'class' => 'form-control',
        'placeholder' => 'Ingrese el teléfono',
    ]) !!}
</div>

<!-- Clie Direccion Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('clie_direccion', 'Dirección:') !!}
    {!! Form::textarea('clie_direccion', null, ['class' => 'form-control', 'rows' => 3]) !!}
</div>

<!-- Id Departamento Field -->
<div class="form-group col-sm-6">
    {!! Form::label('id_departamento', 'Departamento:') !!}
    {!! Form::select('id_departamento', $departamentos, null, [
        'class' => 'form-control',
        'placeholder' => 'Seleccione un departamento',
    ]) !!}
</div>

<!-- Id Ciudad Field -->
<div class="form-group col-sm-6">
    {!! Form::label('id_ciudad', 'Ciudad:') !!}
    {!! Form::select('id_ciudad', $ciudades, null, [
        'class' => 'form-control',
        'placeholder' => 'Seleccione una ciudad',
    ]) !!}
</div>

<!-- Clie Fecha Nac Field -->
<div class="form-group col-sm-6">
    {!! Form::label('clie_fecha_nac', 'Fecha de Nacimiento:') !!}
    {!! Form::date('clie_fecha_nac', null, ['class' => 'form-control', 
    'id' => 'clie_fecha_nac', 'required']) !!}
</div>
