<style>
    @page {
        margin: 28px 32px 40px 32px;
    }
    * { box-sizing: border-box; }
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 9px;
        color: #1e293b;
        line-height: 1.35;
        margin: 0;
        @if($isAr ?? false)
        direction: rtl;
        text-align: right;
        @endif
    }

    /* Letterhead */
    .letterhead {
        width: 100%;
        border-bottom: 3px solid #c2410c;
        padding-bottom: 10px;
        margin-bottom: 0;
    }
    .letterhead-table { width: 100%; border-collapse: collapse; }
    .letterhead-table td { vertical-align: middle; border: none; padding: 0; }
    .company-name {
        font-size: 16px;
        font-weight: bold;
        color: #c2410c;
        letter-spacing: 0.3px;
    }
    .company-sub {
        font-size: 8px;
        color: #64748b;
        margin-top: 2px;
    }
    .letterhead-meta {
        font-size: 8px;
        color: #64748b;
        text-align: {{ ($isAr ?? false) ? 'left' : 'right' }};
    }

    /* Report title band (Crystal-style) */
    .title-band {
        background: #1e293b;
        color: #fff;
        padding: 8px 12px;
        margin: 10px 0 0;
    }
    .title-band-table { width: 100%; border-collapse: collapse; }
    .title-band-table td { border: none; padding: 0; vertical-align: middle; color: #fff; }
    .report-title {
        font-size: 13px;
        font-weight: bold;
    }
    .report-legacy {
        font-size: 7px;
        color: #94a3b8;
        margin-top: 2px;
    }
    .report-category {
        font-size: 8px;
        background: #ea580c;
        padding: 3px 8px;
        border-radius: 2px;
        text-align: center;
        white-space: nowrap;
    }

    /* Filter strip */
    .filter-strip {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-top: none;
        padding: 6px 12px;
        margin-bottom: 12px;
    }
    .filter-strip-table { width: 100%; border-collapse: collapse; }
    .filter-strip-table td {
        border: none;
        padding: 2px 8px 2px 0;
        font-size: 8px;
        vertical-align: top;
    }
    .filter-label {
        color: #64748b;
        font-weight: bold;
        white-space: nowrap;
    }
    .filter-value { color: #0f172a; }

    /* Data tables */
    table.rpt {
        width: 100%;
        border-collapse: collapse;
        margin-top: 4px;
    }
    table.rpt th {
        background: #fff7ed;
        border: 1px solid #cbd5e1;
        padding: 5px 6px;
        font-size: 8px;
        font-weight: bold;
        color: #334155;
        text-align: {{ ($isAr ?? false) ? 'right' : 'left' }};
    }
    table.rpt td {
        border: 1px solid #e2e8f0;
        padding: 4px 6px;
        font-size: 8px;
        vertical-align: top;
    }
    table.rpt tbody tr:nth-child(even) td { background: #fafafa; }
    table.rpt tfoot td {
        background: #f1f5f9;
        font-weight: bold;
        border: 1px solid #cbd5e1;
        padding: 5px 6px;
    }
    .text-right { text-align: {{ ($isAr ?? false) ? 'left' : 'right' }}; direction: ltr; }
    .text-center { text-align: center; }
    .num { font-family: DejaVu Sans Mono, monospace; }

    /* Section blocks */
    .section-head {
        background: #334155;
        color: #fff;
        padding: 5px 10px;
        font-size: 9px;
        font-weight: bold;
        margin-top: 10px;
    }
    .section-box {
        border: 1px solid #cbd5e1;
        border-top: none;
        padding: 0;
        margin-bottom: 10px;
    }

    /* Financial statement lines */
    .fin-line {
        width: 100%;
        border-collapse: collapse;
        margin: 2px 0;
    }
    .fin-line td {
        border: none;
        padding: 5px 8px;
        font-size: 10px;
        border-bottom: 1px solid #f1f5f9;
    }
    .fin-line.net td {
        border-top: 2px solid #1e293b;
        border-bottom: 3px double #1e293b;
        font-weight: bold;
        font-size: 11px;
        padding-top: 8px;
    }

    /* Balance sheet columns */
    .bs-columns { width: 100%; border-collapse: collapse; }
    .bs-columns > tbody > tr > td {
        width: 33%;
        vertical-align: top;
        padding: 0 6px;
        border: none;
    }
    .bs-col-head {
        background: #fff7ed;
        border: 1px solid #cbd5e1;
        padding: 6px;
        font-weight: bold;
        text-align: center;
        font-size: 9px;
    }
    .bs-row {
        width: 100%;
        border-collapse: collapse;
    }
    .bs-row td {
        border: none;
        border-bottom: 1px dotted #e2e8f0;
        padding: 3px 4px;
        font-size: 8px;
    }

    /* Summary bar */
    .summary-bar {
        margin-top: 10px;
        padding: 8px 12px;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        font-size: 9px;
    }
    .summary-bar-table { width: 100%; border-collapse: collapse; }
    .summary-bar-table td { border: none; padding: 2px 12px 2px 0; }

    /* Record count badge */
    .record-count {
        font-size: 8px;
        color: #64748b;
        margin-bottom: 4px;
    }

    /* Page footer */
    .page-footer {
        position: fixed;
        bottom: -24px;
        left: 0;
        right: 0;
        height: 24px;
        border-top: 1px solid #e2e8f0;
        padding-top: 4px;
        font-size: 7px;
        color: #94a3b8;
    }
    .page-footer-table { width: 100%; border-collapse: collapse; }
    .page-footer-table td { border: none; padding: 0; vertical-align: middle; }

    .empty-msg {
        text-align: center;
        padding: 24px;
        color: #94a3b8;
        font-style: italic;
        border: 1px dashed #e2e8f0;
    }
</style>
