{{-- Minimal print: wide margins, hairline rows, navy header strip --}}
<style>
    * { box-sizing: border-box; }
    body.emaaree-print-body {
        margin: 0;
        padding: 28px 36px 40px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'DejaVu Sans', sans-serif;
        font-size: 10pt;
        font-weight: 400;
        color: #1a1a1a;
        background: #fff;
        -webkit-font-smoothing: antialiased;
    }
    @media print {
        @page { size: A4 landscape; margin: 16mm 18mm; }
        body.emaaree-print-body { padding: 0; }
        body.emaaree-print-body,
        .emaaree-results-table thead th,
        .emaaree-navy-bar {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
    .emaaree-print-wrap { max-width: 100%; margin: 0 auto; }
    .emaaree-school-title {
        margin: 0;
        padding: 0 0 12px;
        font-size: 22pt;
        font-weight: 600;
        letter-spacing: 0.02em;
        text-align: center;
        color: #111;
        border-bottom: 2px solid #002147;
        line-height: 1.15;
    }
    .emaaree-navy-bar {
        background: #002147;
        color: #fff;
        text-align: center;
        font-weight: 500;
        font-size: 7.5pt;
        padding: 5px 16px;
        line-height: 1.45;
        letter-spacing: 0.04em;
    }
    .emaaree-doc-title {
        margin: 28px 0 20px;
        text-align: center;
        font-size: 9pt;
        font-weight: 500;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        color: #6b7280;
    }
    .emaaree-meta-lines {
        display: table;
        width: 100%;
        margin: 0 0 28px;
        font-size: 9.5pt;
        line-height: 1.65;
        color: #374151;
    }
    .emaaree-meta-lines .meta-row { display: table-row; }
    .emaaree-meta-lines .meta-cell {
        display: table-cell;
        width: 50%;
        vertical-align: top;
        padding: 0 8px 0 0;
    }
    .emaaree-meta-lines .meta-cell:last-child {
        padding: 0 0 0 16px;
        text-align: right;
    }
    .emaaree-meta-lines .meta-inline { font-weight: 450; }
    .emaaree-meta-lines .meta-sep {
        color: #d1d5db;
        padding: 0 0.35em;
        font-weight: 300;
    }
    .emaaree-meta-lines .meta-k { color: #6b7280; font-weight: 500; }
    .emaaree-rank-final {
        color: #D32F2F;
        font-weight: 600;
    }
    .emaaree-results-table {
        width: 100%;
        border-collapse: collapse;
        margin: 8px 0 0;
        font-size: 9pt;
    }
    .emaaree-results-table thead th {
        background: #002147;
        color: #fff;
        font-weight: 600;
        font-size: 7pt;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        text-align: center;
        border: none;
        padding: 6px 8px;
        vertical-align: middle;
    }
    .emaaree-results-table thead th:first-child {
        text-align: left;
        padding-left: 12px;
    }
    .emaaree-results-table tbody td {
        border: none;
        border-bottom: 1px solid #eee;
        padding: 10px 8px;
        vertical-align: middle;
    }
    .emaaree-results-table tbody td:first-child { padding-left: 12px; }
    .emaaree-results-table td.td-subject {
        text-align: left;
        font-weight: 600;
        color: #111;
    }
    .emaaree-results-table td.td-num {
        text-align: center;
        font-weight: 500;
        color: #374151;
    }
    .emaaree-results-table td.td-text {
        text-align: left;
        font-weight: 500;
        padding-left: 12px;
        color: #374151;
    }
    .emaaree-slot-sub {
        display: block;
        font-weight: 500;
        font-size: 6.5pt;
        opacity: 0.88;
        margin-top: 2px;
        letter-spacing: 0.02em;
    }
    .emaaree-summary-bar {
        margin-top: 36px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }
    .emaaree-summary-bar-inner { text-align: center; }
    .emaaree-summary-title {
        display: block;
        font-size: 7pt;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #9ca3af;
        margin-bottom: 14px;
    }
    .emaaree-summary-metrics {
        display: flex;
        justify-content: center;
        align-items: baseline;
        flex-wrap: wrap;
        gap: 28px 40px;
        font-size: 10pt;
    }
    .emaaree-summary-metrics .sk {
        color: #6b7280;
        font-size: 8pt;
        font-weight: 500;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin-right: 6px;
    }
    .emaaree-summary-metrics .sv { font-weight: 600; color: #111; }
    .emaaree-sig-footer { margin-top: 48px; width: 100%; }
    .emaaree-sig-footer td { vertical-align: bottom; }
    .emaaree-sig-line {
        display: inline-block;
        min-width: 200px;
        border-top: 1px solid #111;
        padding-top: 8px;
        font-weight: 500;
        font-size: 7.5pt;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        text-align: center;
        color: #4b5563;
    }
</style>
