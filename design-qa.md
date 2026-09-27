# Report design 3 verification

Date: 2026-09-27

Selected reference: the third displayed concept, `exec-90685202-7efb-41fc-bc82-588c1643622b.png`.

## Scope

Updated the invoice report, default invoice and estimate templates, receipt,
bank report, expense report, profit-and-loss report, and sales-by-item report.
Customer ledger and sales-by-customer designs are preserved; their paper size is
now explicit. The A4 audit also covers invoice/estimate alternatives and the packing slip.

## Visual review

- Compared the reference and live invoice preview together. Reference canvas is
  1523 x 1032; desktop preview is 1414 x 958 with the same aspect ratio. Compared
  document regions proportionally rather than treating image density as drift.
- Typography: bold navy title and party, muted metadata labels, readable table
  headings, and emphasized totals. DejaVu Sans in PDF and the browser sans-serif
  fallback retain text and rupee glyphs without a network font dependency.
- Layout: split title/party and metadata, bordered item grid, aligned summary,
  side-by-side words and remarks, declaration/signature block, small footnote.
- Colors: navy `#203746`, light table header `#edf1f5`, teal totals `#e6f3f3`,
  and white paper. No bitmap assets are required by this design.
- Intentional adaptations: numbers align right for financial readability;
  A4 PDF type is smaller than the screen preview; summary reports retain their
  own labels and hierarchy instead of gaining invoice-only fields.
- Mobile preview checked at 390 x 844. Header stacks; all table headings fit;
  no horizontal page overflow. Desktop viewport restored afterward.
- Rendered all eight PDFs with Dompdf and inspected rasterized pages. A 65-item
  invoice spans five A4 pages with repeated column headings; all 65 descriptions,
  additions, deductions, final total, amount in words, and footer remain present.
  Footer is in normal flow, so it cannot overlap long item tables.
- No browser console errors or warnings on the preview.

## Content and checks

- Compared rendered text/value token counts against the pre-change templates
  for all eight outputs: no text or data removed or changed. The old estimate
  template's unterminated HTML attribute was normalized only for parsing the
  baseline comparison.
- PHPUnit: ReportDesignTest and InvoicePrintPreviewTest, 5 tests, 82 assertions.
- A4 follow-up: shared page rules explicitly select 210 x 297 mm portrait,
  even with a Letter server default. First-page margins are 12 mm; continuation
  pages reserve 22 mm at the top for repeated table headings. Desktop preview
  is an A4 sheet (793.69 x 1122.52 CSS pixels) with the same content typography
  as the PDF. Browser printing excludes controls and the screen-only sheet frame.
  All eight PDF dimensions and the five-page invoice were checked again.
- Complete paper-size audit: all 15 full PDF templates render as A4, including
  the four alternative invoice/estimate templates, sales-by-customer, customer
  ledger, and packing slip. Rendering tests start with Letter to prove the
  template rules select A4 independently of the server default.
- Converted the packing slip from 4 x 4 inches to A4 portrait with 12 mm margins.
  The customer ledger retains its existing A4 landscape orientation and design;
  the other 14 templates use portrait. No labels or business values changed.
- Corrected oversized totals tables in alternative invoice/estimate templates
  and off-page metadata positioning in template 3. Rendered samples were
  inspected after these changes. PDF text-coordinate checks found no text
  outside any page in all 15 samples or the five-page invoice; every page's
  dimensions are 595.28 x 841.89 points (reversed for the customer ledger).
- Production frontend build passed; existing calculator eval/build-size warnings
  are unrelated to these server-rendered templates.
- `git diff --check` passed.
- Temporary PDFs, screenshots, and comparison logs are outside the repository
  under the system temporary directory, `omtbiz-report-design-3`.
- No business records created or changed; local changes only, no deployment.

No unresolved P0/P1/P2 design issues found.

final result: passed
