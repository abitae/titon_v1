<?php

return [

    'webmail_url' => env('FRONTEND_WEBMAIL_URL', 'https://titon.pe:2096/'),

    'mail_host' => env('FRONTEND_MAIL_HOST', 'mail.titon.pe'),

    'imap_port' => (int) env('FRONTEND_IMAP_PORT', 993),

    'smtp_port' => (int) env('FRONTEND_SMTP_PORT', 465),

    'mail_domain' => env('FRONTEND_MAIL_DOMAIN', 'titon.pe'),

];
