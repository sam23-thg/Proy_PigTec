# Sistema El Puerquito Digital

Aplicación web MVC para la gestión del sistema de la tocinería El Puerquito Digital.

## Módulo de autenticación

- El punto de entrada web es `public/index.php`.
- El formulario de acceso utiliza Bootstrap 5.3 y se encuentra en `app/views/auth/login.php`.
- Las credenciales se verifican con `password_verify()`; las cuentas inactivas no pueden iniciar sesión.
- Las cuentas con contraseña temporal deben completar el cambio en `app/views/auth/cambiar_temporal.php` antes de continuar.
- Los recursos propios de la interfaz se sirven desde `public/assets/`.

## Requisitos de entorno

- PHP 8 o posterior con PDO MySQL habilitado.
- MariaDB o MySQL con la base de datos `elpuerquitodigital` existente.
- Parámetros locales de conexión definidos en el archivo `.env` de la raíz; no se incluyen credenciales de prueba en este repositorio.
- Conectividad de red para cargar Bootstrap 5.3.3 desde jsDelivr.

La conexión de este entorno no pudo probarse contra la base de datos debido a una incompatibilidad del método de autenticación del servidor con el controlador PDO instalado.