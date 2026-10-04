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
3. Abrir `http://localhost/EcoEstudiantes/`.
4. La conexion real esta en `config/conexion.php`. El usuario local es `root` sin contrasena. `conexion.php` de la raiz solo llama a ese archivo.

No importar `base_de_datos/ecoestudiantes.sql` si la base ya tiene datos.

## Roles

- Administrador: usuarios, instituciones, noticias, mensajes, proyectos y reportes.
- Docente: proyectos de los que es responsable, actividades, evaluacion y reportes.
- Estudiante: inscripcion, evidencias y consulta de su nota.

La sesion guarda `id_usuario`, `nombre`, `rol` y `correo`. Cada pagina de accion vuelve a comprobar el rol.

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

`roles`, `usuarios`, `instituciones`, `proyectos_prae`, `actividades`, `participantes`, `evidencias`, `noticias`, `noticias_imagenes`, `mensajes_contacto`.

La nota esta en `participantes.nota`. El archivo de una evidencia se guarda solo con el nombre. La respuesta de contacto esta en `mensajes_contacto.respuesta`.

## Seguridad aplicada

- Consultas preparadas en las acciones.
- Archivos subidos con extension permitida, tamano maximo y nombre aleatorio.
- `uploads/evidencias/.htaccess` impide ejecutar PHP en esa carpeta.
- No se usa `DROP` en el codigo de la aplicacion.
