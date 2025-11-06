
<!-- Field Id -->
<div class="row">
  <div class="form-group col-sm-3">
    <label><strong>ID Compra:</strong></label>
    <div>{{ $compra->id_compra }}</div>
  </div>

  <!-- Field Fecha -->
  <div class="form-group col-sm-3">
    <label><strong>Fecha:</strong></label>
    <div>{{ \Carbon\Carbon::parse($compra->fecha_compra)->format('d/m/Y') }}</div>
  </div>

  <!-- Field Usuario -->
  <div class="form-group col-sm-3">
    <label><strong>Usuario:</strong></label>
    <div>{{ $compra->usuario ?? ($compra->user_name ?? '') }}</div>
  </div>

  <!-- Field Total -->
  <div class="form-group col-sm-3">
    <label><strong>Total:</strong></label>
    <div class="text-right">{{ number_format($compra->total ?? 0, 0, ',', '.') }}</div>
  </div>

  <!-- Field Condicion Compra -->
  <div class="form-group col-sm-3">
    <label><strong>Condicion De Compra:</strong></label>
    <div class="text-center">{{ ($compra->condicion_compra ?? '') }}</div>
  </div>

  <!-- Field Sucursal -->
  <div class="form-group col-sm-3">
    <label><strong>Sucursal:</strong></label>
    <div class="text-center">{{ ($compra->sucursal ?? '') }}</div>
  </div>
</div>

<!-- Field Proveedor -->
<div class="row">
  <div class="form-group col-sm-6">
    <label><strong>Proveedor:</strong></label>
    <div>
      {{ $compra->proveedor ?? $compra->id_proveedor }}
    </div>
  </div>
</div>