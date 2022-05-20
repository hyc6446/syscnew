<?php

return [
    'autoload' => false,
    'hooks' => [
        'upgrade' => [
            'shopro',
        ],
        'app_init' => [
            'shopro',
        ],
        'config_init' => [
            'ueditor',
        ],
    ],
    'route' => [],
    'priority' => [],
    'domain' => '',
];
