<?php

return [
    /*
     * Default Casbin driver
     */
    'default' => [
        /*
         * Casbin model setting.
         */
        'model' => [
            // Available Settings: "file", "text"
            'config_type' => 'file',
            'config_file_path' => BASE_PATH . '/config/autoload/casbin-rbac-model.conf',
            'config_text' => '',
        ],
        /*
         * Casbin adapter .
         */
        'adapter' => [
            'class' => \Donjan\Casbin\Adapters\DatabaseAdapter::class,
            'table_name' => 'casbin_rule',
            'connection' => 'default'
        ],
        'log' => [
            'enabled' => false,
        ],
        'cache' => [
            // changes whether Donjan\Casbin will cache the rules.
            'enabled' => false,
            // cache Key
            'key' => 'rules',
            // ttl \DateTimeInterface|\DateInterval|int|null
            'ttl' => 24 * 60,
        ],
    ]
];
