<?php
/**
 * Help Desk LAN - Authentication & LDAP Connector
 */

namespace App\Core;

use App\Helpers\Logger;
use App\Helpers\Auditor;
use Exception;
use PDO;

class Auth {
    /**
     * Attempts login via Local DB or LDAP (Active Directory).
     */
    public static function login(string $username, string $password): array {
        $db = Database::getConnection();
        
        // 1. Fetch system LDAP configuration
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'ldap_%'");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $ldapEnabled = isset($settings['ldap_enabled']) && $settings['ldap_enabled'] === '1';

        if ($ldapEnabled) {
            Logger::info("Intento de login LDAP para el usuario: {$username}");
            $ldapResult = self::authenticateLDAP($username, $password, $settings);
            
            if ($ldapResult['status']) {
                // Synced user in local database
                $user = self::syncLDAPUser($ldapResult['user_data']);
                if ($user && $user['status'] === 'active') {
                    Auditor::log($user['id'], 'LOGIN_LDAP', "Inicio de sesión LDAP exitoso desde IP");
                    return ['status' => true, 'user' => $user];
                }
                return ['status' => false, 'error' => 'Usuario LDAP desactivado en la Mesa de Ayuda.'];
            }
            // If LDAP fails, we can optionally fall back to Local DB for Admin accounts
            Logger::info("LDAP falló. Intentando base de datos local para el usuario: {$username}");
        }

        // 2. Standard Database Login
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] !== 'active') {
                return ['status' => false, 'error' => 'Su usuario se encuentra desactivado. Contacte a soporte.'];
            }
            Auditor::log($user['id'], 'LOGIN_LOCAL', "Inicio de sesión local exitoso");
            return ['status' => true, 'user' => $user];
        }

        return ['status' => false, 'error' => 'Usuario o contraseña incorrectos.'];
    }

    /**
     * Authenticate via LDAP Active Directory
     */
    private static function authenticateLDAP(string $username, string $password, array $config): array {
        if (!function_exists('ldap_connect')) {
            Logger::error("La extensión PHP LDAP no está instalada en este servidor.");
            return ['status' => false, 'error' => 'Servicio LDAP no disponible en el servidor web.'];
        }

        $host = $config['ldap_host'] ?? '';
        $port = (int)($config['ldap_port'] ?? 389);
        $dnPattern = $config['ldap_dn'] ?? ''; // e.g. "uid={username},ou=users,dc=empresa,dc=lan" or "DOMAIN\\{username}"
        $searchBase = $config['ldap_search_base'] ?? '';

        if (empty($host) || empty($dnPattern)) {
            Logger::error("Configuración de LDAP incompleta en los ajustes.");
            return ['status' => false, 'error' => 'Configuración LDAP incompleta.'];
        }

        // Bind user DN representation
        $userBindDN = str_replace('{username}', $username, $dnPattern);

        $ldapConn = ldap_connect($host, $port);
        if (!$ldapConn) {
            return ['status' => false, 'error' => 'No se pudo conectar al servidor LDAP.'];
        }

        // Set protocols
        ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

        try {
            // Attempt bind
            $bind = @ldap_bind($ldapConn, $userBindDN, $password);
            if ($bind) {
                // Fetch user attributes to sync them
                $filter = "(samaccountname={$username})";
                if (empty($searchBase)) {
                    // Try to guess filter or use dn
                    $filter = "(uid={$username})";
                }
                
                $search = @ldap_search($ldapConn, $searchBase ?: $userBindDN, $filter);
                $info = [];
                if ($search) {
                    $entries = ldap_get_entries($ldapConn, $search);
                    if ($entries && $entries['count'] > 0) {
                        $entry = $entries[0];
                        $info = [
                            'username' => $username,
                            'email' => $entry['mail'][0] ?? "{$username}@empresa.lan",
                            'first_name' => $entry['givenname'][0] ?? $username,
                            'last_name' => $entry['sn'][0] ?? 'LDAP',
                            'phone' => $entry['telephonenumber'][0] ?? null,
                            'position' => $entry['title'][0] ?? 'Empleado LDAP'
                        ];
                    }
                }

                if (empty($info)) {
                    // Fallback basic details if search fails but bind succeeded
                    $info = [
                        'username' => $username,
                        'email' => "{$username}@empresa.lan",
                        'first_name' => $username,
                        'last_name' => 'LDAP',
                        'phone' => null,
                        'position' => 'Empleado LDAP'
                    ];
                }

                @ldap_close($ldapConn);
                return ['status' => true, 'user_data' => $info];
            }
        } catch (Exception $e) {
            Logger::error("Error durante enlace/búsqueda LDAP: " . $e->getMessage());
        }

        @ldap_close($ldapConn);
        return ['status' => false, 'error' => 'Credenciales LDAP incorrectas o servidor inaccesible.'];
    }

    /**
     * Create or update the LDAP user local profile record.
     */
    private static function syncLDAPUser(array $data): ?array {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$data['username']]);
            $user = $stmt->fetch();

            if ($user) {
                // Update LDAP details
                $update = $db->prepare("UPDATE users SET email = ?, first_name = ?, last_name = ?, phone = ?, position = ? WHERE id = ?");
                $update->execute([
                    $data['email'],
                    $data['first_name'],
                    $data['last_name'],
                    $data['phone'] ?: $user['phone'],
                    $data['position'] ?: $user['position'],
                    $user['id']
                ]);
                $user['email'] = $data['email'];
                $user['first_name'] = $data['first_name'];
                $user['last_name'] = $data['last_name'];
                return $user;
            } else {
                // Insert new LDAP user (role defaults to standard 'user')
                $insert = $db->prepare("INSERT INTO users (username, password, email, first_name, last_name, role, phone, position, status) VALUES (?, ?, ?, ?, ?, 'user', ?, ?, 'active')");
                $dummyPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);
                $insert->execute([
                    $data['username'],
                    $dummyPassword,
                    $data['email'],
                    $data['first_name'],
                    $data['last_name'],
                    $data['phone'],
                    $data['position']
                ]);
                
                $id = $db->lastInsertId();
                $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$id]);
                return $stmt->fetch();
            }
        } catch (Exception $e) {
            Logger::error("Error al sincronizar usuario LDAP: " . $e->getMessage());
            return null;
        }
    }
}
