<?php

return [
    'header' => [
        [
            'type' => 'dropdown',
            'key' => 'master-data',
            'label' => 'Master Data',
            'active' => [
                'continents.*',
                'nations.*',
                'regions.*',
            ],
            'items' => [
                ['type' => 'label', 'label' => 'Geography'],

                [
                    'type' => 'route',
                    'label' => 'Continents',
                    'route' => 'continents.index',
                    'active' => ['continents.*'],
                ],
                [
                    'type' => 'route',
                    'label' => 'Nations',
                    'route' => 'nations.index',
                    'active' => ['nations.*'],
                ],
                [
                    'type' => 'route',
                    'label' => 'Regions',
                    'route' => 'regions.index',
                    'active' => ['regions.*'],
                ],
            ],
        ],

        ['type' => 'separator'],

        [
            'type' => 'route',
            'label' => 'Para Swim Styles',
            'route' => 'para-swim-styles.index',
            'active' => ['para-swim-styles.*'],
        ],

        ['type' => 'separator'],

        [
            'type' => 'dropdown',
            'key' => 'imports',
            'label' => 'Imports',
            'active' => [
                'nations.import.*',
                'regions.import.*',
                'imports.lenex.*',
            ],
            'items' => [
                ['type' => 'label', 'label' => 'Master Data Imports'],

                [
                    'type' => 'route',
                    'label' => 'Nations Import',
                    'route' => 'nations.import.show',
                    'active' => ['nations.import.*'],
                ],
                [
                    'type' => 'route',
                    'label' => 'Regions Import',
                    'route' => 'regions.import.show',
                    'active' => ['regions.import.*'],
                ],

                ['type' => 'separator'],

                ['type' => 'label', 'label' => 'Competition Imports'],

                [
                    'type' => 'route',
                    'label' => 'LENEX Import',
                    'route' => 'imports.lenex.create',
                    'active' => ['imports.lenex.*'],
                ],
            ],
        ],
    ],
];
