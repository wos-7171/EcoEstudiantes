# Guía de despliegue — EcoEstudiantes Digitales

Esta guía deja el proyecto listo para publicarse en **InfinityFree** o en cualquier hosting compartido con Apache, PHP y MySQL/MariaDB. También explica cómo resolver el error **HTTP 500**.

---

## 1. Antes de empezar

### Lo que NO se debe subir

| Archivo o carpeta | Motivo |
| --- | --- |
| `.git/` | Expone todo el historial y la configuración del repositorio. |
| `backups/` | Copias antiguas de código y datos. |
| `config/config.local.php` | Contiene usuario y contraseña de la base de datos. |
| `config/config.local.dev.php` | Credenciales de XAMPP para desarrollo; no debe llegar al hosting. |
| Volcados SQL con datos reales | Contienen correos, nombres y hashes de contraseñas. |
| `uploads/evidencias/*` y `evidencias/*` | Fotos subidas por estudiantes (datos personales). |
| `dist/` | Paquetes generados. |

El script `scripts/empaquetar.py` ya excluye todo esto.

### Lo que sí se sube

- Código PHP, CSS, JavaScript e imágenes públicas.
- `base_de_datos/esquema.sql` y `base_de_datos/datos_iniciales.sql`.
- Los archivos `.htaccess` de seguridad.
- `config/config.local.example.php` (plantilla, sin credenciales reales).

---

## 2. Crear el paquete de despliegue

Desde la raíz del proyecto:

```bash
python3 scripts/empaquetar.py
```

Se genera `dist/ecoestudiantes-AAAAMMDD-HHMMSS.zip`. El script verifica que el ZIP no contenga `.git` ni `config.local.php`.

También puedes comprobar el entorno local antes de empaquetar:

```bash
php scripts/verificar_entorno.php
```

---

## 3. Publicar en InfinityFree

### 3.1. Crear la cuenta y el sitio

1. Crea una cuenta en <https://infinityfree.com>.
2. Crea un sitio con un subdominio gratuito (por ejemplo, `ecoestudiantes.infinityfreeapp.com`).
3. Espera a que el panel termine de aprovisionar el sitio.

### 3.2. Crear la base de datos

1. En el panel, entra en **MySQL Databases**.
2. Crea una base de datos, por ejemplo `ecoestudiantes`.
3. Anota estos datos, que aparecen en el panel:

| Dato | Ejemplo | Dónde se usa |
| --- | --- | --- |
| Host | `sql123.infinityfree.com` | `DB_HOST` |
| Usuario | `if0_12345678` | `DB_USER` |
| Contraseña | la que elegiste | `DB_PASS` |
| Base de datos | `if0_12345678_ecoestudiantes` | `DB_NAME` |

> En hosting compartido **no** se usa `localhost`. Si dejas `localhost`, la conexión falla y la web devuelve error 500.

### 3.3. Subir los archivos

1. Abre **File Manager** o conéctate por FTP.
2. Entra en `htdocs/`.
3. Borra los archivos de ejemplo (`index2.html`, `default.php`, etc.).
4. Sube el ZIP y extráelo.
5. Verifica que `index.php` quede directamente en `htdocs/index.php`, **no** en `htdocs/EcoEstudiantes/index.php`.
6. Si el ZIP creó una carpeta intermedia, mueve su contenido a `htdocs/`.

Estructura esperada:

```text
htdocs/
├── index.php
├── login.php
├── admin.php
├── config/
├── includes/
├── pages/
├── assets/
├── images/
├── uploads/
├── base_de_datos/
├── .htaccess
└── ...
```

### 3.4. Configurar las credenciales

Crea el archivo `htdocs/config/config.local.php` con este contenido (reemplaza los valores):

```php
<?php
return [
    'DB_HOST'   => 'sql123.infinityfree.com',
    'DB_USER'   => 'if0_12345678',
    'DB_PASS'   => 'TU_CONTRASENA',
    'DB_NAME'   => 'if0_12345678_ecoestudiantes',
    'DB_PORT'   => 3306,
    'APP_DEBUG' => false,
];
```

- `config.local.php` está ignorado por Git y bloqueado por `.htaccess`.
- No pongas `APP_DEBUG => true` en producción.
- Si también desarrollas en este equipo, crea `config/config.local.dev.php` con los datos de XAMPP. Ese archivo tiene prioridad local y **no debe subirse** al hosting.

### 3.5. Importar la base de datos

1. En el panel, abre **phpMyAdmin** para tu base.
2. Selecciona la base `if0_12345678_ecoestudiantes`.
3. Ve a **Importar**.
4. Importa primero `base_de_datos/esquema.sql`.
5. Importa después `base_de_datos/datos_iniciales.sql`.

Los archivos **no** incluyen `CREATE DATABASE` ni `USE`, así que funcionan en hosting compartido.

Si phpMyAdmin muestra advertencias sobre `SET FOREIGN_KEY_CHECKS`, normalmente son informativas. Verifica al final que existan las 12 tablas.

> Las evidencias no se incluyen en el ZIP por privacidad. En una instalación nueva se suben desde la web. Si migras una base de datos que ya tiene evidencias, copia también las carpetas `uploads/evidencias/` y `evidencias/` al hosting. Si falta un archivo, la web mostrará **“Archivo no disponible”** en lugar de una imagen rota.

### 3.6. Permisos

- Archivos: `644`.
- Carpetas: `755`.
- Carpetas que deben poder escribir: `uploads/evidencias/`, `images/noticias/`, `images/galeria/` y `evidencias/`.

En InfinityFree, si el File Manager no permite cambiar permisos, usa FTP.

### 3.7. Activar HTTPS

1. En el panel, activa **Free SSL** para el dominio.
2. Espera la emisión del certificado.
3. Entra a `https://tu-dominio` y comprueba que el candado aparece.
4. No fuerces redirecciones desde `.htaccess` hasta confirmar que el certificado funciona; un bucle de redirección también produce error 500.

---

## 4. Primer ingreso y puesta en marcha

| Campo | Valor inicial |
| --- | --- |
| URL | `https://tu-dominio/` |
| Correo | `admin@ecoestudiantes.com` |
| Contraseña | `EcoAdmin2026` |

Pasos obligatorios:

1. Inicia sesión como administrador.
2. Ve a **Mi Perfil** y cambia la contraseña por una propia.
3. Cambia también el correo del administrador.
4. Crea tu institución real en **Instituciones**.
5. Crea las cuentas de docentes desde **Panel Admin → Usuarios**.
6. Prueba los tres roles: administrador, docente y estudiante.
7. Sube una evidencia de prueba y elimínala.

---

## 5. Verificación posterior al despliegue

### 5.1. Pruebas mínimas

- [ ] `https://tu-dominio/` muestra el inicio de sesión.
- [ ] El registro de estudiante funciona.
- [ ] El login de administrador funciona.
- [ ] Se puede crear un proyecto PRAE.
- [ ] Se puede crear una actividad.
- [ ] Un estudiante se inscribe y sube una evidencia.
- [ ] El docente evalúa con nota de 1.0 a 5.0.
- [ ] Los reportes cargan y muestran gráficas.
- [ ] Contacto guarda el mensaje y el administrador puede responderlo.
- [ ] Cerrar sesión funciona (ahora es un formulario POST).
- [ ] `https://tu-dominio/.git/config` devuelve **403** o **404**.
- [ ] `https://tu-dominio/base_de_datos/esquema.sql` devuelve **403**.
- [ ] `https://tu-dominio/config/conexion.php` devuelve **403**.

### 5.2. Revisar el límite de subida

El código permite evidencias de hasta **20 MB**, pero se ajusta automáticamente si el hosting tiene un límite menor (por ejemplo, en InfinityFree). La pantalla de subida muestra el máximo real permitido.

Para revisar los límites del hosting:

- `upload_max_filesize`
- `post_max_size`

No hace falta editar el código: se toma el menor de los tres valores (20 MB, `upload_max_filesize` y `post_max_size`).

---

## 6. Solución del error 500 ("Something Went Wrong")

El error 500 es genérico. Revisa en este orden:

1. **Credenciales de la base de datos**
   - `DB_HOST` debe ser `sqlXXX.infinityfree.com`, no `localhost`.
   - `DB_NAME` debe incluir el prefijo `if0_...`.
   - Prueba `php scripts/verificar_entorno.php` en local con las mismas credenciales.

2. **Ubicación de `index.php`**
   - Debe estar en `htdocs/index.php`.
   - Si está en `htdocs/EcoEstudiantes/index.php`, mueve todo el contenido un nivel arriba.

3. **`.htaccess` incompatible**
   - Los `.htaccess` de este proyecto ya no usan `Options` ni `php_flag`, que suelen provocar 500 en InfinityFree.
   - Si el error apareció después de editar un `.htaccess`, renómbralo a `.htaccess.bak` y recarga.
   - Vuelve a activarlo línea por línea para encontrar la directiva problemática.

4. **PHP o extensiones**
   - El hosting debe tener PHP 7.4 o superior y las extensiones `mysqli`, `mbstring`, `json` y `openssl`.
   - Si puedes, cambia la versión de PHP desde el panel.

5. **Base de datos mal importada**
   - No importes el esquema dos veces sobre la misma base.
   - Verifica que las tablas existan y que el usuario de la base tenga permisos.

6. **Prueba mínima**
   - Crea `htdocs/test.php` con:

     ```php
     <?php echo 'ok';
     ```

   - Si `test.php` funciona, Apache y PHP están bien y el problema está en la aplicación o en la base.
   - Borra `test.php` al terminar.

7. **Revisa los logs**
   - En InfinityFree, revisa el registro de errores desde el panel o el archivo de logs del hosting.

---

## 7. Actualizaciones y copias de seguridad

### Base de datos

- Exporta la base desde phpMyAdmin antes de cada actualización importante.
- Guarda el `.sql` **fuera** de `htdocs` y nunca lo subas al repositorio.

### Archivos

- Descarga `config/config.local.php` y `uploads/` antes de reemplazar el código.
- Sube solo los archivos modificados; no borres `uploads/` ni `config/config.local.php`.

### Actualización típica

1. Exporta la base.
2. Descarga `config/config.local.php` y `uploads/`.
3. Sube el nuevo paquete.
4. Restaura `config/config.local.php` y `uploads/`.
5. Si hay cambios de esquema, importa el SQL incremental correspondiente.
6. Prueba login, proyectos, actividades y evidencias.

---

## 8. Checklist de seguridad

- [ ] `APP_DEBUG` en `false`.
- [ ] `config/config.local.php` fuera de Git y con permisos `644`.
- [ ] `.git/`, `backups/` y SQL con datos reales fuera de `htdocs`.
- [ ] Contraseña del administrador cambiada.
- [ ] HTTPS activo.
- [ ] Contraseñas de la base de datos y del hosting robustas.
- [ ] Copia de seguridad reciente de la base.
- [ ] Usuarios y roles revisados (elimina cuentas de prueba).
- [ ] Límite de subida del hosting comprobado.
- [ ] `https://tu-dominio/.git/config` y `https://tu-dominio/config/conexion.php` bloqueados.

---

## 9. Notas sobre el repositorio Git

El repositorio público anterior contenía un volcado SQL con datos reales. Ese archivo ya se retiró del proyecto, pero **sigue en el historial de Git y en GitHub**.

Para eliminarlo del historial hace falta reescribirlo con `git filter-repo` o BFG y hacer `git push --force`. Es una operación destructiva: no la ejecutes sin confirmación y sin una copia de seguridad. Mientras tanto:

1. Cambia todas las contraseñas que aparecían en ese volcado.
2. Considera el repositorio como comprometido.
3. Si el proyecto se publica, crea un repositorio nuevo y limpio para el código de producción.

---

## 10. Comandos de referencia

```bash
# Verificar entorno local
php scripts/verificar_entorno.php

# Generar hash de contraseña
php scripts/crear_hash.php "NuevaClave123"

# Crear paquete ZIP sin datos sensibles
python3 scripts/empaquetar.py
```
