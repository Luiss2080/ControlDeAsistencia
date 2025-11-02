<?php
/**
 * Clase de utilidades para validación de datos
 * Sistema de Control de Asistencia
 */

namespace App\Utils;

class Validator {
    private $datos;
    private $errores = [];
    
    public function __construct($datos = []) {
        $this->datos = $datos;
    }
    
    /**
     * Campos requeridos
     */
    public function requerido($campos) {
        if (is_string($campos)) {
            $campos = [$campos];
        }
        
        foreach ($campos as $campo) {
            if (!isset($this->datos[$campo]) || empty(trim($this->datos[$campo]))) {
                $this->errores[$campo][] = "El campo {$campo} es requerido";
            }
        }
        
        return $this;
    }
    
    /**
     * Validar email
     */
    public function email($campo) {
        if (isset($this->datos[$campo]) && !empty($this->datos[$campo])) {
            if (!filter_var($this->datos[$campo], FILTER_VALIDATE_EMAIL)) {
                $this->errores[$campo][] = "El campo {$campo} debe ser un email válido";
            }
        }
        
        return $this;
    }
    
    /**
     * Longitud mínima
     */
    public function minLength($campo, $longitud) {
        if (isset($this->datos[$campo]) && strlen($this->datos[$campo]) < $longitud) {
            $this->errores[$campo][] = "El campo {$campo} debe tener al menos {$longitud} caracteres";
        }
        
        return $this;
    }
    
    /**
     * Longitud máxima
     */
    public function maxLength($campo, $longitud) {
        if (isset($this->datos[$campo]) && strlen($this->datos[$campo]) > $longitud) {
            $this->errores[$campo][] = "El campo {$campo} no puede tener más de {$longitud} caracteres";
        }
        
        return $this;
    }
    
    /**
     * Validar número
     */
    public function numeric($campo) {
        if (isset($this->datos[$campo]) && !is_numeric($this->datos[$campo])) {
            $this->errores[$campo][] = "El campo {$campo} debe ser numérico";
        }
        
        return $this;
    }
    
    /**
     * Validar fecha
     */
    public function fecha($campo, $formato = 'Y-m-d') {
        if (isset($this->datos[$campo]) && !empty($this->datos[$campo])) {
            $fecha = \DateTime::createFromFormat($formato, $this->datos[$campo]);
            if (!$fecha || $fecha->format($formato) !== $this->datos[$campo]) {
                $this->errores[$campo][] = "El campo {$campo} debe ser una fecha válida";
            }
        }
        
        return $this;
    }
    
    /**
     * Confirmar que dos campos coinciden
     */
    public function coincide($campo1, $campo2) {
        if (isset($this->datos[$campo1]) && isset($this->datos[$campo2])) {
            if ($this->datos[$campo1] !== $this->datos[$campo2]) {
                $this->errores[$campo2][] = "Los campos {$campo1} y {$campo2} deben coincidir";
            }
        }
        
        return $this;
    }
    
    /**
     * Verificar si hay errores
     */
    public function tieneErrores() {
        return !empty($this->errores);
    }
    
    /**
     * Obtener errores
     */
    public function getErrores() {
        return $this->errores;
    }
    
    /**
     * Obtener primer error de un campo
     */
    public function getPrimerError($campo) {
        return isset($this->errores[$campo]) ? $this->errores[$campo][0] : null;
    }
}