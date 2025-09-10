<!-- Id Apertura Field -->
{!! Form::hidden('id_apertura', null, ['class' => 'form-control']) !!}
<!-- Fecha Venta Field -->
<div class="form-group col-sm-4">
    {!! Form::label('fecha_venta', 'Fecha Venta:') !!}
    {!! Form::date('fecha_venta', \Carbon\Carbon::now()->format('Y-m-d'), [
        'class' => 'form-control',
        'id' => 'fecha_venta',
        'required',
        'readonly',
    ]) !!}
</div>

<!-- Factura Nro Field -->
<div class="form-group col-sm-4">
    {!! Form::label('factura_nro', 'Factura Nro:') !!}
    {!! Form::text('factura_nro', null, ['class' => 'form-control', 'readonly']) !!}
</div>

<!-- User Id Field -->
<div class="form-group col-sm-4">
    {!! Form::label('user_id', 'Responsable:') !!}
    {!! Form::text('user_name', $usuario, ['class' => 'form-control', 'readonly']) !!}
    {!! Form::hidden('user_id', auth()->user()->id, ['class' => 'form-control']) !!}
</div>

<!-- Id Cliente Field -->
<div class="form-group col-sm-4">
    {!! Form::label('id_cliente', 'Cliente:') !!}
    {!! Form::select('id_cliente', $clientes, null, [
        'class' => 'form-control',
        'required',
        'placeholder' => 'Seleccione un cliente',
    ]) !!}
</div>

<!-- Condicion venta Field -->
<div class="form-group col-sm-4">
    {!! Form::label('condicion_venta', 'Condición de Venta:') !!}
    {!! Form::select('condicion_venta', $condicion_venta, null, [
        'class' => 'form-control',
        'id' => 'condicion_venta',
        'required',
    ]) !!}
</div>

<!-- sucursal -->
<div class="form-group col-sm-4">
    {!! Form::label('id_sucursal', 'Sucursal:') !!}
    {!! Form::select('id_sucursal', $sucursales, null, [
        'class' => 'form-control',
        'id' => 'id_sucursal',
        'required',
    ]) !!}
</div>

<!-- Intervalo de Vencimiento Field -->
<div class="form-group col-sm-6" id="div-intervalo" style="display: none;"> 
    {!! Form::label('intervalo', 'Intervalo de Vencimiento:') !!}
    {!! Form::select('intervalo', $intervalo_vencimiento, null, [
        'class' => 'form-control',
        'placeholder' => 'Seleccione un intervalo',
        'id' => 'intervalo'
    ]) !!}
</div>

<!-- Cantidad cuota Field -->
<div class="form-group col-sm-6" id="div-cantidad-cuota" style="display: none;">
    {!! Form::label('cantidad_cuota', 'Cantidad Cuota:') !!}
    {!! Form::number('cantidad_cuota', null, [
        'class' => 'form-control',
        'placeholder' => 'Ingrese la cantidad de cuotas',
        'id' => 'cantidad_cuota'
    ]) !!}
</div>

<!-- Detalle de venta -->
<div class="form-group col-sm-12"> 
    @includeIf('ventas.detalle')
</div>


<!-- Total Field -->
<div class="form-group col-sm-6">
    {!! Form::label('total', 'Total:') !!}
    {!! Form::text('total', isset($ventas) ? number_format($ventas->total, 0, ',', '.') : null, ['class' => 'form-control', 'id' => 'total', 'readonly']) !!}
</div>

@includeIf('ventas.modal_producto')

<!-- Js -->
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // comenzar la carga con document ready
        $(document).ready(function() {
            
            /** CONSULTAR AJAX PARA LLENAR POR DEFECTO EL MODAL AL ABRIR SE CONSULTA LA URL */
            document.getElementById('buscar').addEventListener('click', function() {
                $('#productSearchModal').modal('show'); // Mostrar el modal
                fetch('{{ url('buscar-productos') }}?cod_suc=' + $("#id_sucursal").val())// capturar valor de sucursal utilzando val()
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('modalResults').innerHTML = html; // innerHTML es para cargar en el modal
                    })
                    .catch(error => {
                        console.error('Error:', error); // mostrar error en consola
                    });
            });

            // Ocultar o mostra campos segun seleccion de condicion de venta
            $("#condicion_venta").on("change", function() {
                var condicion_venta = $(this).val();// es capturar el dato selecciona con this.val()
                if(condicion_venta == 'CONTADO') {
                    //hide es para ocultar
                    $("#div-intervalo").hide();
                    $("#div-cantidad-cuota").hide();
                    // prop es para asignar una propiedad al campo input y decirle no requerido
                    $("#intervalo").prop('required', false);
                    $("#cantidad_cuota").prop('required', false);
                } else {
                    //show es para mostrar
                    $("#div-intervalo").show(); 
                    $("#div-cantidad-cuota").show();
                    // prop es para asignar una propiedad al campo input y decirle es requerido
                    $("#intervalo").prop('required', true);
                    $("#cantidad_cuota").prop('required', true);
                    
                }
            });
        });
        
        $(document).ready(function() {

            // NUEVO: Formatear el total antes de enviar el formulario
            $('form').on('submit', function() {
                // Obtener el valor actual del total (con puntos)
                var totalConFormato = $('#total').val();
                // Eliminar los puntos para enviar solo el número
                var totalLimpio = totalConFormato.replace(/\./g, '');
                // Actualizar el campo hidden con el valor limpio
                $('#total_valor').val(totalLimpio);
                // Actualizar el campo total con el valor limpio (temporalmente para el envío)
                $('#total').val(totalLimpio);
            });

            // Restaurar el formato visual después del envío (si es necesario)
            @if (isset($ventas))
                // Formatear el total al cargar la página para edición
                var totalValor = {{ $ventas->total }};
                $('#total').val(totalValor.toLocaleString('es-PY', {
                    minimumFractionDigits: 0
                }));
            @endif
        });
    </script>
@endpush