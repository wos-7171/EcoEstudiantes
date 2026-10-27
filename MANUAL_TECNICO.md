# Manual tecnico - EcoEstudiantes Digitales

Aplicacion web para gestionar, seguir y evaluar Proyectos Ambientales Escolares (PRAE). Institucion Educativa Republica de Uruguay. Programa Tecnico Auxiliar en Desarrollo de Software, PIA 2026.

## Stack

- PHP puro, sin framework, con mysqli y consultas preparadas.
- MySQL o MariaDB en XAMPP. Base `ecoestudiantes`.
- HTML y CSS propios. JavaScript propio. Graficos con Chart.js por CDN.
- Contrasenas con `password_hash()` y `password_verify()`.

## Como se ejecuta

1. Encender Apache y MySQL en XAMPP.
2. La carpeta debe estar en `C:\xampp\htdocs\EcoEstudiantes`.
3. Crear la base `ecoestudiantes` e importar `base_de_datos/esquema.sql` y luego `base_de_datos/datos_iniciales.sql`.
4. Abrir `http://localhost/EcoEstudiantes/`.

La conexion se configura en `config/conexion.php`, que lee `config/config.local.php` o variables de entorno (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`, `DB_PORT`). Si no existe configuracion local, usa `localhost / root / sin contrasena`, valido solo para XAMPP. En produccion nunca se muestran los errores de MySQL al visitante.

No importar `datos_iniciales.sql` sobre una base que ya tiene usuarios: el archivo contiene el administrador inicial y puede entrar en conflicto con las claves primarias.

## Roles

- Administrador: usuarios, instituciones, noticias, mensajes, proyectos y reportes.
- Docente: proyectos de los que es responsable, actividades, evaluacion y reportes.
- Estudiante: inscripcion, evidencias y consulta de su nota.

La sesion guarda `id_usuario`, `nombre`, `rol` y `correo`. Las pantallas y las acciones cargan `includes/bootstrap.php`, que inicia la sesion de forma segura, sincroniza el rol con la base de datos y comprueba que la cuenta siga activa. Los formularios POST incluyen un token CSRF y cada accion lo valida antes de escribir.

## Modulos

- `index.php` y `login.php`: ingreso y registro.
- `proyectos.php`: crear y consultar PRAE.
- `actividades.php`: actividades de un proyecto.
- `participantes.php`: inscripcion y nota de 1.0 a 5.0.
- `subir_evidencia.php`: archivos del estudiante. Se guardan en `uploads/evidencias/` con nombre aleatorio.
- `reportes.php`: estados, participacion, promedio y graficas.
- `admin.php`: usuarios, noticias e indicadores.
- `mensajes.php` y `mis_mensajes.php`: contacto y respuesta del administrador.
- `manual.php`: manual de usuario segun el rol.
- `pages/`: normativa, fundamentos, noticias, galeria y contacto.

## Tablas principales

`roles`, `usuarios`, `instituciones`, `proyectos_prae`, `actividades`, `participantes`, `evidencias`, `noticias`, `noticias_imagenes`, `mensajes_contacto`, `intentos_login`.

La nota esta en `participantes.nota`. El archivo de una evidencia se guarda solo con el nombre. La respuesta de contacto esta en `mensajes_contacto.respuesta`. `intentos_login` la crea la propia aplicacion para limitar los intentos fallidos de inicio de sesion.

## Seguridad aplicada

- Consultas preparadas en lecturas con filtros y en todas las escrituras.
- Salida escapada con `htmlspecialchars()`.
- Contrasenas con bcrypt; el hash se actualiza automaticamente si cambia el algoritmo.
- Tokens CSRF en los 31 formularios POST y validacion en las 9 acciones.
- Sesion con cookies `HttpOnly`, `SameSite=Lax` y `Secure` bajo HTTPS; regeneracion de ID al iniciar sesion e inactividad de 60 minutos.
- Verificacion de usuario, estado y rol contra la base de datos en cada accion (`requiere_usuario()`).
- Limite de 5 intentos de login por correo y 20 por IP cada 15 minutos.
- Mensajes de error genericos para no revelar si un correo existe.
- Cierre de sesion solo por POST con CSRF.
- Cabeceras de seguridad: CSP, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy` y `Permissions-Policy`.
- Los errores de PHP/MySQL se registran, pero no se muestran al visitante.
- Archivos subidos con extension permitida, tamano maximo y nombre aleatorio.
- `uploads/evidencias/.htaccess` e `images/noticias/.htaccess` bloquean la ejecucion de scripts.
- `.htaccess` raiz bloquea `.git`, SQL, documentacion, backups y carpetas internas.
- No se usa `DROP` en el codigo de la aplicacion.

## Despliegue

La guia completa esta en `DESPLIEGUE.md`. Para generar el paquete sin datos sensibles:

```bash
python3 scripts/empaquetar.py
```

Para comprobar el entorno:

```bash
php scripts/verificar_entorno.php
```
