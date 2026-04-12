<?php

require_once __DIR__ . '/../../config/env.php';
load_env_file();

class EmailService {
    private $apiKey;
    private $senderEmail;
    private $senderName;
    private $enabled;

    public function __construct() {
        $this->apiKey = getenv('BREVO_API_KEY') ?: '';
        $this->senderEmail = getenv('MAIL_FROM_EMAIL') ?: 'noreply@labsync.local';
        $this->senderName = getenv('MAIL_FROM_NAME') ?: 'LabSync System';
        $enabledValue = strtolower((string)(getenv('MAIL_ENABLED') ?: 'false'));
        $this->enabled = in_array($enabledValue, ['1', 'true', 'yes', 'on'], true);
    }

    public function sendEmail($toEmail, $toName, $subject, $htmlContent) {
        if (!$this->enabled) {
            return [
                'status' => 'skipped',
                'message' => 'Email sending skipped because MAIL_ENABLED is false.'
            ];
        }

        if (empty($this->apiKey) || empty($this->senderEmail)) {
            return [
                'status' => 'error',
                'message' => 'Email configuration missing: BREVO_API_KEY or MAIL_FROM_EMAIL not set.'
            ];
        }

        $payload = [
            'sender' => [
                'name' => $this->senderName,
                'email' => $this->senderEmail,
            ],
            'to' => [[
                'email' => $toEmail,
                'name' => $toName ?: 'Patient',
            ]],
            'subject' => $subject,
            'htmlContent' => $htmlContent,
        ];

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json',
            'api-key: ' . $this->apiKey,
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            return [
                'status' => 'error',
                'message' => 'Email sending failed (cURL error: ' . $error . ')'
            ];
        }

        $respData = json_decode((string)$response, true);
        if ($httpCode >= 200 && $httpCode < 300 && isset($respData['messageId'])) {
            return [
                'status' => 'success',
                'message' => 'Email sent successfully.',
                'messageId' => $respData['messageId'],
            ];
        }

        $errorMsg = $respData['message'] ?? 'Unknown Brevo error';
        return [
            'status' => 'error',
            'message' => 'Email sending failed. Brevo error: ' . $errorMsg,
        ];
    }

    public function sendAppointmentBookedEmail($email, $recipientName, $payload) {
        $appointmentId = (int)($payload['appointment_id'] ?? 0);
        $appointmentDate = htmlspecialchars((string)($payload['appointment_date'] ?? 'N/A'));
        $appointmentTime = htmlspecialchars((string)($payload['appointment_time'] ?? 'N/A'));
        $status = htmlspecialchars((string)($payload['status'] ?? 'Pending'));
        $channel = htmlspecialchars((string)($payload['booking_channel'] ?? 'online_self'));
        $total = number_format((float)($payload['total_price'] ?? 0), 2);
        $testsSummary = htmlspecialchars((string)($payload['tests_summary'] ?? 'Selected tests'));

        $subject = 'Appointment Confirmation #' . $appointmentId . ' - LabSync';

        $htmlContent = "
            <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; color: #243046; line-height: 1.6; }
                        .header { background: #0f4c81; color: #fff; padding: 16px; text-align: center; }
                        .content { padding: 18px; }
                        .box { background: #f4f8fc; border-left: 4px solid #0f4c81; padding: 14px; margin: 16px 0; }
                        .muted { color: #5a667a; font-size: 13px; }
                    </style>
                </head>
                <body>
                    <div class='header'>
                        <h2>LabSync Appointment Confirmation</h2>
                    </div>
                    <div class='content'>
                        <p>Dear " . htmlspecialchars((string)($recipientName ?: 'Patient')) . ",</p>
                        <p>Your appointment has been created successfully.</p>
                        <div class='box'>
                            <p><strong>Appointment ID:</strong> #" . $appointmentId . "</p>
                            <p><strong>Date:</strong> " . $appointmentDate . "</p>
                            <p><strong>Time:</strong> " . $appointmentTime . "</p>
                            <p><strong>Status:</strong> " . $status . "</p>
                            <p><strong>Channel:</strong> " . $channel . "</p>
                            <p><strong>Tests:</strong> " . $testsSummary . "</p>
                            <p><strong>Total:</strong> LKR " . $total . "</p>
                        </div>
                        <p class='muted'>Please arrive at least 10 minutes before your scheduled time.</p>
                        <p>Best regards,<br><strong>LabSync Team</strong></p>
                    </div>
                </body>
            </html>
        ";

        return $this->sendEmail($email, $recipientName, $subject, $htmlContent);
    }
}
