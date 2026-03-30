{{-- Single Term Progress Report — spacing tuned for one A4 landscape page (scoped: body.term-progress-report) --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<style>
    /* Tight print margins — one page fit (does not alter header/table HTML) */
    @media print {
        @page {
            size: A4 landscape;
            margin: 5mm;
        }
        body.term-progress-report.emaaree-print-body {
            padding: 0 !important;
        }
        body.term-progress-report .emaaree-meta-dashboard,
        body.term-progress-report .emaaree-summary-bar-inner,
        body.term-progress-report .emaaree-navy-bar {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
    body.term-progress-report.emaaree-print-body {
        font-family: 'Inter', 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
        padding: 14px 20px 18px;
    }
    /* School name — less vertical space above/below (visual design unchanged) */
    body.term-progress-report .emaaree-school-title {
        font-weight: 800;
        letter-spacing: 0.03em;
        color: #0f172a;
        border-bottom: none;
        margin-top: 0;
        padding: 0 0 6px;
        text-decoration: underline;
        text-decoration-color: #002147;
        text-decoration-thickness: 2px;
        text-underline-offset: 5px;
    }
    body.term-progress-report .emaaree-navy-bar {
        font-weight: 500;
        letter-spacing: 0.06em;
        padding: 3px 12px;
        line-height: 1.32;
    }
    body.term-progress-report .emaaree-doc-title {
        margin: 8px 0 6px;
        font-weight: 600;
        color: #475569;
        letter-spacing: 0.18em;
    }
    /* Metadata — single line per side: tighter type + separators */
    body.term-progress-report .emaaree-meta-dashboard {
        margin: 0 0 14px;
        padding: 10px 14px;
        background: linear-gradient(180deg, #fafbfc 0%, #f4f6f8 100%);
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    body.term-progress-report .emaaree-meta-strip {
        display: flex;
        flex-wrap: nowrap;
        justify-content: space-between;
        align-items: center;
        gap: 10px 14px;
        width: 100%;
        font-size: 9.5pt;
        line-height: 1.35;
    }
    body.term-progress-report .emaaree-meta-left {
        flex: 1 1 0;
        min-width: 0;
        white-space: nowrap;
        text-align: left;
    }
    body.term-progress-report .emaaree-meta-right {
        flex: 0 1 auto;
        min-width: 0;
        white-space: nowrap;
        text-align: right;
    }
    body.term-progress-report .meta-k {
        font-weight: 700;
        color: #64748b;
        font-size: 9pt;
        letter-spacing: 0.01em;
    }
    body.term-progress-report .meta-v {
        font-weight: 400;
        color: #0f172a;
        letter-spacing: 0;
    }
    body.term-progress-report .meta-sep {
        display: inline-block;
        color: #cbd5e1;
        font-weight: 300;
        padding: 0 0.28em;
        user-select: none;
    }
    body.term-progress-report .emaaree-rank-final {
        color: #D32F2F;
        font-weight: 700;
    }
    /* Table — compact but readable */
    body.term-progress-report .emaaree-results-table {
        margin-top: 4px;
    }
    body.term-progress-report .emaaree-results-table tbody td {
        padding: 9px 7px;
    }
    body.term-progress-report .emaaree-results-table thead th {
        padding: 5px 7px;
    }
    body.term-progress-report .emaaree-slot-sub {
        margin-top: 1px;
    }
    /* Summary — closer to table */
    body.term-progress-report .emaaree-summary-bar {
        margin-top: 14px;
        padding-top: 0;
        border-top: none;
    }
    body.term-progress-report .emaaree-summary-bar-inner {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 18px 14px;
        max-width: 720px;
        margin-left: auto;
        margin-right: auto;
    }
    body.term-progress-report .emaaree-summary-title {
        color: #64748b;
        margin-bottom: 10px;
    }
    body.term-progress-report .emaaree-summary-metrics {
        gap: 22px 36px;
    }
    body.term-progress-report .emaaree-summary-metrics .sk {
        color: #64748b;
    }
    body.term-progress-report .emaaree-summary-metrics .sv {
        font-weight: 700;
        color: #0f172a;
    }
    /* Signature — tight to summary */
    body.term-progress-report .emaaree-sig-footer {
        margin-top: 14px;
    }
    body.term-progress-report .emaaree-sig-line {
        min-width: 140px;
        border-top: 1px solid #94a3b8;
        padding-top: 4px;
        font-weight: 600;
        font-size: 6pt;
        letter-spacing: 0.12em;
        color: #64748b;
    }
</style>
