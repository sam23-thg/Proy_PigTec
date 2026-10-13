<?php
// Activa la comprobación estricta de tipos para las reglas compartidas de formularios.
declare(strict_types=1); // Evita conversiones implícitas en la validación de entradas.

// Agrupa las reglas de validación reutilizadas por los controladores MVC.
final class ValidacionFormulario // Centraliza validaciones del servidor que también se declaran en las vistas.
{
    // Acepta entradas de texto solo cuando POST realmente contiene una cadena escalar.
    public static function texto(mixed $valor): string // Evita avisos de PHP si una solicitud envía arreglos.
    {
        // Devuelve una cadena vacía para cualquier entrada que no sea texto.
        return is_string($valor) ? $valor : ''; // Deja que las reglas del campo rechacen el valor ausente.
    }

    // Valida nombres personales con letras Unicode y separadores habituales.
    public static function nombrePersona(string $valor, bool $obligatorio = true): bool // Permite dejar vacíos solo los nombres opcionales.
    {
        // Acepta únicamente espacios vacíos si el dato no es obligatorio.
        if ($valor === '') { // Evita que un valor vacío pase para un campo requerido.
            return !$obligatorio; // Conserva la opcionalidad definida por el formulario.
        }

        // Comprueba longitud de columna, caracteres de persona y ausencia de controles.
        return self::longitudEntre($valor, 1, 100) && preg_match('/^[\p{L}\p{M} .’\'-]+$/uD', $valor) === 1; // Rechaza números y símbolos ajenos a nombres.
    }

    // Valida la estructura de 18 caracteres de una CURP en mayúsculas.
    public static function curp(string $valor): bool // Requiere longitud exacta y estructura alfanumérica oficial.
    {
        // Comprueba los segmentos básicos de letras, fecha, sexo y homoclave.
        return preg_match('/^[A-Z][AEIOUX][A-Z]{2}[0-9]{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9][0-9]$/D', $valor) === 1; // Limita la CURP exactamente a 18 caracteres.
    }

    // Valida teléfonos de contacto conservados como texto para mantener ceros iniciales.
    public static function telefono(string $valor): bool // Acepta de 10 a 15 dígitos sin signos ni separadores.
    {
        // Exige exclusivamente dígitos dentro del tamaño permitido para la columna.
        return preg_match('/^[0-9]{10,15}$/D', $valor) === 1; // Rechaza letras, negativos, espacios y extensiones.
    }

    // Valida importes y cantidades con precisión decimal máxima.
    public static function decimal(string $valor, int $decimales, float $minimo): bool // Rechaza negativos y notación científica.
    {
        // Construye una regla que admite enteros y decimales con precisión controlada.
        $patron = '/^[0-9]+(?:\.[0-9]{1,' . $decimales . '})?$/D'; // Exige punto decimal y dígitos sin agrupadores locales.
        // Comprueba formato estricto, rango finito y mínimo permitido.
        return preg_match($patron, $valor) === 1 && is_finite((float) $valor) && (float) $valor >= $minimo; // Evita negativos, infinitos y valores menores al mínimo.
    }

    // Valida una cadena de longitud Unicode dentro de límites inclusivos.
    public static function longitudEntre(string $valor, int $minimo, int $maximo): bool // Cuenta caracteres y no bytes multibyte.
    {
        // Cuenta los caracteres UTF-8 y rechaza secuencias inválidas.
        $cantidadCaracteres = preg_match_all('/./us', $valor, $coincidencias); // Usa una expresión compatible con versiones sin mbstring.
        // Informa que la cadena es válida únicamente dentro del intervalo pedido.
        return $cantidadCaracteres !== false && $cantidadCaracteres >= $minimo && $cantidadCaracteres <= $maximo; // Impide desbordar límites de columnas.
    }

    // Rechaza caracteres de control en campos de texto libre.
    public static function textoLibre(string $valor, int $maximo, bool $obligatorio = false): bool // Se usa para nombres comerciales y observaciones breves.
    {
        // Permite el valor vacío solo cuando el campo puede omitirse.
        if ($valor === '') { // Conserva la distinción entre campos requeridos y opcionales.
            return !$obligatorio; // No exige contenido para un campo opcional.
        }

        // Comprueba que el texto tenga longitud válida y no incluya controles Unicode.
        return self::longitudEntre($valor, 1, $maximo) && preg_match('/\p{C}/u', $valor) !== 1; // Conserva números y puntuación útiles en nombres comerciales.
    }
}
