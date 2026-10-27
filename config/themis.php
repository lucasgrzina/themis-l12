<?php
return [

    'avisos' => [
        'dias_previos'  => 30,
        'dias_vencidos' => 365,
    ],

    'vencimientos' => [
    ],

    // Archivado automatico de tramites con beneficio acordado (job diario 00:00).
    'archivado' => [
        // Estados considerados "Beneficio Acordado": 515 = AFJP, 516 = ANSES.
        'estados_beneficio_acordado' => array_values(array_filter(array_map(
            'intval',
            explode(',', (string) env('ARCHIVADO_ESTADOS', '515,516'))
        ))),

        // Antiguedad minima (en anios) de fecha_inicio para archivar automaticamente.
        // 2 anios reproduce aprox. el corte "inicio hasta 31/12/2024".
        'antiguedad_anios' => (int) env('ARCHIVADO_ANTIGUEDAD_ANIOS', 2),

        // Usuario del sistema que queda registrado como quien archivo (null = sin usuario).
        'usuario_sistema_id' => env('ARCHIVADO_USUARIO_SISTEMA_ID') !== null
            ? (int) env('ARCHIVADO_USUARIO_SISTEMA_ID')
            : null,

        // Tamanio de lote para procesar en chunks.
        'chunk' => (int) env('ARCHIVADO_CHUNK', 500),

        // Carpeta (relativa a storage/app) donde se guarda el informe.
        'informe_dir' => env('ARCHIVADO_INFORME_DIR', 'informes/archivado'),

        // Emails a los que enviar el informe (coma-separados); vacio = no enviar.
        'informe_emails' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('ARCHIVADO_INFORME_EMAILS', ''))
        ))),
    ],

    'time' => [
        'gestion_default' => env('TIME_GESTION_DEFAULT', 'Actividades a clientes'),
        // Horas de timer corriendo a partir de las cuales se avisa / se cierra solo
        'timer_aviso_horas' => (float) env('TIME_TIMER_AVISO_HORAS', 4),
        'timer_max_horas' => (float) env('TIME_TIMER_MAX_HORAS', 10),
        // Zona horaria local en la que el usuario ingresa las horas desde/hasta.
        // Se usa para convertir la hora tipeada (local) al instante UTC que se guarda,
        // de modo que al mostrarla vuelva a verse igual. app.timezone sigue en UTC.
        'tz' => env('TIME_TZ', 'America/Argentina/Buenos_Aires'),
    ],

];