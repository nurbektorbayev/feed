<?php

return [
    'propel' => [
        'bin'           => 'vendor/bin/propel',
        'project'       => 'feed',
        'database'      => 'pgsql',
        'dsn'           => 'pgsql:host=PG_HOST;port=PG_PORT;dbname=PG_DATABASE',
        'db_user'       => 'PG_USER',
        'db_password'   => 'PG_PASSWORD',
        'platform'      => 'pgsql',
        'config_dir'    => 'src/Resource/propel/connection',
        'schema_dir'    => 'src/Resource/propel/schema',
        'model_dir'     => 'src/Model',
        'migration_dir' => 'src/Resource/propel/migration',
        'migration_table' => 'feed_propel_migration',
    ],
    'database' => [
        'db' => 'PG_DATABASE',
        'host' => 'PG_HOST',
        'port' => 'PG_PORT',
        'username' => 'PG_USER',
        'password' => 'PG_PASSWORD',
    ],

    'centrifugo' => [
        'host' => 'CENTRIFUGO_HOST',
        'api_key' => 'CENTRIFUGO_API_KEY',
        'secret_key' => 'CENTRIFUGO_SECRET_KEY',
    ],

    'badges' => [
        'host' => 'BADGES_HOST',
    ],
];