<?php
/**
 * API REST para dispositivos ESP32
 * Sistema de Control de Asistencia
 */

// Headers CORS y JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Device-Token');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Incluir configuración
require_once __DIR__ . '/../config/bootstrap.php';

// Incluir modelos necesarios
use App\Models\Database;
use App\Models\RegistroAsistencia;
use App\Models\TarjetaRFID;
use App\Models\Dispositivo;

class ApiController {
    private $db;
    private $dispositivo;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Procesar request de la API
     */
    public function procesarRequest() {
        try {
            // Obtener método y endpoint
            $method = $_SERVER['REQUEST_METHOD'];
            $endpoint = $this->getEndpoint();
            
            // Validar autenticación para endpoints protegidos
            if (!$this->validarAutenticacion($endpoint)) {
                return $this->respuestaError('Token de dispositivo inválido o faltante', 401);
            }
            
            // Enrutar según endpoint
            switch ($endpoint) {
                case 'ping':
                    return $this->ping();
                    
                case 'asistencia':
                    if ($method === 'POST') {
                        return $this->registrarAsistencia();
                    }
                    break;
                    
                case 'configuracion':
                    return $this->obtenerConfiguracion();
                    
                case 'sincronizar':
                    return $this->sincronizarDatos();
                    
                case 'estado':
                    if ($method === 'POST') {
                        return $this->actualizarEstadoDispositivo();
                    }
                    break;
                    
                default:
                    return $this->respuestaError('Endpoint no encontrado', 404);
            }
            
        } catch (Exception $e) {
            return $this->respuestaError('Error interno del servidor: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Obtener endpoint de la URL
     */
    private function getEndpoint() {
        $uri = $_SERVER['REQUEST_URI'];
        $path = parse_url($uri, PHP_URL_PATH);
        
        // Remover /api/ del path
        $endpoint = str_replace('/api/', '', $path);
        $endpoint = str_replace('/ControlDeAsistencia/api/', '', $endpoint);
        $endpoint = trim($endpoint, '/');
        
        return $endpoint ?: 'ping';
    }
    
    /**
     * Validar token de autenticación del dispositivo
     */
    private function validarAutenticacion($endpoint) {
        // Endpoint ping no requiere autenticación
        if ($endpoint === 'ping') {
            return true;
        }
        
        // Obtener token del header
        $token = $this->getTokenFromHeader();
        
        if (!$token) {
            return false;
        }
        
        // Validar token en base de datos
        $dispositivoModel = new Dispositivo();
        $this->dispositivo = $dispositivoModel->obtenerPorToken($token);
        
        if (!$this->dispositivo || !$this->dispositivo['activo']) {
            return false;
        }
        
        // Actualizar última conexión
        $dispositivoModel->actualizarUltimaConexion($this->dispositivo['id']);
        
        return true;
    }
    
    /**
     * Obtener token del header Authorization
     */
    private function getTokenFromHeader() {
        // Buscar en varios headers posibles
        $headers = [
            'Authorization',
            'X-Device-Token',
            'X-API-Token'
        ];
        
        foreach ($headers as $header) {
            if (isset($_SERVER['HTTP_' . strtoupper(str_replace('-', '_', $header))])) {
                $value = $_SERVER['HTTP_' . strtoupper(str_replace('-', '_', $header))];
                
                // Si es Authorization, extraer el token después de "Bearer "
                if ($header === 'Authorization' && strpos($value, 'Bearer ') === 0) {
                    return substr($value, 7);
                }
                
                return $value;
            }
        }
        
        return null;
    }
    
    /**
     * Endpoint de ping - verificar conectividad
     */
    private function ping() {
        return $this->respuestaExitosa([
            'mensaje' => 'API funcionando correctamente',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0',
            'servidor' => $_SERVER['SERVER_NAME'] ?? 'localhost'
        ]);
    }
    
    /**
     * Endpoint para registrar asistencia
     */
    private function registrarAsistencia() {
        try {
            // Obtener datos del POST
            $input = file_get_contents('php://input');
            $datos = json_decode($input, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->respuestaError('JSON inválido', 400);
            }
            
            // Validar campos requeridos
            if (!isset($datos['uid_tarjeta']) || empty($datos['uid_tarjeta'])) {
                return $this->respuestaError('UID de tarjeta requerido', 400);
            }
            
            // Agregar información del dispositivo
            $datos['dispositivo_id'] = $this->dispositivo['id'];
            $datos['ip_dispositivo'] = $_SERVER['REMOTE_ADDR'];
            $datos['ubicacion'] = $this->dispositivo['ubicacion'];
            
            // Si no se proporciona timestamp, usar actual
            if (!isset($datos['fecha_hora'])) {
                $datos['fecha_hora'] = date('Y-m-d H:i:s');
            }
            
            // Registrar asistencia
            $registroModel = new RegistroAsistencia();
            $resultado = $registroModel->registrarMarcacion($datos);
            
            if ($resultado['success']) {
                // Respuesta exitosa con información para el ESP32
                return $this->respuestaExitosa([
                    'mensaje' => $resultado['mensaje'],
                    'registro_id' => $resultado['registro_id'],
                    'tipo' => $resultado['tipo'],
                    'usuario' => $resultado['usuario'],
                    'fecha_hora' => $resultado['fecha_hora'],
                    'fuera_horario' => $resultado['fuera_horario'],
                    'sonido' => $resultado['fuera_horario'] ? 'warning' : 'success', // Para ESP32
                    'led' => $resultado['fuera_horario'] ? 'amarillo' : 'verde'
                ]);
            } else {
                return $this->respuestaError($resultado['error'], 400, [
                    'codigo_error' => $resultado['codigo_error'],
                    'sonido' => 'error',
                    'led' => 'rojo'
                ]);
            }
            
        } catch (Exception $e) {
            return $this->respuestaError('Error procesando asistencia: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Obtener configuración del dispositivo
     */
    private function obtenerConfiguracion() {
        try {
            $config = [
                'dispositivo' => [
                    'id' => $this->dispositivo['id'],
                    'nombre' => $this->dispositivo['nombre'],
                    'ubicacion' => $this->dispositivo['ubicacion'],
                    'configuracion' => json_decode($this->dispositivo['configuracion'], true)
                ],
                'sistema' => [
                    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'America/Mexico_City',
                    'fecha_servidor' => date('Y-m-d H:i:s'),
                    'intervalo_ping' => 300, // 5 minutos
                    'timeout_lectura' => 30
                ],
                'validaciones' => [
                    'min_tiempo_entre_marcaciones' => 300, // 5 minutos
                    'max_intentos_tarjeta_invalida' => 3,
                    'reintentos_conexion' => 3
                ]
            ];
            
            return $this->respuestaExitosa($config);
            
        } catch (Exception $e) {
            return $this->respuestaError('Error obteniendo configuración: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Sincronizar datos del dispositivo
     */
    private function sincronizarDatos() {
        try {
            // Obtener tarjetas activas
            $tarjetaModel = new TarjetaRFID();
            $tarjetasActivas = $tarjetaModel->obtenerTodas(['estado' => 'activa', 'asignada' => 'si']);
            
            // Preparar lista de UIDs válidos
            $uidsValidos = [];
            foreach ($tarjetasActivas as $tarjeta) {
                $uidsValidos[] = [
                    'uid' => $tarjeta['uid_tarjeta'],
                    'usuario' => $tarjeta['usuario_nombre'],
                    'numero_empleado' => $tarjeta['numero_empleado']
                ];
            }
            
            $sincronizacion = [
                'tarjetas_activas' => $uidsValidos,
                'total_tarjetas' => count($uidsValidos),
                'ultima_sincronizacion' => date('Y-m-d H:i:s'),
                'version_datos' => md5(json_encode($uidsValidos))
            ];
            
            return $this->respuestaExitosa($sincronizacion);
            
        } catch (Exception $e) {
            return $this->respuestaError('Error en sincronización: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Actualizar estado del dispositivo
     */
    private function actualizarEstadoDispositivo() {
        try {
            $input = file_get_contents('php://input');
            $datos = json_decode($input, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->respuestaError('JSON inválido', 400);
            }
            
            // Actualizar estado del dispositivo
            $estadoDispositivo = [
                'estado' => $datos['estado'] ?? 'online',
                'version_firmware' => $datos['version_firmware'] ?? null,
                'memoria_libre' => $datos['memoria_libre'] ?? null,
                'señal_wifi' => $datos['señal_wifi'] ?? null,
                'temperatura' => $datos['temperatura'] ?? null,
                'uptime' => $datos['uptime'] ?? null,
                'ultima_conexion' => date('Y-m-d H:i:s')
            ];
            
            $dispositivoModel = new Dispositivo();
            $dispositivoModel->actualizarEstado($this->dispositivo['id'], $estadoDispositivo);
            
            return $this->respuestaExitosa([
                'mensaje' => 'Estado actualizado correctamente',
                'dispositivo_id' => $this->dispositivo['id']
            ]);
            
        } catch (Exception $e) {
            return $this->respuestaError('Error actualizando estado: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Respuesta exitosa
     */
    private function respuestaExitosa($data, $codigo = 200) {
        http_response_code($codigo);
        return json_encode([
            'success' => true,
            'data' => $data,
            'timestamp' => date('c')
        ], JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Respuesta de error
     */
    private function respuestaError($mensaje, $codigo = 400, $extra = []) {
        http_response_code($codigo);
        $response = [
            'success' => false,
            'error' => $mensaje,
            'codigo' => $codigo,
            'timestamp' => date('c')
        ];
        
        if (!empty($extra)) {
            $response = array_merge($response, $extra);
        }
        
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }
}

// Procesar request
try {
    $api = new ApiController();
    echo $api->procesarRequest();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error fatal del servidor',
        'timestamp' => date('c')
    ], JSON_UNESCAPED_UNICODE);
}