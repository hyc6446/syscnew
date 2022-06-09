<?php

return [
    'autoload' => false,
    'hooks' => [
        'app_init' => [
            'epay',
            'shopro',
        ],
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
    ],
    'route' => [],
    'priority' => [],
    'domain' => '',
];
