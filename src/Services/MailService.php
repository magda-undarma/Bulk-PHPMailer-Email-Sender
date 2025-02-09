<?php

namespace Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService {
    private $config;
    private $smtpConfigs;
    private $smtpIndex = 0;
    private $cumulativeSuccessCount = 0;

    public function __construct($config) {
        $this->config = $config;
        $this->smtpConfigs = $this->loadSmtpConfigs($config['smtp_file']);
    }

    private function loadSmtpConfigs($filePath) {
        if (!file_exists($filePath)) {
            throw new \Exception("SMTP configuration file not found: $filePath");
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $configs = [];

        foreach ($lines as $line) {
            $parts = explode('|', $line);
            if (count($parts) !== 5) {
                throw new \Exception("Invalid SMTP configuration format in line: $line");
            }

            $configs[] = [
                'host' => $parts[0],
                'port' => $parts[1],
                'username' => $parts[2],
                'password' => $parts[3],
                'encryption' => $parts[4],
            ];
        }

        return $configs;
    }

    private function parsePlaceholders($content) {
        return preg_replace_callback('/##(date_utc[-+]?\d+|number_\d+|words_low_\d+|words_up_\d+|wordsnum_low_\d+|wordsnum_up_\d+)##/', function ($matches) {
            $placeholder = $matches[1];

            if (strpos($placeholder, 'date_utc') === 0) {
                preg_match('/date_utc([-+]?\d+)/', $placeholder, $utcMatches);
                $utcOffset = isset($utcMatches[1]) ? (int)$utcMatches[1] : 0;
                return $this->generateDateForTimeZone($utcOffset);
            }

            preg_match('/(number|words_low|words_up|wordsnum_low|wordsnum_up)_(\d+)/', $placeholder, $otherMatches);
            $type = $otherMatches[1] ?? '';
            $length = (int)($otherMatches[2] ?? 0);

            switch ($type) {
                case 'number':
                    return $this->generateRandomNumber($length);
                case 'words_low':
                    return $this->generateRandomString($length, 'abcdefghijklmnopqrstuvwxyz');
                case 'words_up':
                    return $this->generateRandomString($length, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
                case 'wordsnum_low':
                    return $this->generateRandomString($length, 'abcdefghijklmnopqrstuvwxyz0123456789');
                case 'wordsnum_up':
                    return $this->generateRandomString($length, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
                default:
                    return '';
            }
        }, $content);
    }

    private function generateRandomNumber($length) {
        return substr(str_repeat('0123456789', $length), 0, $length);
    }

    private function generateRandomString($length, $pool) {
        $output = '';
        $poolLength = strlen($pool);
        for ($i = 0; $i < $length; $i++) {
            $output .= $pool[random_int(0, $poolLength - 1)];
        }
        return $output;
    }

    private function generateDateForTimeZone($utcOffset) {
        $timezoneName = timezone_name_from_abbr('', $utcOffset * 3600, false);

        if (!$timezoneName) {
            $timezoneName = "Etc/GMT" . ($utcOffset >= 0 ? "-$utcOffset" : "+" . abs($utcOffset));
        }

        $date = new \DateTime('now', new \DateTimeZone($timezoneName));
        $formattedDate = $date->format('F j, Y \a\t g:i:s A');
        $timezoneAbbreviation = $date->format('T');

        return "$formattedDate ($timezoneAbbreviation)";
    }

    private function getNextSmtpConfig() {
        $config = $this->smtpConfigs[$this->smtpIndex];
        $this->smtpIndex = ($this->smtpIndex + 1) % count($this->smtpConfigs);
        return $config;
    }

    public function sendBatchEmail($recipients, $subject, $body, $cc = [], $attachments = []) {
        $batchSize = $this->config['bulk']['batch_size'] ?? 10;
        $delay = $this->config['bulk']['delay'] ?? 0;
        $batches = array_chunk($recipients, $batchSize);

        foreach ($batches as $batch) {
            $smtpConfig = $this->getNextSmtpConfig();

            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = $smtpConfig['host'];
                $mail->SMTPAuth = true;
                $mail->Username = $smtpConfig['username'];
                $mail->Password = $smtpConfig['password'];
                $mail->SMTPSecure = $smtpConfig['encryption'];
                $mail->Port = $smtpConfig['port'];

                $subject = $this->parsePlaceholders($subject);
                $body = $this->parsePlaceholders($body);

                $fromName = $this->parsePlaceholders($this->config['email']['from_name']);
                $fromEmail = strpos($smtpConfig['host'], 'office365.com') !== false || strpos($smtpConfig['host'], 'outlook.com') !== false
                    ? $smtpConfig['username']
                    : $this->parsePlaceholders($this->config['email']['from_email']);

                $mail->setFrom($fromEmail, '=?UTF-8?B?' . base64_encode($fromName) . '?=');

                foreach ($batch as $recipient) {
                    $mail->addBCC($recipient);
                }

                if (!empty($cc)) {
                    foreach ($cc as $ccEmail) {
                        if (!empty($ccEmail)) {
                            $mail->addCC($ccEmail);
                        }
                    }
                }

                foreach ($attachments as $attachment) {
                    if (isset($attachment['path']) && file_exists($attachment['path'])) {
                        $mail->addAttachment($attachment['path']);
                    }
                }

                $mail->isHTML(true);
                $mail->Subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
                $mail->Body = $body;

                $mail->send();
                $this->cumulativeSuccessCount += count($batch);

                echo "\033[32m--------------- Email Sent ---------------\033[0m\n";
                echo "\033[34mSMTP Server:\033[0m {$smtpConfig['host']}\n";
                echo "\033[34mSender Email:\033[0m $fromEmail\n";
                echo "\033[34mSender Name:\033[0m $fromName\n";
                echo "\033[34mRecipient(s):\033[0m\n";
                foreach ($batch as $recipient) {
                    echo "  - $recipient\n";
                }
                echo "\033[34mTotal successfully sent emails:\033[0m {$this->cumulativeSuccessCount}\n";
                echo "\033[32m-----------------------------------------\033[0m\n";

            } catch (\Exception $e) {
                echo "Error: " . $e->getMessage() . "\n";
                continue;
            }

            if ($delay > 0) {
                sleep($delay);
            }
        }
    }
}