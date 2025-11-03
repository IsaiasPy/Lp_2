<table class="table tabla table-bordered" id="cargos-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Clientes</th>
                                <th>Fecha Nac.</th>
                                <th>Edad</th>
                                <th>Telefono</th>
                                <th>Nro CI</th>
                                <th>Direccion</th>
                                <th>Ciudad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($clientes as $cliente)
                                <tr>
                                    <td>{{ $cliente->id_cliente }}</td>
                                    <td>{{ $cliente->clie_nombre . ' ' . $cliente->clie_apellido }}</td>
                                    <td>{{ \Carbon\Carbon::parse($cliente->clie_fecha_nac)->format('d/m/Y')}}</td>
                                    <td>{{ $cliente->edad }}</td>
                                    <td>{{ $cliente->clie_telefono }}</td>
                                    <td>{{ $cliente->clie_ci }}</td>
                                    <td>{{ $cliente->clie_direccion }}</td>
                                    <td>{{ $cliente->ciudad }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>