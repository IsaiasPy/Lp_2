@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-12">
                <h1>
                    Cobrar Cuota N° {{ $deuda->nro_cuota ?? '' }}
                </h1>
            </div>
        </div>
    </div>
</section>

<div class="content px-3">

    @include('sweetalert::alert')
    <div class="card">

        {!! Form::open(['route' => 'cobros.cxc.store']) !!}

        <div class="card-body">
            <div class="row">
                
                {!! Form::hidden('id_cuenta', $deuda->id_cuenta, ['class' => 'form-control']) !!}
                
                {!! Form::hidden('id_caja_abierta', auth()->user()->id_apertura_abierta ?? 1) !!}
                
                <div class="form-group col-sm-6">
                    {!! Form::label('fecha_vencimiento', 'Fecha Vencimiento:') !!}
                    {!! Form::date(
                        'fecha_vencimiento',
                        \Carbon\Carbon::parse($deuda->vencimiento)->format('Y-m-d'),
                        ['class' => 'form-control', 'id' => 'fecha_vencimiento', 'readonly' => 'readonly'],
                    ) !!}
                </div>

                <div class="form-group col-sm-6">
                    {!! Form::label('factura_nro', 'Nro Factura:') !!}
                    {!! Form::text('factura_nro', $deuda->factura_nro,
                    ['class' => 'form-control',
                    'readonly' => 'readonly']) !!}
                </div>

                <div class="form-group col-sm-6">
                    {!! Form::label('cliente', 'Cliente:') !!}
                    {!! Form::text('cliente', $deuda->cliente, [
                    'class' => 'form-control',
                    'readonly' => 'readonly',
                    ]) !!}
                </div>

                <div class="form-group col-sm-6">
                    {!! Form::label('saldo_pendiente', 'Saldo Pendiente:') !!}
                    {!! Form::text('saldo_pendiente', number_format($deuda->saldo, 0, ',', '.'), [
                    'class' => 'form-control',
                    'readonly' => 'readonly',
                    'id' => 'vtot_fac' // Mantenemos este ID para el JS
                    ]) !!}
                </div>
            </div>

            <div class="row">
                <table class="table listado_for_pago">
                    <thead>
                        <tr>
                            <th style="width:35%;min-width:240px;">Forma de cobro</th>
                            <th class="text-center" style="width:20%;">Importe (Pago)</th>
                            <th class="text-center">Nro Voucher</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>

                    <tfoot>
                        <tr>
                            <td colspan="3">
                                <a href="javascript:void(0);"
                                    class='btn btn-primary btn-sm btn-add-row'>
                                    <i class="fa fa-plus"></i> Agregar
                                </a>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="row">
                <div class="form-group col-sm-6">
                    {!! Form::label('pendiento_cobro', 'Pendiente:') !!}
                    {!! Form::text('pendiento_cobro', number_format($deuda->saldo, 0, ',', '.') , [
                    'class' => 'form-control text-right',
                    'readonly' => 'readonly',
                    'id' => 'vtot_pend',
                    ]) !!}
                </div>

                <div class="form-group col-sm-6">
                    {!! Form::label('total', 'Total Pago:') !!}
                    {!! Form::text('total', 0, [
                    'class' => 'form-control text-right vtot_fpa',
                    'readonly' => 'readonly',
                    ]) !!}
                    <input type="hidden" id="monto_cobrado" name="monto_cobrado">
                    <input type="hidden" id="tot_fpa" name="tot_fpa">
                </div>
            </div>
        </div>

        <div class="card-footer">
            {!! Form::submit('Aplicar Pago', ['class' => 'btn btn-primary']) !!}
            <a href="{{ route('cuentasacobrar.index') }}" class="btn btn-default"> Cancelar </a>
        </div>

        {!! Form::close() !!}

    </div>

    <template tpl-cobros>
        <tr>
            <td>
                {!! Form::select('id_metodo_pago[]', $metodos_pago, null, [
                'class' => 'form-control',
                'style' => 'width: 100%',
                'id' => 'forma_pago',
                ]) !!}
                {!! Form::hidden('id_cobro[]', null) !!}
            </td>

            <td class="text-center">
                <input class="form-control text-center totalFpa"
                    type="text" min="1"
                    name="importe_pago[]"
                    onchange="actTotalFpa(this)"
                    onkeyup="format(this);"
                    style="text-align: center">
            </td>

            <td class="text-center" style="width: 20%">
                <input class="form-control text-center"
                type="text"
                name="nro_voucher[]" style="text-align: center">
            </td>

            <td class="text-center">
                <a href="javascript:void(0);"
                class="btn btn-danger"
                title="Eliminar Fila"
                onclick="eliminarFila(this)">
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
        /** evitar submit con el boton enter **/
        $("form").keypress(function(e) {
            if (e.which == 13) {
                return false;
            }
        });

        /** funcion clic para clonar filas tr para la tabla de pagos **/
        $(".btn-add-row").click(function (e) {
            e.preventDefault();
            const $this = $(this);
            const tableRef = $this.parents(".listado_for_pago");
            const row_pagos = document.querySelector('[tpl-cobros]')
            .content.cloneNode(true);

            if ($(row_pagos).length > 0) {
                tableRef.find("tbody").append(row_pagos);
            }
            $this.removeClass('disabled');
        });
    });

    /** FUNCION PARA ELIMINAR FILA DE UNA TABLA **/
    function eliminarFila(t) {
        $(t).parents('tr').remove();
        actTotalFpa();
    }

    /** Funcion para calcular subtotal de forma de pagos **/
    function actTotalFpa(t) {
        var error = false;
        // Referencia al input que se modificó
        var $input = $(t).parents("tr").find("[name='importe_pago[]']"); 
        
        var totalFpa = $input.val().replace(/\./g, '');
        var totalFpa1 = $input.val();
        
        var vtot = 0;
        var totfpa = 0;
        var totalFac = 0;
        
        // Asignación de valores
        totalFac = $("#vtot_fac").val().replace(/\./g, ''); // Saldo Pendiente
        
        if (isNaN(totalFpa)) {
             Swal.fire({ title: 'Error!', text: 'Ingrese un número válido', icon: 'info', confirmButtonText: 'Ok' });
             error = true;
        } else if (parseFloat(totalFpa) < 0) {
             Swal.fire({ title: 'Error!', text: 'Ingrese un número mayor o igual a cero', icon: 'info', confirmButtonText: 'Ok' });
             error = true;
        }

        if (error) {
             $input.val(0).select(); // Resetear el valor
             actTotFpa();
             return;
        } else {
             $input.val(formatMoney(totalFpa));
             actTotFpa();
        }

        var total = 0;
        $('.listado_for_pago tbody tr').each(function(idx, el) {
            row = {
                // CAMBIO 5D: Usamos el nuevo nombre del campo JS
                'monto': parseInt($(el).find("[name='importe_pago[]']").val().replace(/\./g, '')),
            };
            total += row.monto;
        });

        if (total > totalFac) {
             Swal.fire({
                 title: 'Error!',
                 text: 'El monto total pagado (' + formatMoney(total) + ') no puede exceder el saldo pendiente (' + formatMoney(totalFac) + ')!',
                 icon: 'info',
                 confirmButtonText: 'Ok'
             });

             $input.val(0).select();
             actTotFpa();
        } 
    }

    function actTotFpa() {
        var total = 0,
            totfac = $("#vtot_fac").val().replace(/\./g, '');
        $('.listado_for_pago tbody tr').each(function(idx, el) {
            row = {
                'monto': parseInt($(el).find("[name='importe_pago[]']").val().replace(/\./g, '')),
            };
            total += row.monto;
        });
        console.log("totales por forma de pago::::", total);

        $(".vtot_fpa").val(formatMoney(total));
        // Aquí enviamos el MONTO TOTAL A COBRAR
        $("#monto_cobrado").val(total); 
        $("#vtot_pend").val(formatMoney(totfac - total));
    }

    function formatMoney (n, c, d, t) {
        let s, i, j;
        c = isNaN(c = Math.abs(c)) ? 0 : c;
        d = d === undefined ? "," : d;
        t = t === undefined ? "." : t;
        s = n < 0 ? "-" : "";
        i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c)));
        j = (j = i.length) > 3 ? j % 3 : 0;
        return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) +
            (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
    }
</script>
@endpush