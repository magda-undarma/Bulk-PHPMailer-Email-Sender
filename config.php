<?php
return [
    'smtp_file' => './smtp.txt', // Path to SMTP configuration file
    'list_file' => './list.txt', // Path to recipient list file
    'email' => [
        'from_name' => 'Your Company ##words_up_5##', // Placeholder-supported sender name
        'from_email' => 'no-reply##wordsnum_low_4##@example.com', // Sender email address
        'subject' => 'Welcome ##words_up_4## ##date_utc-5##', // Placeholder-supported subject
        'html_template' => './letter.html', // Path to the HTML template
        'body_encoding' => 'utf-8', // Email body encoding
        'subject_encoding' => 'utf-8', // Email subject encoding
        'attachments' => [ // Attachments array
            [
                'path' => './attachments/sample.pdf', // Attachment file path
                'encoding' => 'base64', // Attachment encoding
            ],
        ],
    ],

    'bulk' => [
        'batch_size' => 1, // Number of recipients per batch
        'delay' => 1, // Delay between batches in seconds
        'cc' => [ // Optional CC recipients
            '',
            '', // Please Remove Recipent CC if not use like this '',
        ],
    ],
];
