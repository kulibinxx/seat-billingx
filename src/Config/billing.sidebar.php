<?php

return [
    'billing' => [
        'name' => 'SeAT IRS',
        'icon' => 'fa-credit-card',
        'route_segment' => 'billing',
        'permission' => 'billing.view',
        'route' => 'billing.view',
        'entries' => [
            'billing' => [
                'name' => 'Billing Statements',
                'icon' => 'fa-money',
                'route_segment' => 'billing',
                'route' => 'billing.view',
                'permission' => 'billing.view',
            ],
            'settings' => [
                'name' => 'Settings',
                'icon' => 'fa-gear',
                'route_segment' => 'billing',
                'route' => 'billing.settings',
                'permission' => 'billing.settings',
            ],
        ],
    ],
];
