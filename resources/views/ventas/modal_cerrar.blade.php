<div class="modal fade" id="cerrar-caja" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"> <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h4 class="modal-title"><i class="fas fa-cash-register"></i> Cerrar Caja / Arqueo</h4>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            
            <div class="modal-body">
                {{-- El action se llenará dinámicamente con JS --}}
                <form method="GET" action="" id="form-cierre"> 
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label>Fecha Apertura</label>
                            {!! Form::text('fecha_apertura', null, ['class' => 'form-control', 'readonly', 'id' => 'fecha_visual']) !!}
                        </div>
                        <div class="col-md-6">
                            <label>Caja</label>
                            {!! Form::text('caja_nombre', null, ['class' => 'form-control', 'readonly', 'id' => 'caja_visual']) !!}
                        </div>
                    </div>
                    
                    <hr>
                    <h5 class="text-center text-muted">Resumen Financiero</h5>
                    <br>

                    <div class="row text-center">
                        <div class="col-md-4">
                            <label class="text-primary">Saldo Inicial (+)</label>
                            <input type="text" id="monto_apertura_visual" class="form-control text-center font-weight-bold" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="text-success">Total Ingresos (+)</label>
                            <input type="text" id="total_ingresos_visual" class="form-control text-center font-weight-bold" readonly style="color: #28a745;">
                            <small class="form-text text-muted">Cobros Ventas</small>
                        </div>
                        <div class="col-md-4">
                            <label class="text-danger">Total Egresos (-)</label>
                            <input type="text" id="total_egresos_visual" class="form-control text-center font-weight-bold" readonly style="color: #dc3545;">
                            <small class="form-text text-muted">Pagos Proveedores</small>
                        </div>
                    </div>

                    <br>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-secondary text-center">
                                <label style="font-size: 1.1em;">Saldo Teórico (Lo que debería haber)</label>
                                <input type="text" id="saldo_esperado_visual" class="form-control form-control-lg text-center" readonly 
                                       style="font-size: 1.5em; font-weight: bold; background-color: transparent; border: none;">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-12">
                            <label style="font-size: 1.2em;">Monto Cierre (Dinero físico en caja)</label>
                            {!! Form::text('monto_cierre', null, [
                                'class' => 'form-control form-control-lg text-center',
                                'id' => 'monto_cierre',
                                'placeholder' => 'Ingrese el monto contado manualmente',
                                'required' => 'required',
                                'onkeyup' => "format(this)",
                                'autocomplete' => 'off'
                            ]) !!}
                            <small class="text-muted">Ingrese la cantidad de dinero que cuenta físicamente.</small>
                        </div>
                    </div>

                    <div class="modal-footer mt-4">
                        <button type="submit" class="btn btn-danger btn-lg btn-block">Confirmar Cierre de Caja</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>