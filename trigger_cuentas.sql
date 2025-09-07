CREATE OR REPLACE FUNCTION generar_ctas_cobrar()
  RETURNS trigger AS
$BODY$ 
DECLARE 
	-- se declaran variable a utilizar
    monto_a_cobrar integer;
    vencimiento date;
    cont integer; -- contador
    dias interval; -- Cambiado el tipo de datos a interval
    primer_vto date;
BEGIN 

	-- Solo si la condicion venta = CREDITO DEBE EJECUTARSE
	if new.condicion_venta = 'Credito' THEN
	    monto_a_cobrar = round(new.total / new.cantidad_cuota);--round es redondeo
	    -- Construir el intervalo a partir de la variable días
	    dias = (new.intervalo || ' days')::interval;

	    select NEW.fecha_venta + dias into primer_vto;

		-- Recorrer deacuerdo a la cantidad cuota para realizar los insert en cuentas_a_cobrar
	    FOR cont IN 1..new.cantidad_cuota LOOP
			-- calcular vencimiento segun intervalo y fecha de venta
	        vencimiento = primer_vto + ((cont - 1) * dias);

	        INSERT INTO cuentas_a_cobrar(id_cliente, id_venta, vencimiento, importe, nro_cuota, estado)
	        VALUES(new.id_cliente, new.id_venta, vencimiento, monto_a_cobrar, cont, 'PENDIENTE');

	    END LOOP;
	   
    	RETURN NEW;
   	end if;
	
	return null;
END; 
$BODY$
LANGUAGE plpgsql VOLATILE
COST 100;

-- crear el trigger para eso primero debe ejecutarse la funcion
create trigger trg_ctas_a_cobrar after
insert
on
public.ventas for each row execute procedure generar_ctas_cobrar()