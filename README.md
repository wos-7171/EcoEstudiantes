# EcoEstudiantes Digitales

Plataforma para gestionar, hacer seguimiento y evaluar **Proyectos Ambientales Escolares (PRAE)**. Tiene tres roles: **Administrador**, **Docente** y **Estudiante**.

## Tecnologías

- PHP 7.4+ (recomendado 8.0) con `mysqli` y consultas preparadas.
- MySQL 5.7+ o MariaDB 10.3+.
- HTML, CSS y JavaScript propios; Chart.js por CDN para los reportes.
- Contraseñas con `password_hash()` / `password_verify()` (bcrypt).
- Sin Composer, sin npm y sin proceso de compilación.

## Requisitos

- PHP con las extensiones `mysqli`, `mbstring`, `json` y `openssl`.
- Apache con `.htaccess` (XAMPP o hosting compartido).
- Permisos de escritura en `uploads/evidencias/`, `images/noticias/`, `images/galeria/` y `evidencias/`.

## Instalación local (XAMPP)

1. Inicia Apache y MySQL desde el panel de XAMPP.
2. Clona el proyecto en `C:\xampp\htdocs\EcoEstudiantes`.
3. En phpMyAdmin crea una base de datos vacía llamada `ecoestudiantes`.
4. Importa, en este orden:
   - `base_de_datos/esquema.sql` (solo estructura, sin datos personales).
   - `base_de_datos/datos_iniciales.sql` (roles, institución y administrador inicial).
5. Abre `http://localhost/EcoEstudiantes/`.

Si tu MySQL no usa `root` sin contraseña, copia `config/config.local.example.php` y ajusta los datos: en el PC usa `config/config.local.dev.php` (tiene prioridad); en el hosting usa `config/config.local.php`. Ambos están ignorados por Git y excluidos del paquete.

### Acceso inicial

| Campo | Valor |
| --- | --- |
| Correo | `admin@ecoestudiantes.com` |
| Contraseña | `EcoAdmin2026` |

**Cambia la contraseña y el correo inmediatamente** desde *Mi Perfil* después del primer ingreso.

## Estructura

- `index.php`: inicio de sesión y registro.
- `login.php`, `registro_usuario.php`, `logout.php`: autenticación.
- `includes/bootstrap.php`: arranque común (sesión, cabeceras, conexión, permisos y CSRF).
- `includes/funciones.php`: utilidades de sesión, CSRF, validaciones y borrados.
- `config/conexion.php`: conexión configurable por entorno.
- `base_de_datos/esquema.sql` y `datos_iniciales.sql`: instalación limpia.
- `pages/`: páginas informativas, noticias, galería y contacto.
- `assets/`, `images/`: estilos, scripts e imágenes públicas.
- `uploads/evidencias/`: evidencias subidas por estudiantes (sin ejecución de scripts).
- `scripts/`: verificación del entorno, hash de contraseñas y empaquetado.

## Seguridad

- Todos los formularios POST llevan token **CSRF** y las acciones lo validan.
- Las acciones comprueban el usuario, su estado y su rol **contra la base de datos**, no solo contra la sesión.
- Cookies de sesión `HttpOnly`, `SameSite=Lax` y `Secure` cuando hay HTTPS.
- Cabeceras `Content-Security-Policy`, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy` y `Permissions-Policy`.
- Límite de intentos de inicio de sesión: **5 por correo y 20 por IP** cada 15 minutos.
- Los errores de PHP y MySQL no se muestran al visitante.
- `.htaccess` bloquea `.git`, SQL, documentación, backups y carpetas internas.

## Despliegue

Consulta **[DESPLIEGUE.md](DESPLIEGUE.md)** para publicar en InfinityFree o en otro hosting compartido, generar el paquete ZIP y resolver el error 500.

Resumen:

```bash
python3 scripts/empaquetar.py
php scripts/verificar_entorno.php
```

## Comandos útiles

```bash
# Comprobar PHP, extensiones, conexión, tablas y permisos
php scripts/verificar_entorno.php

# Generar un hash bcrypt para restablecer una contraseña
php scripts/crear_hash.php "NuevaClave123"

# Crear el ZIP de despliegue en dist/
python3 scripts/empaquetar.py
```

## Desarrollo

Después de actualizar el código:

```bash
git pull
```

Recarga el navegador con `Ctrl+F5`. No publiques `config/config.local.php`, `backups/` ni volcados SQL con datos reales.

## Licencia y uso

Proyecto académico de la Institución Educativa República de Uruguay, Programa Técnico Auxiliar en Desarrollo de Software, PIA 2026.
