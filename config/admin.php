<?php

return [
    /*
     * Comma-separated list of IPs allowed to access the admin panel.
     * Leave empty to allow all IPs (development default).
     *
     * Example: ADMIN_ALLOWED_IPS=1.2.3.4,5.6.7.8
     */
    'allowed_ips' => array_filter(
        explode(',', env('ADMIN_ALLOWED_IPS', '')),
        fn (string $ip) => $ip !== '',
    ),
];
