@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Ventas</h1>
                </div>
                <div class="col-sm-6">
                    @if (
                        !empty($caja_abierta) &&
                            \Carbon\Carbon::parse($caja_abierta->fecha_apertura)->format('Y-m-d') == \Carbon\Carbon::now()->format('Y-m-d'))
                        <a class="btn btn-primary float-right" href="{{ route('ventas.create') }}">
                            <i class="fas fa-plus"></i>
                            Nueva Venta
                        </a>
                    @endif

                    @if (empty($caja_abierta))
                        <a class="btn btn-default float-right mr-2" data-toggle="modal" data-target="#apertura"
                            href="#">
                            <i class="fas fa-cart-plus"></i>
                            Abrir Caja
                        </a>
                    @endif

                    @if (
                        !empty($caja_abierta) &&
                            \Carbon\Carbon::parse($caja_abierta->fecha_apertura)->format('Y-m-d') <= \Carbon\Carbon::now()->format('Y-m-d'))
                        <a class="btn btn-danger float-right mr-2" href="#" 
                            id="cerrar" 
                            data-id="{{ isset($caja_abierta) ? $caja_abierta->id_apertura : null }}">
                            <i class="fas fa-lock"></i>
                            Cerrar Caja
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        {{-- @include('flash::message') --}}
        @include('adminlte-templates::common.errors')
        @include('sweetalert::alert')

        <div class="clearfix">
            @includeIf('layouts.buscador', ['url' => url()->current()])
        </div>

        <div class="card tabla-container">
            @include('ventas.table')
        </div>
    </div>

    @includeIf('ventas.modal_apertura', ['cajas' => $cajas])

    @include('ventas.modal_cerrar', ['cajas' => $cajas])

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            
            // --- LÓGICA PARA CERRAR CAJA ---
            $("#cerrar").on("click", function(e) {
                e.preventDefault();
                e.stopPropagation();

                let $this = this;

                if ($this.classList.contains('disabled')) {
                    return false;
                }
                
                const id = $this.getAttribute('data-id');
                const url = "{{ url('apertura_cierre/editCierre') }}/" + id;

                // Llamada AJAX para obtener los totales calculados
                fetch(url, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(res => {
                        if (res.success) {
                            // 1. Llenar Información General
                            document.getElementById("fecha_visual").value = res.apertura.fecha_apertura;
                            document.getElementById("caja_visual").value = 'Caja #' + res.apertura.id_caja;

                            // 2. Llenar el Balance Financiero (Nuevos campos)
                            document.getElementById("monto_apertura_visual").value = formatMoney(res.saldo_inicial);
                            document.getElementById("total_ingresos_visual").value = formatMoney(res.total_ingresos);
                            document.getElementById("total_egresos_visual").value = formatMoney(res.total_egresos);
                            
                            // 3. Llenar el Saldo Esperado (Teórico)
                            document.getElementById("saldo_esperado_visual").value = formatMoney(res.saldo_esperado);

                            // 4. Limpiar el input de arqueo para que el usuario escriba
                            document.getElementById("monto_cierre").value = '';

                            // 5. Configurar la URL del formulario
                            const formUrl = "{{ url('apertura_cierre/cerrar_caja') }}/" + id;
                            let form = document.getElementById('form-cierre');
                            form.setAttribute("action", formUrl);

                            // 6. Mostrar Modal
                            $("#cerrar-caja").modal("show"); 
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: res.message,
                                icon: 'error',
                                confirmButtonText: 'Ok'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Hubo un problema al procesar la solicitud.',
                            icon: 'error',
                            confirmButtonText: 'Ok'
                        });
                    });
            });
        });

        /** Formateador de Miles (visual) */
        function formatMoney (n, c, d, t) {
            var c = isNaN(c = Math.abs(c)) ? 0 : c,
                d = d == undefined ? "," : d,
                t = t == undefined ? "." : t,
                s = n < 0 ? "-" : "",
                i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))),
                j = (j = i.length) > 3 ? j % 3 : 0;

            return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
        }

        /** Función llamada por el evento onkeyup en el input */
        function format(input){
            var num = input.value.replace(/\./g,''); // Quitar puntos existentes
            if(!isNaN(num)){
                input.value = formatMoney(num); // Volver a formatear
            }
        }

    </script>
@endpush