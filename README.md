# Mesa de Ayuda LAN - Sistema de Soporte TI Corporativo

Un sistema web moderno y profesional de Mesa de Ayuda (Help Desk) diseñado desde cero para operar exclusivamente en entornos de **Red LAN Corporativa** de alta disponibilidad sin requerir conexión a internet.

---

## 🚀 Características del Sistema

- **Arquitectura MVC Pura**: Desarrollado con PHP 8.3 orientado a objetos sin dependencias de frameworks externos innecesarios.
- **Instalador Web Automatizado**: Realiza un diagnóstico del entorno (PHP, extensiones, permisos) y configura la base de datos MariaDB y la cuenta administradora inicial.
- **Diseño Premium Responsive**: Basado en Bootstrap 5 con soporte integrado para **Modo Oscuro / Modo Claro** mediante almacenamiento local, animaciones fluidas y micro-interacciones.
- **Dashboard Analítico**: Métricas de rendimiento, tiempos de respuesta, tiempos de solución y gráficos interactivos mediante **Chart.js** y tablas ordenables con **DataTables**.
- **Roles y Permisos**: Administrador, Técnico de Sistemas y Usuario Final con flujos de asignación y notificaciones.
- **Canal de Comentarios Tipo Chat**: Intercambio de mensajes en tiempo real con avatares de perfil, marcas de rol e importación de adjuntos (PDF, Word, Excel, PNG, ZIP, etc.).
- **Módulo de Reportes**: Filtrado avanzado por fechas, departamentos, técnicos o categorías con exportaciones nativas en formatos **CSV**, **Excel** e **Impresión Limpia/PDF**.
- **Seguridad LAN Corporativa**:
  - Sentencias preparadas nativas contra Inyección SQL mediante PDO.
  - Prevención de XSS y CSRF mediante tokens de seguridad validados en sesiones.
  - Almacenamiento seguro de contraseñas mediante encriptación `BCRYPT`.
  - Integración nativa de inicio de sesión con **Active Directory mediante protocolo LDAP**.
  - Auditoría de bitácora detallada de toda actividad del sistema con captura de IP.

---

## 🛠️ Requisitos del Servidor

- **Sistema Operativo**: Linux (Recomendado: Ubuntu Server 24.04 LTS) o Windows Server.
- **Servidor Web**: Apache2 con el módulo `mod_rewrite` habilitado.
- **Runtime**: PHP 8.3 o superior (Módulos requeridos: `pdo`, `pdo_mysql`, `session`, `ldap` opcional para Active Directory).
- **Base de Datos**: MariaDB 10.6 o superior / MySQL.

---

## 📂 Estructura del Proyecto

```text
/app/               # Núcleo del Framework
  ├── Core/         # Clases base de MVC, Router, Base de datos, Sesiones y Auth
  ├── Helpers/      # Registradores, Loggers, Auditor de base de datos y Uploader
  ├── Models/       # Modelos del Dominio (Tickets, Usuarios, Comentarios, etc.)
  └── Controllers/  # Controladores de Negocio
/config/            # Configuración de base de datos y archivo de esquema SQL
/views/             # Plantillas y layouts HTML/PHP
/public/            # Directorio Público (Raíz del Web Server)
  ├── assets/       # Librerías locales minificadas (CSS/JS/Icons)
  ├── uploads/      # Directorio de archivos y avatares subidos
  ├── install.php   # Asistente de instalación inicial
  └── index.php     # Front Controller / Enrutador
/logs/              # Registros de diagnósticos del sistema
```

---

## 🔧 Instalación y Configuración

### 1. Habilitar mod_rewrite en Apache (Ubuntu Server)
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 2. Configurar Directorio Virtual (VirtualHost)
Asegúrese de apuntar la directiva `DocumentRoot` a la carpeta `/public` del proyecto y habilitar las modificaciones `.htaccess`:
```apache
<VirtualHost *:80>
    ServerName helpdesk
    DocumentRoot /var/www/helpdesk/public

    <Directory /var/www/helpdesk/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/helpdesk_error.log
    CustomLog ${APACHE_LOG_DIR}/helpdesk_access.log combined
</VirtualHost>
```

### 3. Asignar Permisos de Carpeta
El usuario del servidor web (`www-data` en Ubuntu) debe tener permisos de escritura en las siguientes carpetas:
```bash
sudo chown -R www-data:www-data /var/www/helpdesk
sudo chmod -R 775 /var/www/helpdesk/config
sudo chmod -R 775 /var/www/helpdesk/public/uploads
sudo chmod -R 775 /var/www/helpdesk/logs
```

### 4. Ejecutar el Instalador Web
Abra su navegador y acceda a la URL configurada:
`http://helpdesk/install.php` o `http://192.168.x.x/install.php`

Siga los pasos en el asistente para validar requerimientos, ingresar las credenciales de MariaDB y configurar la cuenta de Administrador.

---

## 🔐 Integración con Active Directory (LDAP)

Para habilitar la autenticación de usuarios mediante Active Directory de la empresa, acceda al panel de **Configuración** como administrador:
1. Active la casilla **Habilitar Autenticación LDAP**.
2. Escriba la IP del Servidor Active Directory.
3. Ingrese el patrón DN. Ejemplo:
   - `DOMAIN\\{username}`
   - `uid={username},ou=users,dc=empresa,dc=lan`
4. Al iniciar sesión, el sistema intentará autenticar con Active Directory. Si el enlace es exitoso, sincronizará y creará automáticamente el perfil del usuario de forma local con rol básico.
