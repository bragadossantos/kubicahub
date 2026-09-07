<?php
/**
 * Kubica Hub — Configuração da Base de Dados MySQL
 * Alterar as credenciais conforme o ambiente (XAMPP local / hosting Angola).
 */

declare(strict_types=1);

return [
    'default' => 'mysql',

    'connections' => [
        'mysql' => [
            'driver'    => 'mysql',
            'host'      => getenv('DB_HOST')     ?: '127.0.0.1',
            'port'      => getenv('DB_PORT')     ?: '3306',
            'database'  => getenv('DB_NAME')     ?: 'kubica_hub',
            'username'  => getenv('DB_USER')     ?: 'root',
            'password'  => getenv('DB_PASS')     ?: '',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'options'   => [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 5,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'",
            ],
        ],
    ],
];
