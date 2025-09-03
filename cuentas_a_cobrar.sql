create table cuentas_a_cobrar (
id_cuenta serial,
id_cliente integer not null,
id_venta integer not null,
vencimiento date not null,
importe float not null,
nro_cuota integer not null,
estado varchar(20) default 'Pendiente',
primary key(id_cuenta),
foreign key (id_cliente) references clientes(id_cliente),
foreign key (id_venta) references ventas(id_venta)
)