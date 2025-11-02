<?php
/**
 * Clase de utilidades para respuestas HTTP/JSON
 * Sistema de Control de Asistencia
 */

namespace App\Utils;

class Response {
    
    /**
     * Respuesta de éxito
     */
    public static function exito($mensaje, $datos = null, $codigo = 200) {
        http_response_code($codigo);
        
        $response = [
            'success' => true,
            'message' => $mensaje
        ];
        
        if ($datos !== null) {
            $response['data'] = $datos;
        }
        
        header('Content-Type: application/json');
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Respuesta de error
     */
    public static function error($mensaje, $datos = null, $codigo = 400) {
        http_response_code($codigo);
        
        $response = [
            'success' => false,
            'error' => $mensaje
        ];
        
        if ($datos !== null) {
            $response['data'] = $datos;
        }
        
        header('Content-Type: application/json');
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Respuesta con paginación
     */
    public static function paginado($datos, $totalRegistros, $paginaActual, $porPagina, $mensaje = 'Datos obtenidos') {
        $totalPaginas = ceil($totalRegistros / $porPagina);
        
        $response = [
            'success' => true,
            'message' => $mensaje,
            'data' => $datos,
            'pagination' => [
                'current_page' => $paginaActual,
                'per_page' => $porPagina,
                'total_records' => $totalRegistros,
                'total_pages' => $totalPaginas,
                'has_next' => $paginaActual < $totalPaginas,
                'has_previous' => $paginaActual > 1
            ]
        ];
        
        header('Content-Type: application/json');
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Redirigir
     */
    public static function redirigir($url, $mensaje = null) {
        if ($mensaje) {
            $url .= (strpos($url, '?') !== false ? '&' : '?') . 'mensaje=' . urlencode($mensaje);
        }
        
        header("Location: {$url}");
        exit;
    }
    
    /**
     * Respuesta 404
     */
    public static function noEncontrado($mensaje = 'Recurso no encontrado') {
        self::error($mensaje, null, 404);
    }
    
    /**
     * Respuesta 401
     */
    public static function noAutorizado($mensaje = 'No autorizado') {
        self::error($mensaje, null, 401);
    }
    
    /**
     * Respuesta 403
     */
    public static function prohibido($mensaje = 'Acceso prohibido') {
        self::error($mensaje, null, 403);
    }
    
    /**
     * Respuesta 500
     */
    public static function errorServidor($mensaje = 'Error interno del servidor') {
        self::error($mensaje, null, 500);
    }
}