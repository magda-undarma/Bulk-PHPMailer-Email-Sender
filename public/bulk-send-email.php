<?php

require __DIR__ . '/../vendor/autoload.php';
$config = require __DIR__ . '/../config.php';

use Services\MailService;

try {
    $mailService = new MailService($config);
    $recipients = file($config['list_file'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if (!$recipients || empty($recipients)) {
        throw new Exception("Recipient list is empty or not found.");
    }

    $mailService->sendBatchEmail(
        $recipients,
        $config['email']['subject'],
        file_get_contents($config['email']['html_template']),
        $config['bulk']['cc'],
        $config['email']['attachments']
    );

    echo "Bulk email operation completed successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
