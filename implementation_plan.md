# PDF Report Generation for Lab Sync

## Background

The existing Lab Sync system already has:
- An **authorization workflow** — technicians enter values → mark as ready → authorize via modal
- A `reports` table with `pdf_relative_path`, `pdf_generated_at`, `pdf_generated_by` columns
- A `lab_configuration` table with lab name, address, phone, email, logo
- Test results stored in `test_results` linked to `test_units` and `test_reference_ranges`
- One report row already exists for appointment 16 / test 29

The missing piece is the **actual mPDF-based PDF generation** triggered by authorization, plus the **receptionist dashboard** for viewing/printing/downloading reports.

---

## User Review Required

> [!IMPORTANT]
> **mPDF Installation Required**: No `composer.json` or `vendor/` exists yet. We'll need to run `composer require mpdf/mpdf` to install mPDF. This requires Composer to be installed on your system.

> [!IMPORTANT]
> **Database Migration**: A small ALTER TABLE is needed to add `test_id` to the `reports` table so each report is linked to a specific test within an appointment.

---

## Proposed Changes

### Component 1: Composer & mPDF Setup

#### [NEW] [composer.json](file:///c:/xampp/htdocs/lab_sync/composer.json)
- Initialize Composer project with `mpdf/mpdf` as dependency
- Run `composer require mpdf/mpdf` to install

---

### Component 2: Database Migration

#### [NEW] [add_test_id_to_reports.sql](file:///c:/xampp/htdocs/lab_sync/config/migrations/add_test_id_to_reports.sql)
- Add `test_id INT` column to `reports` table (if not already present)
- Add foreign key to `tests(test_id)`

```sql
ALTER TABLE reports ADD COLUMN test_id INT NULL AFTER appointment_id;
ALTER TABLE reports ADD KEY idx_reports_test (test_id);
```

---

### Component 3: PDF Generation Logic

#### [NEW] [pdfGenerator.php](file:///c:/xampp/htdocs/lab_sync/app/core/pdfGenerator.php)
Core PDF generation class using mPDF. This is the heart of the feature:

- **`generateReport($appointmentId, $testId, $generatedBy)`** — main entry point
  - Fetches patient info, appointment data, test results + reference ranges, lab config
  - Builds styled HTML template matching the specified layout (single-page A4)
  - Uses mPDF to render to PDF
  - Saves to `public/reports/pdfs/YYYY/MM/` directory structure
  - Updates `reports` table with path, file size, generated_at, status=AUTHORIZED
  - Returns the `report_id` and relative path

- **PDF HTML Template** features:
  - Lab header with logo (from `lab_configuration`), lab name, "CONFIDENTIAL LABORATORY REPORT"
  - Patient info section: Name, UHID, Age/Sex, DOB, Referred By, Sample/Report dates, Reference No
  - Results table with columns: Test Name | Result | Unit | Reference Range | Flag
  - Flag styling: H = red bold, L = blue bold, N = green
  - Alternating row colors, bordered table
  - Authorization footer with technician name, signature line, date
  - Font: Arial/Helvetica 10-11pt, A4, 10mm margins

---

### Component 4: Controller Updates

#### [MODIFY] [reportsController.php](file:///c:/xampp/htdocs/lab_sync/app/controllers/reportsController.php)

Add these new action methods:

1. **`generatePdf()`** — Called after authorization succeeds
   - Accepts POST `{ appointment_id, test_id }`
   - Calls `PdfGenerator::generateReport()`
   - Returns JSON `{ status, pdf_path, report_id }`

2. **`viewPdf()`** — Streams PDF inline for Chrome's native viewer
   - Accepts GET `?report_id=X` or `?appointment_id=X&test_id=Y`
   - Sets `Content-Type: application/pdf` + `Content-Disposition: inline`
   - Calls `readfile()` on the stored PDF

3. **`downloadPdf()`** — Forces browser download
   - Same lookup as viewPdf but `Content-Disposition: attachment`

4. **`receptionistDashboard()`** — Renders the receptionist report dashboard view

5. **`listAuthorizedReports()`** — JSON API for receptionist dashboard
   - Returns paginated list of AUTHORIZED/PRINTED reports
   - Search by UHID, patient name, reference number

---

### Component 5: Model Updates

#### [MODIFY] [reportModel.php](file:///c:/xampp/htdocs/lab_sync/app/models/reportModel.php)

Add these new methods:

1. **`getPdfReportPayload($appointmentId, $testId)`** — Gathers all data needed for PDF:
   - Patient info (name, UHID, age, gender, DOB, contact)
   - Appointment info (date, time, referred_by, sample_datetime)
   - Lab config (name, logo, address, phone, email, accreditation)  
   - All test results for this appointment+test with units and reference ranges
   - Authorization info (technician name, authorized_at)

2. **`saveReportRecord($data)`** — Creates/updates the `reports` row with PDF path

3. **`getReportByAppointmentTest($appointmentId, $testId)`** — Finds existing report row

4. **`getAuthorizedReportsList($filters, $page, $perPage)`** — For receptionist dashboard

5. **`countAuthorizedReports($filters)`** — Count for pagination

---

### Component 6: Frontend - Authorize Modal Enhancement

#### [MODIFY] [reportAuthorizeModal.js](file:///c:/xampp/htdocs/lab_sync/public/js/reportAuthorizeModal.js)

After successful authorization (`submitDecision('authorize')`):
1. Automatically call `generatePdf` endpoint
2. Show a loading spinner with "Generating PDF..."
3. On success, show a "View PDF" button that opens `viewPdf` in a new tab
4. Update the page to show the report is now authorized with PDF available

---

### Component 7: Report Details View Enhancement

#### [MODIFY] [report_details.php](file:///c:/xampp/htdocs/lab_sync/app/views/technicians/report_details.php)

For tests with status `AUTHORIZED`:
- Change "Create Report" button to show **two actions**:
  - "View PDF" — opens PDF inline in new tab
  - "Re-generate PDF" — regenerates if needed

---

### Component 8: Receptionist Dashboard

#### [NEW] [receptionist_reports.php](file:///c:/xampp/htdocs/lab_sync/app/views/receptionist/receptionist_reports.php)

A full-page dashboard for receptionists with:
- Search bar (by UHID, patient name, reference number)
- Filterable table of authorized reports showing:
  - Reference No | Patient Name | UHID | Test Name | Date | Status
- Action buttons per row:
  - 📄 **View PDF** — `window.open()` to view inline
  - ⬇️ **Download PDF** — triggers file download
  - 🖨️ **Print PDF** — opens in new tab with automatic `window.print()`
  - ✉️ **Send to Patient** — opens modal (placeholder for email/SMS)
- Pagination controls

#### [NEW] [receptionistReports.css](file:///c:/xampp/htdocs/lab_sync/public/receptionistReports.css)

Styling for the receptionist dashboard matching the existing design system:
- Dark theme consistent with existing CSS
- Card-based layout
- Status badges
- Responsive action buttons with icons

#### [NEW] [receptionistReports.js](file:///c:/xampp/htdocs/lab_sync/public/js/receptionistReports.js)

JavaScript for the receptionist dashboard:
- Fetch and render authorized reports list
- Search/filter functionality
- Handle View/Download/Print/Send actions
- Pagination controls

---

### Component 9: Router Updates

#### [MODIFY] [index.php](file:///c:/xampp/htdocs/lab_sync/index.php)

Add new routes under `reportsController`:
- `generatePdf` → `$reportsController->generatePdf()`
- `viewPdf` → `$reportsController->viewPdf()`
- `downloadPdf` → `$reportsController->downloadPdf()`
- `receptionistDashboard` → `$reportsController->receptionistDashboard()`
- `listAuthorizedReports` → `$reportsController->listAuthorizedReports()`

---

## File Summary

| File | Action | Purpose |
|------|--------|---------|
| `composer.json` | NEW | mPDF dependency |
| `config/migrations/add_test_id_to_reports.sql` | NEW | DB migration |
| `app/core/pdfGenerator.php` | NEW | PDF generation engine |
| `app/controllers/reportsController.php` | MODIFY | New endpoints |
| `app/models/reportModel.php` | MODIFY | New data methods |
| `app/views/technicians/report_details.php` | MODIFY | PDF buttons for authorized tests |
| `app/views/receptionist/receptionist_reports.php` | NEW | Receptionist dashboard view |
| `public/receptionistReports.css` | NEW | Dashboard styling |
| `public/js/receptionistReports.js` | NEW | Dashboard logic |
| `public/js/reportAuthorizeModal.js` | MODIFY | Auto-generate PDF on authorize |
| `index.php` | MODIFY | New routes |

---

## Open Questions

> [!IMPORTANT]
> **Composer**: Is Composer already installed on your system? We need it to install mPDF. If not, we can use a manual download approach.

> [!NOTE]
> **Lab Logo**: The `lab_configuration` table has a logo at `/lab_sync/public/uploads/lab_logo_1772551364.jpg`. This will be embedded in the PDF header. Is this correct?

> [!NOTE]  
> **"Send to Patient"**: For the initial implementation, shall we build a placeholder modal for the email/SMS send feature, or a fully working email sender using PHP `mail()`?

---

## Verification Plan

### Automated Tests
1. Run `composer install` to verify mPDF installs correctly
2. Execute the SQL migration against the database
3. Test PDF generation via browser: authorize a test and verify PDF is created in `public/reports/pdfs/`

### Manual Verification
1. Log in as **technician** → Navigate to Reports → Open a completed test → Authorize → Verify PDF auto-generates and "View PDF" button appears
2. Open generated PDF → Verify layout matches the specified design (header, patient info, results table, footer)
3. Log in as **receptionist** → Navigate to Reports → Verify dashboard shows authorized reports
4. Test View/Download/Print buttons work correctly
5. Verify PDF opens inline in Chrome's native PDF viewer
