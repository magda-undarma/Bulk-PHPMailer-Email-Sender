<?php

namespace Config;

class MailerConfig {
    public static function getSMTPConfig($smtpFile) {
        if (!file_exists($smtpFile)) {
            throw new \Exception("SMTP configuration file not found: $smtpFile");
        }
        return file($smtpFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }
}
