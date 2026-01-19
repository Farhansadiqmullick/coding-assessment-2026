Sourcetop Inc. – Coding Assessment Submission Answers

1. Technical Approach

Which critical bug did you find most challenging to fix, and why?

The most challenging issue was handling corrupted JSON invoice data, particularly cases where the items array was opened but not properly closed before subsequent fields (e.g., discount). This caused json_decode() to fail silently and resulted in data being overwritten instead of appended.

The challenge was ensuring the fix was safe and deterministic—repairing only the known corruption pattern without risking valid data or introducing unintended side effects.


What was your debugging process for identifying the root causes?

I followed a structured debugging approach:
	•	Reproduced the issue using existing test data
	•	Logged raw file contents before and after save operations
	•	Verified json_last_error() to confirm decode failures
	•	Isolated the exact malformed pattern in the JSON file
	•	Implemented a targeted regex-based repair only for that known issue

This allowed me to fix the root cause while keeping the solution minimal and controlled.


2. Feature Implementation

Which incomplete feature(s) did you choose to implement, and why?

I implemented PDF generation and dynamic tax calculation.

PDF generation was a natural choice because it:
	•	Was explicitly marked as incomplete
	•	Added real output value to the system
	•	Allowed verification via both code and generated artifacts

Dynamic tax calculation was implemented because the hardcoded tax logic violated the requirement that tax rates change frequently and should be configuration-driven.


If you implemented PDF generation, which library did you choose and what influenced your decision?

I chose Dompdf.

The decision was based on:
	•	Native HTML-to-PDF support (ideal for invoices)
	•	Simple API and fast integration
	•	No manual layout or coordinate calculations
	•	Easy testing and debugging during development

Dompdf allowed me to reuse the HTML invoice structure and reliably generate PDFs at save-time.


3. Code Quality

What improvements did you make to the codebase beyond the required fixes?

Beyond fixing the required bugs, I:
	•	Added input validation for items, discounts, and tax calculations
	•	Replaced timestamp-based invoice IDs with UUIDs via Composer
	•	Externalized tax rates into tax_rates.json
	•	Improved file handling with safe appends and locking
	•	Added repair logging for corrupted JSON cases
	•	Clarified intent with inline comments and documentation

These changes improve reliability, maintainability, and correctness without altering the system’s scope.


Are there any areas of the code you would refactor further given more time?

Yes. Given more time, I would:
	•	Introduce clearer separation between domain logic and persistence
	•	Extract shared interfaces or DTOs for invoice rendering
	•	Add stricter typing and stronger boundaries between layers
	•	Replace ad-hoc file storage with a small persistence abstraction

These refactors would further improve scalability and testability.


4. Testing

Describe your approach to testing your changes.

I focused on ensuring that existing tests passed first, then validated behavior through targeted tests and real output verification.

I used php run_tests.php continuously during development to confirm:
	•	No regressions were introduced
	•	Previously failing tests were resolved correctly


Did you add any new tests? If so, what do they cover?

Yes, I added a PDF generation test that:
	•	Generates a PDF from the latest invoice data
	•	Confirms the file is created successfully
	•	Ensures the PDF generation process works end-to-end

This test verifies real output rather than only internal state.


5. Technical Decisions

What assumptions did you make while completing this assessment?

Some key assumptions:
	•	JSON corruption followed a known and repeatable pattern
	•	Persisted invoice data should be treated as immutable for rendering
	•	PDF generation should reflect stored data, not recomputed values
	•	Tests should validate observable behavior rather than internal implementation

For PDF testing, I intentionally generated the PDF from the last invoice in the JSON file to confirm that persisted data produces correct output.


If you encountered any blockers or unclear requirements, how did you resolve them?

Where requirements were unclear (e.g., how aggressively to repair corrupted JSON), I:
	•	Chose the least risky deterministic solution
	•	Documented assumptions clearly in code and comments

This ensured correctness without overengineering.


6. Time Management

Approximately how much time did you spend on this assessment?

Approximately 4 hours.

This included:
	•	Understanding the codebase
	•	Debugging existing issues
	•	Implementing missing features
	•	Writing tests and documentation


If you had an additional 2 hours, what would you prioritize?

With 2 more hours, I would focus on:
	1.	Better architecture / maintainability
	    •	Extract file operations into a repository/service class.
	    •	Add stronger typing and clearer exception boundaries.
	2.	More test coverage
	    •	Add tests for invalid inputs (negative qty, invalid discount, missing tax region).
	    •	Add tests for JSON repair behavior (corrupted JSON → repaired → load works).
	3.	Improved documentation
	    •	A short developer guide explaining configuration (tax rates, PDF output directory, and repair logs).