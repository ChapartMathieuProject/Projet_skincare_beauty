<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/classes/Service/MailerInterface.php';
require_once __DIR__ . '/classes/Service/PhpMailerService.php';

$mailer = new PhpMailerService(
    getenv('MAIL_HOST')         ?: 'localhost',
    (int) (getenv('MAIL_PORT')  ?: 1025),
    getenv('MAIL_USERNAME')     ?: '',
    getenv('MAIL_PASSWORD')     ?: '',
    getenv('MAIL_ENCRYPTION')   ?: '',
    getenv('MAIL_FROM_ADDRESS') ?: 'noreply@skincarebeauty.fr',
    getenv('MAIL_FROM_NAME')    ?: 'SkinCareBeauty'
);