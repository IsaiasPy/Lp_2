<!-- Id Apertura Field -->
    {!! Form::hidden('id_apertura', null, ['class' => 'form-control']) !!}

<!-- Fecha Venta Field -->
<div class="form-group col-sm-4">
    {!! Form::label('fecha_venta', 'Fecha Venta:') !!}
    {!! Form::date('fecha_venta', \Carbon\Carbon::now()->format('Y-m-d'), ['class' => 'form-control','id'=>'fecha_venta', 'required', 'readonly']) !!}
</div>

<!-- Factura Nro Field -->
<div class="form-group col-sm-4">
    {!! Form::label('factura_nro', 'Nro de Factura:') !!}
    {!! Form::text('factura_nro', null, ['class' => 'form-control', 'readonly']) !!}
</div>

<!-- User Id Field -->
<div class="form-group col-sm-4">
    {!! Form::label('user_id', 'Responsable:') !!}
    {!! Form::text('user_id', $usuario, ['class' => 'form-control','readonly']) !!}
    {!!  Form::hidden('user_id', Auth::user()->id, ['class' => 'form-control']) !!}
</div>

<!-- Id Cliente Field -->
<div class="form-group col-sm-4">
    {!! Form::label('id_cliente', 'Seleccione Cliente:') !!}
    {!! Form::select('id_cliente',$clientes, null, ['class' => 'form-control', 'required', 'placeholder' => 'Seleccione un cliente']) !!}
</div>

<!-- Condicion venta Field -->
<div class="form-group col-sm-4">
    {!! Form::label('condicion_venta', 'Condicion de Venta:') !!}
    {!! Form::select('condicion_venta', $condicion_venta, null, ['class' => 'form-control', 
    'required',
    'id' => 'condicion_venta', 'required',]) !!}
</div>

<!-- Sucursal Field -->
<div class="form-group col-sm-4">
    {!! Form::label('id_sucursal', 'Sucursal:') !!}
    {!! Form::select('id_sucursal', $sucursales, null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Intervalo de vencimiento Field -->
<div class="form-group col-sm-6" id="div_intervalo" style="display: none;">
    {!! Form::label('intervalo_vencimiento', 'Intervalo de Vencimiento:') !!}
    {!! Form::select('intervalo_vencimiento', $intervalo_vencimiento, null, ['class' => 'form-control',
    'placeholder' => 'Seleccione un intervalo',
    'id' => 'intervalo']) !!}
</div>

<!-- Cantidad de Cuota Field -->
<div class="form-group col-sm-6" id="div_cantidad_cuota" style="display: none;">
    {!! Form::label('cantidad_cuota', 'Total:') !!}
    {!! Form::number('cantidad_cuota', null, [
        'class' => 'form-control',
        'placeholder' => 'Ingrese la cantidad de cuotas',
        'id' => 'cantidad_cuota'
    ]) !!}
</div>

<!--  Detalle de venta Field -->
<div class="form-group col-sm-12">
    @includeIf('ventas.detalle')
</div>

<!-- Total Field -->
<div class="form-group col-sm-6">
    {!! Form::label('total', 'Total:') !!}
    {!! Form::text('total', null, ['class' => 'form-control', 'readonly']) !!}
</div>

<!-- Cargar el modal -->
@include('ventas.modal_producto')

<!-- Js -->
@push('scripts')
    <script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
        //comenzar la carga con document ready
        $(document).ready(function() {
            /** CONSULTAR AJAX PARA LLENAR POR DEFECTO EL MODAL AL ABRIR SE CONSULTA LA URL */
            document.getElementById('buscar').addEventListener('click', function() {
                $('#productSearchModal').modal('show');
                fetch('{{ url('buscar-productos') }}?cod_suc=' + $("#id_sucursal").val()) //capturar el valor de sucursal utilizando val
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('modalResults').innerHTML = html;
                    });
            });
          $('#condicion_venta').on('change', function() {
              var condicion_venta = $(this).val();
            if (condicion_venta == "Contado") {
                $('#div_intervalo').hide();
                $('#div_cantidad_cuota').hide();
                $('#intervalo').prop('required', false); // el prop es para asignar una propiedad al campo input y decirle no requerido
                $('#cantidad_cuota').prop('required', false);
            } else {
                $('#div_intervalo').show();
                $('#div_cantidad_cuota').show();
                $('#intervalo').prop('required', true); // el prop es para asignar una propiedad al campo input y decirle requerido
                $('#cantidad_cuota').prop('required', true);
            }
          });  
        });
    </script>
@endpush