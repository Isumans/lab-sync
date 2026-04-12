<?php

require_once __DIR__ . '/../../config/env.php';
load_env_file();

class SmsService {
    private $apiToken;
    private $senderId;
    private $enabled;
    private $endpoint;

    public function __construct() {
        $this->apiToken = getenv('TEXTLK_API_TOKEN') ?: '';
        $this->senderId = getenv('TEXTLK_SENDER_ID') ?: '';
        $enabledValue = strtolower((string)(getenv('SMS_ENABLED') ?: 'false'));
        $this->enabled = in_array($enabledValue, ['1', 'true', 'yes', 'on'], true);
        $this->endpoint = getenv('TEXTLK_SMS_ENDPOINT') ?: 'https://app.text.lk/api/http/sms/send';
    }

    public function sendSms($recipient, $message, $scheduleTime = null) {
        if (!$this->enabled) {
            return [
                'status' => 'skipped',
                'message' => 'SMS sending skipped because SMS_ENABLED is false.'
            ];
        }

        if ($this->apiToken === '' || $this->senderId === '') {
            return [
                'status' => 'error',
                'message' => 'SMS configuration missing: TEXTLK_API_TOKEN or TEXTLK_SENDER_ID not set.'
            ];
        }

        $normalizedRecipient = $this->normalizeRecipient((string)$recipient);
        if ($normalizedRecipient === '') {
            return [
                'status' => 'error',
                'message' => 'Invalid recipient mobile number for SMS.'
            ];
        }

        $trimmedMessage = trim((string)$message);
        if ($trimmedMessage === '') {
            return [
                'status' => 'error',
                'message' => 'SMS message content cannot be empty.'
            ];
        }

        $payload = [
            'api_token' => $this->apiToken,
            'recipient' => $normalizedRecipient,
            'sender_id' => $this->senderId,
            'type' => 'plain',
            'message' => $trimmedMessage,
        ];

        if ($scheduleTime !== null && trim((string)$scheduleTime) !== '') {
            $payload['schedule_time'] = trim((string)$scheduleTime);
        }

        $ch = curl_init($this->endpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json',
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
                'message' => 'SMS sending failed (cURL error: ' . $error . ')'
            ];
        }

        $respData = json_decode((string)$response, true);
        if ($httpCode >= 200 && $httpCode < 300 && isset($respData['status']) && strtolower((string)$respData['status']) === 'success') {
            return [
                'status' => 'success',
                'message' => 'SMS sent successfully.',
                'providerResponse' => $respData['data'] ?? null,
            ];
        }

        $errorMsg = $respData['message'] ?? 'Unknown Text.lk error';
        return [
            'status' => 'error',
            'message' => 'SMS sending failed. Text.lk error: ' . $errorMsg,
        ];
    }

    public function sendAppointmentBookedSms($phoneNumber, $recipientName, $payload) {
        $appointment = $this->extractAppointmentData($payload);

        $appointmentId = (int)($appointment['appointment_id'] ?? 0);
        $appointmentDate = (string)($appointment['appointment_date'] ?? 'N/A');
        $appointmentTime = (string)($appointment['appointment_time'] ?? 'N/A');

        $recipientLabel = trim((string)$recipientName);
        if ($recipientLabel === '') {
            $recipientLabel = 'Patient';
        }

        $message = sprintf(
            'Hi %s, your LabSync appointment #%d is confirmed for %s at %s.',
            $recipientLabel,
            $appointmentId,
            $appointmentDate,
            $appointmentTime
        );

        return $this->sendSms($phoneNumber, $message);
    }

    private function extractAppointmentData($payload) {
        if (is_array($payload) && isset($payload['appointment']) && is_array($payload['appointment'])) {
            return $payload['appointment'];
        }

        return is_array($payload) ? $payload : [];
    }

    private function normalizeRecipient($value) {
        $digits = preg_replace('/\D+/', '', $value);
        if ($digits === null) {
            return '';
        }

        if (strlen($digits) === 10 && strpos($digits, '0') === 0) {
            return '94' . substr($digits, 1);
        }

        if (strlen($digits) === 9) {
            return '94' . $digits;
        }

        if (strlen($digits) >= 10 && strlen($digits) <= 15) {
            return $digits;
        }

        return '';
    }
}
