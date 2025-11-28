@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-12">
                <h1>
                    Pagar Cuota N° {{ $deuda->nro_cuenta ?? '' }}
                </h1>
            </div>
        </div>
    </div>
</section>

<div class="content px-3">

    @include('sweetalert::alert')
    <div class="card">

        {!! Form::open(['route' => 'pagosproveedor.cxp.store']) !!}

        <div class="card-body">

            {!! Form::hidden('id_cta', $deuda->id_cta) !!}

            {{-- Usamos el ID real de la caja que validamos en el controlador --}}
            {!! Form::hidden('id_caja_abierta', $caja_abierta->id_apertura) !!}
            <div class="row">
                <div class="form-group col-sm-3">
                    {!! Form::label('fecha_vencimiento', 'Vencimiento:') !!}
                    {!! Form::text('fecha_vencimiento', \Carbon\Carbon::parse($deuda->vencimiento)->format('d/m/Y'),
                    ['class' => 'form-control', 'readonly']) !!}
                </div>

                <div class="form-group col-sm-3">
                    {!! Form::label('factura', 'Ref. Factura Compra:') !!}
                    {!! Form::text('factura', $deuda->factura,
                    ['class' => 'form-control', 'readonly']) !!}
                </div>

                <div class="form-group col-sm-6">
                    {!! Form::label('proveedor', 'Proveedor:') !!}
                    {!! Form::text('proveedor', $deuda->proveedor,
                    ['class' => 'form-control', 'readonly']) !!}
                </div>
            </div>

            <div class="row">
                <div class="form-group col-sm-4">
                    {!! Form::label('saldo_pendiente', 'Saldo Pendiente:') !!}
                    {!! Form::text('saldo_pendiente', number_format($deuda->saldo, 0, ',', '.'), [
                    'class' => 'form-control',
                    'readonly',
                    'id' => 'vtot_fac' // ID usado por JS para validar tope
                    ]) !!}
                </div>

                <div class="form-group col-sm-8">
                    {!! Form::label('nro_recibo', 'N° Recibo / Comprobante de Pago:') !!}
                    {!! Form::text('nro_recibo', null, [
                    'class' => 'form-control',
                    'placeholder' => 'Ingrese el número del recibo entregado por el proveedor',
                    'required' => 'required'
                    ]) !!}
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-12">
                    <label>Formas de Pago (Salida de Dinero)</label>
                </div>
                <table class="table listado_for_pago">
                    <thead>
                        <tr>
                            <th style="width:40%;">Método</th>
                            <th class="text-center" style="width:30%;">Importe a Pagar</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>

                    <tfoot>
                        <tr>
                            <td colspan="3">
                                <a href="javascript:void(0);" class='btn btn-primary btn-sm btn-add-row'>
                                    <i class="fa fa-plus"></i> Agregar Forma de Pago
                                </a>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="row mt-3">
                <div class="form-group col-sm-6 text-right">
                    <label class="mt-2">Total a Pagar:</label>
                </div>
                <div class="form-group col-sm-6">
                    {!! Form::text('total_visual', 0, [
                    'class' => 'form-control text-right vtot_fpa',
                    'readonly',
                    'style' => 'font-weight: bold; font-size: 1.2em;'
                    ]) !!}
                    <input type="hidden" id="monto_pagado" name="monto_pagado">
                </div>
            </div>
        </div>

        <div class="card-footer">
            {!! Form::submit('Registrar Pago', ['class' => 'btn btn-success']) !!}
            <a href="{{ route('cuentasapagar.index') }}" class="btn btn-default"> Cancelar </a>
        </div>

        {!! Form::close() !!}

    </div>

    <template tpl-pagos>
        <tr>
            <td>
                {!! Form::select('id_metodo_pago[]', $metodos_pago, null, [
                'class' => 'form-control',
                'style' => 'width: 100%',
                'required' => 'required'
                ]) !!}
            </td>

            <td class="text-center">
                <input class="form-control text-center totalFpa" type="text" min="1" name="importe_pago[]" onchange="actTotalFpa(this)" onkeyup="format(this);" style="text-align: center" required>
            </td>

            <td class="text-center">
                <a href="javascript:void(0);" class="btn btn-danger btn-sm" title="Eliminar Fila" onclick="eliminarFila(this)">
                    <i class="far fa-trash-alt "></i>
                </a>
            </td>
        </tr>
    </template>
</div>
@endsection

@push('page_scripts')
<script type="text/javascript">
    $(document).ready(function() {

        // --- 1. FUNCIÓN CENTRAL PARA AGREGAR FILAS ---
        function agregarFila() {
            // Buscamos el template
            const template = document.querySelector('template[tpl-pagos]');

            if (!template) {
                console.error("Error: No se encontró el template 'tpl-pagos'.");
                return;
            }

            // Clonamos el contenido
            const clone = template.content.cloneNode(true);

            // Lo agregamos al cuerpo de la tabla
            $('.listado_for_pago tbody').append(clone);

            console.log("Fila agregada correctamente.");
        }

        // --- 2. EVENTO DEL BOTÓN (Usamos 'off' para evitar dobles clics) ---
        $(document).off('click', '.btn-add-row').on('click', '.btn-add-row', function(e) {
            e.preventDefault();
            agregarFila();
        });

        // --- 3. INICIALIZACIÓN: AGREGAR PRIMERA FILA AUTOMÁTICAMENTE ---
        // Si la tabla está vacía al cargar, agregamos la primera fila
        if ($('.listado_for_pago tbody tr').length === 0) {
            agregarFila();
        }

        // --- 4. PREVENIR SUBMIT CON ENTER ---
        $("form").keypress(function(e) {
            if (e.which == 13) {
                return false;
            }
        });
    });

    // --- 5. FUNCIONES GLOBALES (Fuera del document.ready) ---

    /** ELIMINAR FILA **/
    function eliminarFila(t) {
        // Evitar dejar la tabla vacía (opcional)
        // if ($('.listado_for_pago tbody tr').length <= 1) { return; } 

        $(t).closest('tr').remove();
        actTotFpa();
    }

    /** CALCULAR TOTALES Y VALIDAR **/
    function actTotalFpa(t) {
        // Referencia al input actual
        var $input = $(t).closest("tr").find("[name='importe_pago[]']");
        var valorRaw = $input.val().replace(/\./g, '');
        var saldoPendiente = $("#vtot_fac").val().replace(/\./g, '');

        // Validaciones
        if (isNaN(valorRaw) || valorRaw === "") {
            valorRaw = 0;
        }

        var valorFloat = parseFloat(valorRaw);

        if (valorFloat < 0) {
            Swal.fire({
                title: 'Error'
                , text: 'El monto debe ser positivo'
                , icon: 'warning'
            });
            $input.val(0);
            actTotFpa();
            return;
        }

        // Formatear visualmente
        $input.val(formatMoney(valorRaw));

        // Recalcular total general
        actTotFpa();

        // Validación de Tope
        validarTope(saldoPendiente);
    }

    /** SUMAR TOTAL FINAL **/
    function actTotFpa() {
        var total = 0;
        $('.listado_for_pago tbody tr').each(function(idx, el) {
            let val = $(el).find("[name='importe_pago[]']").val();
            // Aseguramos que sea string antes de replace
            let monto = parseInt(String(val).replace(/\./g, '')) || 0;
            total += monto;
        });

        $(".vtot_fpa").val(formatMoney(total));
        $("#monto_pagado").val(total);
    }

    function validarTope(saldoPendiente) {
        var totalAcumulado = parseInt($("#monto_pagado").val()) || 0;
        var saldo = parseInt(saldoPendiente) || 0;

        if (totalAcumulado > saldo) {
            Swal.fire({
                title: 'Atención'
                , text: 'El total a pagar (' + formatMoney(totalAcumulado) + ') excede el saldo pendiente (' + formatMoney(saldo) + ').'
                , icon: 'warning'
            });
            // Opcional: Podrías resetear el último input o dejar que el usuario corrija
        }
    }

    /** FORMATO DE MILES **/
    function formatMoney(n, c, d, t) {
        var c = isNaN(c = Math.abs(c)) ? 0 : c
            , d = d == undefined ? "," : d
            , t = t == undefined ? "." : t
            , s = n < 0 ? "-" : ""
            , i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c)))
            , j = (j = i.length) > 3 ? j % 3 : 0;

        return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
    }

    function format(input) {
        var num = input.value.replace(/\./g, '');
        if (!isNaN(num)) {
            input.value = formatMoney(num);
        }
    }

</script>
@endpush
