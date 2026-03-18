<?php
// config/email.php
return [
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'secure' => 'tls',
        'auth' => true,
        'username' => 'atencion.usuario.pos@gmail.com', // Tu cuenta de Gmail
        'password' => 'vxdhyssiccxswnyd', // Pégala aquí
    ],
    'from' => [
        'email' => 'atencion.usuario.pos@gmail.com',
        'name' => 'Atención al Usuario',
    ],
    'to' => [
        'email' => 'atencion.usuario.pos@gmail.com', // AQUÍ LLEGARÁN LOS CORREOS
        'name' => 'Equipo de Soporte',
    ],
];