# EcoEstudiantes Digitales

Plataforma para gestionar, hacer seguimiento y evaluar Proyectos Ambientales Escolares (PRAE). Tiene tres roles: Administrador, Docente y Estudiante.

## Tecnologías

- PHP puro con mysqli y consultas preparadas
- MySQL o MariaDB en XAMPP
- HTML y CSS propios
- JavaScript propio y Chart.js por CDN para los reportes
- Contraseñas con `password_hash()`

## Instalación

1. Instala XAMPP y enciende Apache y MySQL.
2. Clona el proyecto en la carpeta de XAMPP:

```bat
cd C:\xampp\htdocs
git clone https://github.com/wos-7171/EcoEstudiantes.git EcoEstudiantes
```

La carpeta debe llamarse `EcoEstudiantes`. Las direcciones del sitio dependen de ese nombre.

3. En phpMyAdmin crea la base `ecoestudiantes` e importa `base_de_datos/ecoestudiantes.sql` solo si la base todavía no existe. Si ya tienes datos, no importes ese archivo: borraría lo que ya cargaste.
4. Abre `config/conexion.php` solo si tu usuario o contraseña de MySQL no son `root` sin contraseña.
5. Entra en `http://localhost/EcoEstudiantes/`.

## Estructura

- `index.php` es el inicio de sesión. El resto de pantallas sigue en la raíz para no cambiar las direcciones.
- `config/conexion.php` es la conexión real. `conexion.php` en la raíz solo la llama.
- `includes/` tiene las funciones y el menú. Los archivos de la raíz son puentes.
- `assets/css` y `assets/js` tienen los estilos y el JavaScript.
- `pages/` tiene las páginas públicas: Quiénes somos, Noticias, Contacto.
- `uploads/evidencias/` guarda los archivos que suben los estudiantes. `evidencias/` se dejó por los archivos antiguos.
- `images/noticias/` guarda las fotos de las noticias.

## Base de datos

La base se llama `ecoestudiantes`. No ejecutes `DROP DATABASE` ni `DROP TABLE` sobre una base que ya tiene usuarios o proyectos. Si un cambio nuevo necesita columnas, el SQL para pegar en phpMyAdmin se entrega aparte y no borra datos.

## Desarrollo

Después de bajar cambios:

```bat
cd C:\xampp\htdocs\EcoEstudiantes
git pull
```

Recarga el navegador con Ctrl+F5.
