<?php
// Redirigir el flujo del navegador de forma automática hacia el Front Controller público
header('Location: public/assets/index.php');
// Detener la ejecución del script actual para asegurar la redirección limpia
exit();