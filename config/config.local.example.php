<?php
/**
 * Plantilla de configuración local.
 *
 * Copia este archivo como config/config.local.php y ajusta los valores.
 * config/config.local.php está ignorado por Git y nunca debe publicarse.
 *
 * En InfinityFree los datos están en el panel de control, sección MySQL:
 *   - Host:     sqlXXX.infinityfree.com
 *   - Usuario:  if0_XXXXXXXX
 *   - Nombre:   if0_XXXXXXXX_ecoestudiantes
 */
return [
    'DB_HOST'   => 'sqlXXX.infinityfree.com',
    'DB_USER'   => 'if0_XXXXXXXX',
    'DB_PASS'   => 'CAMBIAR_POR_LA_CONTRASENA_DE_LA_BASE',
    'DB_NAME'   => 'if0_XXXXXXXX_ecoestudiantes',
    'DB_PORT'   => 3306,

    // true solo en desarrollo local. En producción debe ser false.
    'APP_DEBUG' => false,
];
