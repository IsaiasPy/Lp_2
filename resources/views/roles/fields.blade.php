<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Rol:') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Guard Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('guard_name', 'Guard Name:') !!}
    {!! Form::text('guard_name', 'web', ['class' => 'form-control','readonly']) !!}
</div>