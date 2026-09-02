<?php

/**
 * @see https://github.com/artesaos/seotools
 */

return [
    'inertia' => env('SEO_TOOLS_INERTIA', false),
    'meta' => [
        /*
         * The default configurations to be used by the meta generator.
         */
        'defaults' => [
            'title' => 'Favala CRM',
            'titleBefore' => false,
            'description' => 'Favala CRM — manage customers, leads, and relationships in one place.',
            'separator' => ' – ',
            'keywords' => ['favala', 'crm', 'customer relationship management', 'leads', 'sales'],
            'canonical' => 'current',
            'robots' => false, // Set to 'all', 'none' or any combination of index/noindex and follow/nofollow
        ],
        /*
         * Webmaster tags are always added.
         */
        'webmaster_tags' => [
            'google' => null,
            'bing' => null,
            'alexa' => null,
            'pinterest' => null,
            'yandex' => null,
            'norton' => null,
        ],

        'add_notranslate_class' => false,
    ],
    'opengraph' => [
        /*
         * The default configurations to be used by the opengraph generator.
         */
        'defaults' => [
            'title' => 'Favala CRM',
            'description' => 'Favala CRM — manage customers, leads, and relationships in one place.',
            'url' => null,
            'type' => 'website',
            'site_name' => 'Favala CRM',
            'images' => [],
        ],
    ],
    'twitter' => [
        /*
         * The default values to be used by the twitter cards generator.
         */
        'defaults' => [
            // 'card'        => 'summary',
            // 'site'        => '@LuizVinicius73',
        ],
    ],
    'json-ld' => [
        /*
         * The default configurations to be used by the json-ld generator.
         */
        'defaults' => [
            'title' => 'Favala CRM',
            'description' => 'Favala CRM — manage customers, leads, and relationships in one place.',
            'url' => 'current',
            'type' => 'WebPage',
            'images' => [],
        ],
    ],
];
