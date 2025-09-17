<?php

return [
    'paths' => ['api/*', 'broadcasting/auth'], // ضيف المسارات اللي محتاجها
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:5173'], // React dev server
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];