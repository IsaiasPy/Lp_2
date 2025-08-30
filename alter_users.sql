-- columnas agregadas a la tabla users
alter table users add column ci varchar(20);
alter table users add column direccion text;
alter table users add column telefono varchar(20);
alter table users add column fecha_ingreso date;
alter table users add column estado boolean default true;