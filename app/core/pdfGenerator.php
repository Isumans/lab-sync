<?php
/**
 * PdfGenerator — generates laboratory report PDFs using mPDF.
 *
 * Usage:
 *   require_once __DIR__ . '/pdfGenerator.php';
 *   $gen = new PdfGenerator(connect());
 *   $result = $gen->generateReport($appointmentId, $testId, $generatedByUserId);
 *   // $result = ['report_id' => 5, 'pdf_relative_path' => '2026/04/report_...pdf', 'pdf_url' => '/lab_sync/public/reports/pdfs/...']
 */

class PdfGenerator
{
    private $db;
    private $lastError = '';

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Main entry point — gathers data, builds HTML, renders PDF, saves file + DB row.
     *
     * @param  int $appointmentId
     * @param  int $testId
     * @param  int $generatedBy   user_id of the technician
     * @return array|false        ['report_id', 'pdf_relative_path', 'pdf_url'] or false
     */
    public function generateReport($appointmentId, $testId, $generatedBy)
    {
        $this->lastError = '';
        $appointmentId = intval($appointmentId);
        $testId        = intval($testId);
        $generatedBy   = intval($generatedBy);

        if ($appointmentId <= 0 || $testId <= 0) {
            $this->lastError = 'Invalid appointment or test ID.';
            return false;
        }

        // ---- 1. gather all data -------------------------------------------
        $payload = $this->gatherPayload($appointmentId, $testId, $generatedBy);
        if ($payload === null) {
            return false;
        }

        // ---- 2. insert / update reports table ----------------------------
        $timestamp   = date('Ymd_His');
        $referenceNo = 'REF-' . $appointmentId . '-' . $testId . '-' . $timestamp;
        $now         = date('Y-m-d H:i:s');

        $reportId = $this->upsertReportRow([
            'appointment_id'    => $appointmentId,
            'test_id'           => $testId,
            'reference_number'  => $referenceNo,
            'pdf_relative_path' => null,
            'pdf_original_name' => null,
            'pdf_file_size'     => null,
            'pdf_generated_at'  => $now,
            'pdf_generated_by'  => $generatedBy,
            'report_datetime'   => $now,
            'status'            => 'AUTHORIZED',
        ]);

        if ($reportId === false) {
            return false;
        }

        return [
            'report_id'        => $reportId,
            'reference_number' => $referenceNo,
        ];
    }

    /**
     * Build and return the full HTML report document for browser rendering.
     * Does not write to the database or disk.
     */
    public function getReportHtml($appointmentId, $testId, $generatedBy = 0)
    {
        $payload = $this->gatherPayload(intval($appointmentId), intval($testId), intval($generatedBy));
        if ($payload === null) {
            return null;
        }
        return $this->buildHtml($payload);
    }

    /* ====================================================================
     * DATA GATHERING
     * ==================================================================== */

    private function gatherPayload($appointmentId, $testId, $generatedBy)
    {
        $patient     = $this->getPatientInfo($appointmentId);
        $appointment = $this->getAppointmentInfo($appointmentId);
        $lab         = $this->getLabConfig();
        $results     = $this->getTestResults($appointmentId, $testId);
        $testMeta    = $this->getTestMeta($testId);
        $techName    = $this->getUserName($generatedBy);
        $authInfo    = $this->getAuthorizationInfo($appointmentId, $testId);
        $remarks     = $this->getTechnicianRemarks($appointmentId, $testId);

        if ($patient === null || $appointment === null) {
            $this->lastError = $this->lastError ?: 'Patient or appointment data not found.';
            return null;
        }

        return [
            'patient'     => $patient,
            'appointment' => $appointment,
            'lab'         => $lab,
            'results'     => $results,
            'test'        => $testMeta,
            'techName'    => $techName,
            'authInfo'    => $authInfo,
            'remarks'     => $remarks,
        ];
    }

    private function getPatientInfo($appointmentId)
    {
        $sql = "
            SELECT
                p.patient_id,
                COALESCE(p.uhid, '') AS uhid,
                COALESCE(p.patient_name, '') AS patient_name,
                p.date_of_birth,
                COALESCE(p.gender, '') AS gender,
                COALESCE(p.contact_number, '') AS contact_number,
                COALESCE(p.email, '') AS email
            FROM appointment a
            JOIN patients p ON p.patient_id = a.patient_id
            WHERE a.appointment_id = ?
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) { $this->lastError = 'getPatientInfo prepare: ' . $this->db->error; return null; }
        $stmt->bind_param('i', $appointmentId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    private function getAppointmentInfo($appointmentId)
    {
        $sql = "
            SELECT
                a.appointment_id,
                a.appointment_date,
                a.appointment_time,
                COALESCE(a.referred_by, '') AS referred_by,
                a.sample_datetime
            FROM appointment a
            WHERE a.appointment_id = ?
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) { $this->lastError = 'getAppointmentInfo prepare: ' . $this->db->error; return null; }
        $stmt->bind_param('i', $appointmentId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    private function getLabConfig()
    {
        $sql = "SELECT * FROM lab_configuration LIMIT 1";
        $result = $this->db->query($sql);
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        // fallback
        return [
            'lab_name'      => 'Laboratory',
            'accreditation' => '',
            'address'       => '',
            'phone'         => '',
            'email'         => '',
            'logo_path'     => '',
        ];
    }

    private function getTestMeta($testId)
    {
        $sql = "
            SELECT test_id, test_name, print_name, department, default_unit, methodology, default_comment
            FROM tests
            WHERE test_id = ?
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return ['test_name' => 'Unknown Test', 'print_name' => ''];
        $stmt->bind_param('i', $testId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        return $row ?: ['test_name' => 'Unknown Test', 'print_name' => ''];
    }

    private function getTestResults($appointmentId, $testId)
    {
        $sql = "
            SELECT
                tr.result_id,
                tr.unit_id,
                tr.measured_value,
                COALESCE(tr.flag, 'N') AS flag,
                tu.value_name,
                tu.unit_name,
                tu.unit_index,
                trr.ref_min,
                trr.ref_max,
                COALESCE(trr.range_label, '') AS range_label
            FROM test_results tr
            JOIN test_units tu ON tu.unit_id = tr.unit_id
            LEFT JOIN test_reference_ranges trr ON trr.unit_id = tu.unit_id
                AND trr.range_index = 0
            WHERE tr.appointment_id = ? AND tr.test_id = ?
            ORDER BY tu.unit_index ASC
        ";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param('ii', $appointmentId, $testId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    private function getUserName($userId)
    {
        if ($userId <= 0) return 'System';
        $sql = "SELECT username FROM users WHERE user_id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return 'Unknown';
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        return $row ? $row['username'] : 'Unknown';
    }

    private function getAuthorizationInfo($appointmentId, $testId)
    {
        $sql = "
            SELECT
                at.authorized_by,
                at.authorized_at,
                COALESCE(u.username, '') AS authorized_username
            FROM appointment_tests at
            LEFT JOIN users u ON u.user_id = at.authorized_by
            WHERE at.appointment_id = ? AND at.test_id = ?
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return ['authorized_by' => null, 'authorized_at' => null, 'authorized_username' => ''];
        $stmt->bind_param('ii', $appointmentId, $testId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        return $row ?: ['authorized_by' => null, 'authorized_at' => null, 'authorized_username' => ''];
    }

    private function getTechnicianRemarks($appointmentId, $testId)
    {
        $sql = "
            SELECT tc.comment_text
            FROM test_comments tc
            INNER JOIN test_results tr ON tr.result_id = tc.result_id
            WHERE tr.appointment_id = ? AND tr.test_id = ?
            ORDER BY tc.display_order ASC, tc.comment_id ASC
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return '';
        $stmt->bind_param('ii', $appointmentId, $testId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        return $row ? trim((string) $row['comment_text']) : '';
    }

    /* ====================================================================
     * DATABASE — upsert report row
     * ==================================================================== */

    private function upsertReportRow($data)
    {
        // Check for existing report
        $checkSql = "SELECT report_id FROM reports WHERE appointment_id = ? AND test_id = ? LIMIT 1";
        $checkStmt = $this->db->prepare($checkSql);
        if (!$checkStmt) {
            $this->lastError = 'upsertReportRow check prepare: ' . $this->db->error;
            return false;
        }
        $checkStmt->bind_param('ii', $data['appointment_id'], $data['test_id']);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $existing = $checkResult ? $checkResult->fetch_assoc() : null;

        if ($existing) {
            // UPDATE
            $updateSql = "
                UPDATE reports SET
                    reference_number   = ?,
                    pdf_relative_path  = ?,
                    pdf_original_name  = ?,
                    pdf_file_size      = ?,
                    pdf_generated_at   = ?,
                    pdf_generated_by   = ?,
                    report_datetime    = ?,
                    status             = ?
                WHERE report_id = ?
            ";
            $updateStmt = $this->db->prepare($updateSql);
            if (!$updateStmt) {
                $this->lastError = 'upsertReportRow update prepare: ' . $this->db->error;
                return false;
            }
            $updateStmt->bind_param(
                'ssssisssi',
                $data['reference_number'],
                $data['pdf_relative_path'],
                $data['pdf_original_name'],
                $data['pdf_file_size'],
                $data['pdf_generated_at'],
                $data['pdf_generated_by'],
                $data['report_datetime'],
                $data['status'],
                $existing['report_id']
            );
            if (!$updateStmt->execute()) {
                $this->lastError = 'upsertReportRow update exec: ' . $updateStmt->error;
                return false;
            }
            return intval($existing['report_id']);
        }

        // INSERT
        $insertSql = "
            INSERT INTO reports
                (appointment_id, test_id, reference_number, pdf_relative_path, pdf_original_name,
                 pdf_file_size, pdf_generated_at, pdf_generated_by, report_datetime, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $insertStmt = $this->db->prepare($insertSql);
        if (!$insertStmt) {
            $this->lastError = 'upsertReportRow insert prepare: ' . $this->db->error;
            return false;
        }
        $insertStmt->bind_param(
            'iisssissss',
            $data['appointment_id'],
            $data['test_id'],
            $data['reference_number'],
            $data['pdf_relative_path'],
            $data['pdf_original_name'],
            $data['pdf_file_size'],
            $data['pdf_generated_at'],
            $data['pdf_generated_by'],
            $data['report_datetime'],
            $data['status']
        );
        if (!$insertStmt->execute()) {
            $this->lastError = 'upsertReportRow insert exec: ' . $insertStmt->error;
            return false;
        }
        return intval($this->db->insert_id);
    }

    /* ====================================================================
     * HTML TEMPLATE
     * ==================================================================== */

    private function buildHtml($payload)
    {
        $patient     = $payload['patient'];
        $appointment = $payload['appointment'];
        $lab         = $payload['lab'];
        $results     = $payload['results'];
        $test        = $payload['test'];
        $authInfo    = $payload['authInfo'];
        $remarks     = $payload['remarks'] ?? '';

        $labName      = htmlspecialchars($lab['lab_name'] ?? 'Laboratory');
        $labAddress   = htmlspecialchars($lab['address'] ?? '');
        $labPhone     = htmlspecialchars($lab['phone'] ?? '');
        $labEmail     = htmlspecialchars($lab['email'] ?? '');
        $accreditation= htmlspecialchars($lab['accreditation'] ?? '');

        $patientName  = htmlspecialchars($patient['patient_name'] ?? '');
        $uhid         = htmlspecialchars($patient['uhid'] ?? 'N/A');
        $gender       = htmlspecialchars($patient['gender'] ?? '');
        $dob          = $patient['date_of_birth'] ?? '';
        $contact      = htmlspecialchars($patient['contact_number'] ?? '');

        // Calculate age
        $age = '';
        if ($dob && $dob !== '0000-00-00') {
            $birthDate = new DateTime($dob);
            $now = new DateTime();
            $age = $birthDate->diff($now)->y . ' Y';
        }
        $ageGender = trim($age . '/' . substr($gender, 0, 1));

        $referredBy   = htmlspecialchars($appointment['referred_by'] ?? '');
        $appointDate  = $appointment['appointment_date'] ?? '';
        $appointTime  = $appointment['appointment_time'] ?? '';
        $sampleDt     = $appointment['sample_datetime'] ?? '';
        if (!$sampleDt && $appointDate) {
            $sampleDt = $appointDate . ' ' . $appointTime;
        }
        $reportDt     = date('d/m/Y H:i');

        // Format sample datetime
        $sampleFormatted = '';
        if ($sampleDt) {
            try {
                $dt = new DateTime($sampleDt);
                $sampleFormatted = $dt->format('d/m/Y H:i');
            } catch (Exception $e) {
                $sampleFormatted = $sampleDt;
            }
        }

        $testPrintName = htmlspecialchars($test['print_name'] ?? $test['test_name'] ?? 'Test');
        $refNo = 'APP-' . str_pad($appointment['appointment_id'], 4, '0', STR_PAD_LEFT);

        $authorizedUsername = htmlspecialchars($authInfo['authorized_username'] ?? '');
        $authorizedAt = '';
        if (!empty($authInfo['authorized_at'])) {
            try {
                $dt = new DateTime($authInfo['authorized_at']);
                $authorizedAt = $dt->format('d/m/Y H:i');
            } catch (Exception $e) {
                $authorizedAt = $authInfo['authorized_at'];
            }
        }

        // Logo — use a web-accessible URL so the browser can load it
        $logoHtml = '';
        $logoPath = $lab['logo_path'] ?? '';
        if ($logoPath) {
            $logoSrc  = '/lab_sync/public/uploads/' . basename($logoPath);
            $logoHtml = '<img src="' . htmlspecialchars($logoSrc) . '" style="max-height:60px; max-width:120px;" alt="Lab Logo">';
        }

        // Build results rows
        $resultsHtml = '';
        $rowIndex = 0;
        foreach ($results as $r) {
            $rowClass = ($rowIndex % 2 === 0) ? 'even-row' : 'odd-row';
            $valueName = htmlspecialchars($r['value_name'] ?? '');
            $measuredValue = $r['measured_value'] !== null ? number_format((float)$r['measured_value'], 2) : '-';
            $unitName = htmlspecialchars($r['unit_name'] ?? '');
            $flag = strtoupper(trim($r['flag'] ?? 'N'));

            // Reference range text
            $refMin = $r['ref_min'];
            $refMax = $r['ref_max'];
            $refText = '';
            if ($refMin !== null && $refMax !== null) {
                $refText = number_format((float)$refMin, 1) . ' - ' . number_format((float)$refMax, 1);
            } elseif ($refMin !== null) {
                $refText = '>= ' . number_format((float)$refMin, 1);
            } elseif ($refMax !== null) {
                $refText = '<= ' . number_format((float)$refMax, 1);
            }

            // Flag styling
            $flagDisplay = '';
            $flagStyle = '';
            $valueStyle = '';
            if ($flag === 'H') {
                $flagDisplay = 'H';
                $flagStyle = 'color: #dc3545; font-weight: bold;';
                $valueStyle = 'color: #dc3545; font-weight: bold;';
            } elseif ($flag === 'L') {
                $flagDisplay = 'L';
                $flagStyle = 'color: #0d6efd; font-weight: bold;';
                $valueStyle = 'color: #0d6efd; font-weight: bold;';
            } else {
                $flagDisplay = '-';
                $flagStyle = 'color: #198754;';
                $valueStyle = '';
            }

            $resultsHtml .= "
                <tr class=\"{$rowClass}\">
                    <td style=\"padding: 6px 8px; border: 1px solid #dee2e6; text-align: left;\">{$valueName}</td>
                    <td style=\"padding: 6px 8px; border: 1px solid #dee2e6; text-align: center; {$valueStyle}\">{$measuredValue}</td>
                    <td style=\"padding: 6px 8px; border: 1px solid #dee2e6; text-align: center;\">{$unitName}</td>
                    <td style=\"padding: 6px 8px; border: 1px solid #dee2e6; text-align: center;\">{$refText}</td>
                    <td style=\"padding: 6px 8px; border: 1px solid #dee2e6; text-align: center; {$flagStyle}\">{$flagDisplay}</td>
                </tr>
            ";
            $rowIndex++;
        }

        if (empty($results)) {
            $resultsHtml = '<tr><td colspan="5" style="padding:12px; text-align:center; color:#999;">No test results available.</td></tr>';
        }

        $remarksHtml = '';
        if ($remarks !== '') {
            $escapedRemarks = nl2br(htmlspecialchars($remarks));
            $remarksHtml = <<<REMARKS
    <!-- TECHNICIAN COMMENTS -->
    <div class="section-header">COMMENTS BY MLT TECHNICIAN</div>
    <div style="border:1px solid #dee2e6; border-top:none; padding:10px 15px; font-size:9.5pt; color:#212529; background:#fff; min-height:40px;">{$escapedRemarks}</div>
REMARKS;
        }

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laboratory Report &mdash; {$testPrintName}</title>
<style>
    @page { size: A4; margin: 10mm; }
    @media print {
        .no-print { display: none !important; }
        body { background: #fff; padding: 0; }
        .report-container { box-shadow: none; margin: 0; padding: 0; max-width: 100%; }
    }
    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10.5pt;
        color: #212529;
        margin: 0;
        padding: 20px 0 40px;
        background: #e8e8e8;
    }
    .report-container {
        max-width: 794px;
        margin: 0 auto;
        background: #fff;
        box-shadow: 0 2px 12px rgba(0,0,0,0.18);
    }
    .header-table {
        width: 100%;
        border-collapse: collapse;
        background: linear-gradient(135deg, #1a3a5c 0%, #2c5f8a 100%);
        color: #ffffff;
        margin-bottom: 0;
    }
    .header-table td {
        padding: 12px 15px;
        vertical-align: middle;
    }
    .lab-name {
        font-size: 18pt;
        font-weight: bold;
        letter-spacing: 0.5px;
    }
    .lab-subtitle {
        font-size: 8.5pt;
        color: #ccd8e8;
        margin-top: 3px;
    }
    .confidential-badge {
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.3);
        border-radius: 3px;
        padding: 4px 10px;
        font-size: 8pt;
        font-weight: bold;
        letter-spacing: 1.5px;
        text-align: center;
        display: inline-block;
    }
    .report-title-bar {
        background: #e8eef5;
        padding: 6px 15px;
        font-size: 11pt;
        font-weight: bold;
        color: #1a3a5c;
        text-align: center;
        border-bottom: 2px solid #1a3a5c;
        letter-spacing: 0.5px;
    }
    .patient-info-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
        border: 1px solid #dee2e6;
        border-top: none;
    }
    .patient-info-table td {
        padding: 5px 15px;
        font-size: 9.5pt;
        border-bottom: 1px solid #eee;
    }
    .patient-info-table .label {
        color: #6c757d;
        font-weight: 600;
        width: 120px;
    }
    .patient-info-table .value {
        color: #212529;
        font-weight: normal;
    }
    .section-header {
        background: #1a3a5c;
        color: #ffffff;
        padding: 6px 15px;
        font-size: 10pt;
        font-weight: bold;
        letter-spacing: 0.5px;
        margin-top: 10px;
    }
    .results-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }
    .results-table th {
        background: #2c5f8a;
        color: #ffffff;
        padding: 7px 8px;
        font-size: 9.5pt;
        font-weight: bold;
        text-align: center;
        border: 1px solid #1a3a5c;
    }
    .results-table th:first-child {
        text-align: left;
    }
    .even-row {
        background: #ffffff;
    }
    .odd-row {
        background: #f8f9fa;
    }
    .footer-section {
        margin-top: 20px;
        border-top: 2px solid #1a3a5c;
        padding-top: 10px;
    }
    .footer-table {
        width: 100%;
        border-collapse: collapse;
    }
    .footer-table td {
        padding: 3px 0;
        font-size: 9pt;
        vertical-align: top;
    }
    .signature-line {
        border-bottom: 1px solid #333;
        width: 200px;
        display: inline-block;
        margin-top: 20px;
    }
    .stamp-box {
        border: 1px dashed #adb5bd;
        padding: 8px;
        text-align: center;
        font-size: 8pt;
        color: #6c757d;
        margin-top: 10px;
    }
    .disclaimer {
        font-size: 7.5pt;
        color: #999;
        text-align: center;
        margin-top: 15px;
        padding-top: 8px;
        border-top: 1px solid #eee;
    }
</style>
</head>
<body>
<div class="no-print" style="max-width:794px; margin:0 auto 12px; text-align:right;">
    <button onclick="window.print()" style="padding:6px 16px; background:#1a3a5c; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:13px;">Print / Save as PDF</button>
</div>
<div class="report-container">

    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td style="width: 80px;">{$logoHtml}</td>
            <td>
                <div class="lab-name">{$labName}</div>
                <div class="lab-subtitle">{$labAddress}</div>
                <div class="lab-subtitle">Tel: {$labPhone} | Email: {$labEmail} | Accreditation: {$accreditation}</div>
            </td>
            <td style="text-align:right; width:160px;">
                <div class="confidential-badge">CONFIDENTIAL</div>
            </td>
        </tr>
    </table>

    <!-- REPORT TYPE BAR -->
    <div class="report-title-bar">LABORATORY REPORT &mdash; {$testPrintName}</div>

    <!-- PATIENT INFO -->
    <table class="patient-info-table">
        <tr>
            <td class="label">Patient Name</td>
            <td class="value">{$patientName}</td>
            <td class="label">UHID</td>
            <td class="value">{$uhid}</td>
        </tr>
        <tr>
            <td class="label">Age / Sex</td>
            <td class="value">{$ageGender}</td>
            <td class="label">Reference No</td>
            <td class="value">{$refNo}</td>
        </tr>
        <tr>
            <td class="label">Referred By</td>
            <td class="value">{$referredBy}</td>
            <td class="label">Contact</td>
            <td class="value">{$contact}</td>
        </tr>
        <tr>
            <td class="label">Sample Date</td>
            <td class="value">{$sampleFormatted}</td>
            <td class="label">Report Date</td>
            <td class="value">{$reportDt}</td>
        </tr>
    </table>

    <!-- TEST RESULTS -->
    <div class="section-header">TEST RESULTS</div>
    <table class="results-table">
        <thead>
            <tr>
                <th style="text-align:left; width:35%;">Test Parameter</th>
                <th style="width:18%;">Result</th>
                <th style="width:12%;">Unit</th>
                <th style="width:22%;">Reference Range</th>
                <th style="width:8%;">Flag</th>
            </tr>
        </thead>
        <tbody>
            {$resultsHtml}
        </tbody>
    </table>

    {$remarksHtml}

    <!-- AUTHORIZATION FOOTER -->
    <div class="footer-section">
        <table class="footer-table">
            <tr>
                <td style="width:50%;">
                    <strong>Authorized By:</strong> {$authorizedUsername}<br>
                    <strong>Date:</strong> {$authorizedAt}
                </td>
                <td style="width:50%; text-align:right;">
                    <div style="margin-top:5px;">
                        <span class="signature-line">&nbsp;</span>
                    </div>
                    <div style="font-size:8pt; color:#6c757d; margin-top:3px;">Authorized Signature</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- DISCLAIMER -->
    <div class="disclaimer">
        This report is strictly confidential and intended solely for the use of the patient and the referring physician.<br>
        Results are valid only for the specimen received. This is a computer-generated report.
    </div>

</div>
</body>
</html>
HTML;

        return $html;
    }
}
