@extends('layouts.app')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1>Stocks</h1></div>
      <div class="col-sm-6">
      </div>
    </div>
  </div>
</section>

<div class="content px-3">
  @include('sweetalert::alert')
  @include('adminlte-templates::common.errors')
  
  <div class="card card-default">
    <div class="card-body">
        <form method="GET" action="{{ route('stocks.index') }}" id="filter-form">
            <div class="row">

                <div class="form-group col-md-4">
                    {!! Form::label('buscar', 'Busqueda:') !!}
                    {!! Form::text('buscar', request('buscar'), ['class' => 'form-control', 'placeholder' => 'Buscar...', 'id' => 'stock-search-input']) !!}
                </div>
                
                <div class="form-group col-md-3">
                    {!! Form::label('id_sucursal', 'Sucursales:') !!}
                    {!! Form::select('id_sucursal', $sucursales, request('id_sucursal'), ['class' => 'form-control', 'placeholder' => 'Todas las sucursales', 'id' => 'id_sucursal']) !!}
                </div>

                <div class="form-group col-md-3">
                    {!! Form::label('id_producto', 'Filtrar por Producto:') !!}
                    {!! Form::select('id_producto', $productos, request('id_producto'), ['class' => 'form-control', 'placeholder' => 'Todos los productos', 'id' => 'id_producto']) !!}
                </div>

                <div class="form-group col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Buscar">
                        <i class="fas fa-search"></i>
                    </button>

                    <button type="button" id="btn-limpiar-filtros" class="btn btn-default ml-2" data-toggle="tooltip" data-placement="top" title="Borrar filtros">
                        <i class="fas fa-eraser"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
  </div>
  <!-- Fin Formulario de Filtros -->

  {{-- Este es el contenedor que se actualizará por AJAX --}}
  <div class="card tabla-container">
      @include('stocks.table')
  </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // 1. Inicializar los tooltips (Tu Modificación 2)
    $('[data-toggle="tooltip"]').tooltip();

    // 2. Lógica de búsqueda AJAX (Tu Modificación 1)
    let searchTimer; // Variable para el temporizador (debounce)

    // Función principal para la búsqueda AJAX
    function fetchStockData() {
        // Obtenemos todos los valores de los filtros
        let buscar = $('#stock-search-input').val();
        let sucursal = $('#id_sucursal').val();
        let producto = $('#id_producto').val();
        
        // Construimos la URL con los parámetros
        let url = new URL('{{ route('stocks.index') }}');
        url.searchParams.set('buscar', buscar || '');
        url.searchParams.set('id_sucursal', sucursal || '');
        url.searchParams.set('id_producto', producto || '');

        // Hacemos la petición Fetch
        fetch(url.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest' // Esencial para que el controlador detecte AJAX
            }
        })
        .then(response => response.text())
        .then(html => {
            // Reemplazamos solo el contenido de la tabla
            $('.tabla-container').html(html);
            // Re-inicializamos tooltips en la nueva tabla (si los hubiera)
            $('[data-toggle="tooltip"]').tooltip();
        })
        .catch(error => console.error('Error en la búsqueda AJAX:', error));
    }

    // Disparar AJAX al teclear en el buscador (con 500ms de espera)
    $('#stock-search-input').on('keyup', function() {
        clearTimeout(searchTimer); // Limpiamos el temporizador anterior
        searchTimer = setTimeout(fetchStockData, 500); // Esperamos 500ms antes de buscar
    });

    // Disparar AJAX al cambiar un filtro <select>
    $('#id_sucursal, #id_producto').on('change', function() {
        fetchStockData(); // Aquí lo hacemos al instante
    });

    // Evitar que el formulario se envíe de forma tradicional (al presionar Enter o el botón)
    $('#filter-form').on('submit', function(e) {
        e.preventDefault(); // Detenemos el envío normal
        fetchStockData();   // Y en su lugar, ejecutamos el AJAX
    });

    // *** INICIO DE LA MODIFICACIÓN ***
    // Manejar clic en el botón "Limpiar"
    $('#btn-limpiar-filtros').on('click', function() {
        // 1. Limpiar los campos de filtro
        $('#stock-search-input').val('');
        $('#id_sucursal').val(null).trigger('change.select2'); // Limpiar select2
        $('#id_producto').val(null).trigger('change.select2'); // Limpiar select2

        // 2. Recargar la tabla con los filtros limpios
        fetchStockData();

        // 3. Poner el focus en el buscador (Tu petición)
        $('#stock-search-input').focus();
    });
    // *** FIN DE LA MODIFICACIÓN ***

    // Manejar paginación AJAX
    // Usamos $(document).on(...) para que funcione en los enlaces que se cargan con AJAX
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault(); // Prevenir la recarga de página
        let url = $(this).attr('href'); // Obtener la URL de la página

        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            $('.tabla-container').html(html);
            // Re-inicializamos tooltips
            $('[data-toggle="tooltip"]').tooltip();
        })
        .catch(error => console.error('Error en la paginación AJAX:', error));
    });
});
</script>
@endpush