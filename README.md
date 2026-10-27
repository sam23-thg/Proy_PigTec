# Sistema El Puerquito Digital

Aplicación web MVC para la gestión del sistema de la tocinería El Puerquito Digital.

## Módulo de autenticación

- El punto de entrada web es `public/index.php`.
- El formulario de acceso utiliza Bootstrap 5.3 y se encuentra en `app/views/auth/login.php`.
- Las vistas comparten una paleta rosa en `public/assets/css/paleta-rosa.css` y utilizan etiquetas de texto en lugar de iconos decorativos.
- Las credenciales se verifican con `password_verify()`; las cuentas inactivas no pueden iniciar sesión.
- Las cuentas con contraseña temporal deben completar el cambio en `app/views/auth/cambiar_temporal.php` antes de continuar.
- Los recursos propios de la interfaz se sirven desde `public/assets/`.

## Validación de formularios

- Los formularios validan los datos en el navegador para orientar a la persona usuaria y repiten las reglas en los controladores, sin confiar en JavaScript.
- Los nombres personales aceptan letras Unicode y separadores habituales, pero no números; la CURP requiere 18 caracteres con formato estructurado y el teléfono de 10 a 15 dígitos.
- Los precios deben ser positivos con hasta dos decimales y las existencias no negativas con hasta tres decimales.
- Los campos de texto respetan los límites de las columnas y los selectores de rol, género, categoría y unidad rechazan valores ajenos a sus opciones.
- Las contraseñas nuevas requieren al menos 8 caracteres y se limitan a 72 bytes para evitar el truncamiento de BCRYPT.

## Catálogo de ventas

- La ruta `index.php?c=inventario&a=catalogo` muestra productos activos con inventario asociado a usuarios VENTAS y ADMINISTRADOR.
- El catálogo consulta producto e inventario mediante una unión interna y filtra las filas en el navegador mientras se escribe.

## Registro de ventas

- La ruta `index.php?c=venta&a=nueva` presenta el punto de venta a usuarios VENTAS y ADMINISTRADOR.
- La venta y sus artículos se guardan en una sola transacción PDO; los triggers existentes de la base de datos validan y actualizan el inventario al insertar cada detalle.
- Si el trigger informa stock insuficiente con SQLSTATE `45000`, el sistema revierte la transacción y conserva el carrito para corregirlo.

## Pedidos de almacén

- La ruta `index.php?c=pedido&a=pendientes` muestra los pedidos en estado PENDIENTE o EN_PREPARACION a usuarios ALMACEN y ADMINISTRADOR.
- Cada tarjeta presenta el folio y la fecha de venta, y despliega por producto los kilogramos que deben prepararse.

## Control de cuentas de empleados

- La ruta `index.php?c=empleado&a=lista` permite al administrador consultar, editar los datos personales y alternar el estado de las cuentas.
- Las cuentas con `estado = 0` se identifican como inactivas y no pueden iniciar sesión; habilitarlas vuelve a permitir el acceso.
- La ficha conserva usuario, CURP y rol como datos de solo lectura, y nunca muestra la contraseña.

## Requisitos de entorno

- PHP 8 o posterior con PDO MySQL habilitado.
- MariaDB o MySQL con la base de datos `elpuerquitodigital` existente.
- Parámetros locales de conexión definidos en el archivo `.env` de la raíz; no se incluyen credenciales de prueba en este repositorio.
- La conexión PDO se configura en `config/database.php` con codificación `utf8mb4`; la búsqueda de inicio de sesión detecta las columnas opcionales disponibles en `empleado`.
- Conectividad de red para cargar Bootstrap 5.3.3 desde jsDelivr.
