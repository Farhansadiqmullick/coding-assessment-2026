# Invoice Management System

This project implements a basic Invoice Management System with support for:

- Invoice creation and item management
- Discount application
- Tax calculation from configuration
- JSON persistence with basic repair handling
- PDF generation using Composer libraries
- Basic test coverage via a custom test runner

## 🧪 Test Coverage Summary

The test suite is implemented in `InvoiceTest` and focuses on core functionality. Some tests are intentionally simple and highlight known issues or previously fixed bugs.

### Test Execution Flow

The following tests are run in order:

1. **Create Invoice**
2. **Calculate Total**
3. **Add Multiple Items**
4. **Save & Load Invoice**
5. **Tax Calculation**
6. **PDF Generation**

## ✅ Test Results Explained

### 1. `test_create_invoice` – PASS ✅
- Verifies invoice creation
- Confirms customer name is stored correctly

### 2. `test_calculate_total` – PASS ✅
- Adds a single item
- Confirms total calculation
- Uses qty consistently (previous bug fixed)

### 3. `test_add_multiple_items` – PASS ✅
- Adds multiple items
- Confirms aggregated total is correct

### 4. `test_save_and_load` – PASS ✅
- Saves multiple invoices to `invoices.json`
- Confirms invoices append instead of overwrite
- Loads invoice by ID correctly

#### Key Fix Applied
- JSON file append logic
- Repair handling for malformed JSON
- UUID-based invoice IDs (no collisions)

### 5. `test_tax_calculation` – PASS ✅
- Calculates tax dynamically from `tax_rates.json`
- Supports region-based tax rules (e.g., US-NY)
- Falls back to default rates when needed

```php
InvoiceCalculator::calculateTax(100, 'US-NY'); // returns 8.00

```
### 5. `Generates a PDF for the latest invoice` – PASS ✅
    
Uses Dompdf (installed via Composer)

Confirms PDF file is created successfully

###  `📄 PDF Generation` 

PDF Library

    Dompdf (installed via Composer)

    Converts HTML invoices to PDF

When PDF is Generated

    PDF is generated after saving an invoice

    Uses the saved invoice data (array) to avoid recalculation

🛠 JSON Repair Handling
Purpose

Handles known JSON corruption issues, specifically:

    Unclosed items arrays

    Missing commas before discount

Behavior

    Repairs JSON only if invalid

    Logs repair actions to:
    text

data/invoices.repair.log

    Never overwrites valid JSON


📌 Notes & Limitations
	•	Test coverage is intentionally minimal
	•	No PHPUnit dependency (custom test runner)
	•	No concurrency locking beyond file-level locks
	•	No tax caching (loaded per call)
	•	No PDF styling beyond basic layout
