# Informe de pruebas - EcoEstudiantes Digitales

Institucion Educativa Republica de Uruguay. Tecnico Auxiliar en Desarrollo de Software, PIA 2026.

Este informe contrasta lo prometido en la propuesta con lo que la aplicacion hace hoy. No borra datos ni cambia la base.

## Resultado general

La aplicacion cumple el objetivo: registrar, seguir y evaluar Proyectos Ambientales Escolares, con roles distintos y datos guardados en MySQL.

## Pruebas funcionales

| Funcion | Resultado | Como se comprobo |
| --- | --- | --- |
| Ingreso y registro | Cumple | Correo y contrasena con password_hash. Una cuenta inactiva no entra. |
| Roles | Cumple | Administrador, Docente y Estudiante ven menus y acciones distintas. |
| Usuarios e instituciones | Cumple | El administrador crea, edita y desactiva usuarios, y registra instituciones. |
| Proyectos PRAE | Cumple | Se crean, editan, filtran por estado y eliminan con sus actividades. |
| Articulacion con el PEI | Cumple | Cada proyecto guarda Gestion academica, de comunidad, administrativa o transversal. |
| Actividades | Cumple | El docente registra actividades dentro de un proyecto. |
| Inscripcion del estudiante | Cumple | El estudiante se inscribe y ve sus actividades. |
| Evidencias | Cumple | Se suben a uploads/evidencias con nombre aleatorio y se pueden ver. |
| Evaluacion | Cumple | La nota va de 1.0 a 5.0 y solo se exige si el estado es Participo. |
| Reportes | Cumple | Muestran estados, participacion, promedio, graficas e impacto bajo, medio o alto. |
| Noticias | Cumple | El administrador publica hasta 3 imagenes. |
| Galeria | Cumple | Las fotos antiguas y las nuevas salen en la lista. Quitar las borra y no vuelven solas. |
| Contacto y respuesta | Cumple | El usuario escribe, el administrador responde y la respuesta se lee en Mis mensajes. |
| Manual de usuario | Cumple | El menu muestra la guia segun el rol. |
| Manual tecnico | Cumple | Esta en MANUAL_TECNICO.md. |

## Lo que se ajusto para que no quedara incoherente

- Servicios ya no dice seguimiento en tiempo real, graficos personalizables ni exportacion en varios formatos.
- La barra del panel de administrador solo salta a Usuarios, Noticias, Galeria, Proyectos y Mensajes, y deja Ver sitio para el menu general.

## Limites conocidos

- Los reportes se actualizan al recargar o al pulsar Actualizar datos. No hay aviso en vivo.
- El impacto es el nivel que marca el docente o el administrador, no un calculo automatico de arboles o residuos.
- La aplicacion corre en XAMPP local. No esta publicada en internet.
