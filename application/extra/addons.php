<?php

return [
    'autoload' => false,
    'hooks' => [
        'config_init' => [
            'qcloudsms',
            'ueditor',
        ],
        'sms_send' => [
            'qcloudsms',
        ],
        'sms_notice' => [
            'qcloudsms',
        ],
        'sms_check' => [
            'qcloudsms',
        ],
        'upgrade' => [
            'shopro',
        ],
        'app_init' => [
            'shopro',
        ],
    ],
    'route' => [],
    'priority' => [],
    'domain' => '',
];
