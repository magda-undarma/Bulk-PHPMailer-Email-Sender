# Bulk PHPMailer Email Sender

This project provides a powerful script for sending bulk emails using PHPMailer, complete with advanced features such as SMTP rotation, placeholder parsing, and support for special characters in subjects and sender names. Below is a detailed guide to the features, installation process, and usage of the script.

---

## Features

1. **SMTP Rotation**:
   - Rotates SMTP servers listed in `smtp.txt` for each batch of emails sent.
   - Ensures reliability and prevents throttling or blocking by email providers.

2. **Placeholder Parsing**:
   - Dynamically replaces placeholders in `from_name`, `from_email`, and `subject` with:
     - Randomized text (e.g., `##words_low_N##`, `##words_up_N##`, `##wordsnum_low_N##`, `##wordsnum_up_N##`).
     - Dynamic date values (e.g., `##date_utc±X##` for UTC-based timestamps).
     - Change the N or X value with number 1-50

3. **Special Character Support**:
   - UTF-8 Base64 encoding ensures proper rendering of special characters in subject lines and sender names.

4. **Flexible From Mail Configuration**:
   - For non-Outlook/Office SMTP servers, the `from_email` field can use placeholders and be randomized.
   - For Outlook/Office SMTP servers, the `from_email` defaults to the SMTP username, ensuring compatibility.

5. **Batch Sending**:
   - Configurable batch size and delay between batches for optimized delivery.

6. **Detailed Logging**:
   - Outputs information about sent emails, including SMTP server, sender details, recipients, and total emails sent.
   - Color-coded console output for easy readability.

7. **Attachment Support**:
   - Easily attach files to emails with options for encoding.

---

## Requirements

1. **PHP**: PHP 8.0 or higher.
   - Ensure the PHP CLI is installed and accessible.
   - Required extensions: `mbstring`, `openssl`.

2. **Composer**:
   - Used to install PHPMailer and manage dependencies.

3. **PHPMailer**:
   - Installed via Composer.

4. **SMTP Credentials**:
   - List of SMTP servers and credentials in `smtp.txt`.

5. **Email Recipients**:
   - Recipient email addresses in `list.txt`.

---

## Installation

1. **Install PHP**:
   - On Ubuntu/Debian:
     ```bash
     sudo apt update
     sudo apt install php-cli php-mbstring php-openssl
     ```
   - On macOS (using Homebrew):
     ```bash
     brew install php
     ```

2. **Install Composer**:
   - Download and install Composer:
     ```bash
     curl -sS https://getcomposer.org/installer | php
     mv composer.phar /usr/local/bin/composer
     ```

3. **Clone the Repository**:
   ```bash
   git clone https://github.com/your-repo/bulk-phpmailer-sender.git
   cd bulk-phpmailer-sender
   ```

4. **Install Dependencies**:
   ```bash
   composer install
   ```

---

## Configuration

1. **SMTP Configuration**:
   - Create or update `smtp.txt` with the following format (one SMTP server per line):
     ```
     smtp.server.com|587|username@example.com|password|tls
     smtp.office365.com|587|user@office.com|password|tls
     ```

2. **Recipient List**:
   - Add recipient email addresses to `list.txt` (one address per line):
     ```
     recipient1@example.com
     recipient2@example.com
     ```

3. **Main Configuration**:
   - Update `config.php`:
     ```php
     return [
         'smtp_file' => './smtp.txt',
         'list_file' => './list.txt',
         'email' => [
             'from_name' => 'Your Company ##words_up_5##',
             'from_email' => 'no-reply##wordsnum_low_4##@example.com',
             'subject' => 'Welcome ##words_up_4## ##date_utc-5##',
             'html_template' => './letter.html',
             'body_encoding' => 'utf-8',
             'subject_encoding' => 'utf-8',
             'attachments' => [
                 [
                     'path' => './attachments/sample.pdf',
                     'encoding' => 'base64',
                 ],
             ],
         ],
         'bulk' => [
             'batch_size' => 10,
             'delay' => 5,
             'cc' => [
                 '', // Add CC recipients or leave empty
             ],
         ],
     ];
     ```

4. **Email Template**:
   - Customize `letter.html` for your email content.

---

## Usage

1. Run the script via CLI:
   ```bash
   php public/bulk-send-email.php
   ```

2. Monitor console output for detailed logs.

---

## Notes

- Ensure correct permissions for the `smtp.txt` and `list.txt` files.
- Validate email addresses in `list.txt` to avoid delivery errors.
- Test SMTP settings with a small batch size before scaling up.

---

## Troubleshooting

1. **SMTP Errors**:
   - Verify credentials and ensure the SMTP server is accessible.

2. **Unrendered Placeholders**:
   - Check placeholder syntax in `config.php` and ensure correct parsing logic.

3. **Special Characters**:
   - Ensure UTF-8 encoding is specified for subject and body content.

---

## License

This project is licensed under the MIT License.