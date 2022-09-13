<?php

return [
    'autoload' => false,
    'hooks' => [
        'sms_send' => [
            'alisms',
        ],
        'sms_notice' => [
            'alisms',
        ],
        'sms_check' => [
            'alisms',
        ],
        'app_init' => [
            'epay',
            'shopro',
        ],
        'upgrade' => [
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
