<div class="card-body p-0">
  <div class="table-responsive">
    <table class="table" id="compras-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Proveedor</th>
          <th>Fecha</th>
          <th>Usuario</th>
          <th class="text-right">Total</th>
          <th colspan="3">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($compras as $compra)
          <tr>
            <td>{{ $compra->id_compra }}</td>
            <td>
                {{ $compra->proveedor ?? $compra->id_proveedor }}
            </td>
            <td>{{ \Carbon\Carbon::parse($compra->fecha_compra)->format('d/m/Y') }}</td>
            <td>{{ $compra->usuario ?? ($compra->user_name ?? '') }}</td>
            <td class="text-right">{{ number_format($compra->total ?? 0, 0, ',', '.') }}</td>
            <td width="120">
              <div class="btn-group">
                <a href="{{ route('compras.show', $compra->id_compra) }}" class="btn btn-default btn-xs" title="Ver"><i class="far fa-eye"></i></a>
                @if($compra->estado != 'ANULADO')
                  <a href="{{ route('compras.edit', $compra->id_compra) }}" class="btn btn-info btn-xs" title="Editar"><i class="far fa-edit"></i></a>
                  {!! Form::open(['route' => ['compras.destroy', $compra->id_compra], 'method' => 'delete', 'style' => 'display:inline']) !!}
                    {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return confirm('¿Anular esta compra?')"]) !!}
                  {!! Form::close() !!}
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted">No hay compras registradas.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>