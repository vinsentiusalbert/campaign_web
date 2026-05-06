@extends('master')

@section('title', 'Dashboard KAM')

@section('css')
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
<style>
    :root {
        --kam-accent: #c41e3a;
        --kam-accent-dark: #8f1327;
        --kam-ink: #172033;
        --kam-muted: #64748b;
        --kam-surface: #ffffff;
        --kam-surface-soft: #f8fbff;
        --kam-border: #dbe5f0;
        --kam-border-strong: #c7d7ea;
        --kam-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
        --kam-shadow-soft: 0 8px 18px rgba(15, 23, 42, 0.05);
    }

    .kam-dashboard-shell {
        display: grid;
        gap: 18px;
    }

    .kam-hero {
        background: linear-gradient(135deg, #f8fbff 0%, #eef7fb 100%);
        border: 1px solid var(--kam-border);
        border-radius: 20px;
        padding: 24px;
        box-shadow: var(--kam-shadow);
    }

    .kam-hero-content,
    .kam-hero-stats {
        position: relative;
        z-index: 1;
    }

    .kam-hero-content {
        max-width: 620px;
    }

    .kam-hero-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        margin-bottom: 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.88);
        border: 1px solid rgba(148, 163, 184, 0.16);
        color: #0f766e;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .kam-hero-title {
        margin: 0;
        font-family: 'Poppins', sans-serif;
        font-size: 30px;
        font-weight: 700;
        line-height: 1.14;
        color: var(--kam-ink);
        letter-spacing: -0.04em;
    }

    .kam-hero-subtitle {
        margin: 12px 0 0;
        max-width: 560px;
        color: var(--kam-muted);
        font-size: 14px;
        line-height: 1.65;
    }

    .kam-hero-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 16px;
    }

    .kam-hero-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.88);
        border: 1px solid rgba(148, 163, 184, 0.14);
        color: #475569;
        font-size: 12px;
        font-weight: 600;
    }

    .kam-hero-pill i {
        color: #0f766e;
    }

    .kam-chip-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .kam-chip {
        position: relative;
        overflow: hidden;
        border-radius: 16px;
        padding: 16px;
        border: 1px solid rgba(148, 163, 184, 0.16);
        box-shadow: var(--kam-shadow-soft);
    }

    .kam-chip::before {
        content: '';
        position: absolute;
        inset: 0 auto 0 0;
        width: 3px;
        background: currentColor;
        opacity: 0.14;
    }

    .kam-chip:hover {
        transform: none;
        box-shadow: var(--kam-shadow-soft);
    }

    .kam-chip-label {
        display: block;
        color: inherit;
        opacity: 0.75;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        margin-bottom: 10px;
    }

    .kam-chip-value {
        color: inherit;
        font-size: 26px;
        font-weight: 700;
        line-height: 1.08;
        letter-spacing: -0.04em;
    }

    .kam-chip-tosca {
        background: linear-gradient(135deg, rgba(204, 251, 241, 0.92) 0%, rgba(224, 242, 254, 0.98) 100%);
        border-color: rgba(45, 212, 191, 0.24);
        color: #0f766e;
    }

    .kam-chip-failed {
        background: linear-gradient(135deg, rgba(254, 226, 226, 0.9) 0%, rgba(255, 241, 242, 0.98) 100%);
        border-color: rgba(248, 113, 113, 0.22);
        color: #be123c;
    }

    .kam-chip-saldo {
        background: linear-gradient(135deg, rgba(219, 234, 254, 0.96) 0%, rgba(239, 246, 255, 0.98) 100%);
        border-color: rgba(96, 165, 250, 0.26);
        color: #1d4ed8;
    }

    .kam-chip-balance {
        background: linear-gradient(135deg, rgba(224, 242, 254, 0.96) 0%, rgba(236, 254, 255, 0.98) 100%);
        border-color: rgba(45, 212, 191, 0.22);
        color: #0f766e;
    }

    .kam-filter-card,
    .kam-table-card {
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(250, 252, 255, 0.98) 100%);
        border: 1px solid var(--kam-border);
        border-radius: 20px;
        box-shadow: var(--kam-shadow-soft);
        overflow: hidden;
    }

    .kam-filter-head,
    .kam-table-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid rgba(219, 229, 240, 0.9);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.96) 0%, rgba(248, 251, 255, 0.96) 100%);
    }

    .kam-section-title {
        margin: 0;
        font-family: 'Poppins', sans-serif;
        font-size: 20px;
        font-weight: 700;
        color: var(--kam-ink);
        letter-spacing: -0.03em;
    }

    .kam-section-note {
        color: var(--kam-muted);
        font-size: 13px;
        margin: 5px 0 0;
        line-height: 1.55;
    }

    .kam-filter-body,
    .kam-table-body {
        padding: 20px;
    }

    .kam-filter-body .form-group label {
        color: #334155;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 10px;
    }

    .kam-filter-body .form-control {
        height: 48px;
        border-radius: 14px;
        border: 1px solid var(--kam-border-strong);
        background: #fff;
        color: #1e293b;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.02);
    }

    .kam-filter-body .form-control:focus {
        border-color: rgba(45, 212, 191, 0.44);
        box-shadow: 0 0 0 0.22rem rgba(45, 212, 191, 0.1);
    }

    .kam-filter-actions {
        display: flex;
        gap: 10px;
    }

    .kam-btn-primary {
        height: 48px;
        border-radius: 14px;
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        border-color: #0f766e;
        font-weight: 700;
        box-shadow: 0 12px 24px rgba(20, 184, 166, 0.18);
    }

    .kam-btn-primary:hover,
    .kam-btn-primary:focus {
        background: linear-gradient(135deg, #0b5f59 0%, #0f766e 100%);
        border-color: #0b5f59;
    }

    .kam-filter-actions .btn-outline-secondary {
        height: 48px;
        border-radius: 14px;
        border-color: var(--kam-border-strong);
        color: #475569;
        font-weight: 600;
    }

    .kam-filter-actions .btn-outline-secondary:hover {
        background: #f8fafc;
        color: #1e293b;
    }

    .kam-table-meta {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        background: rgba(240, 253, 250, 0.95);
        border: 1px solid rgba(45, 212, 191, 0.2);
        border-radius: 999px;
        color: #0f766e;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .kam-report-table {
        margin-bottom: 0;
        font-size: 13px;
        width: 100% !important;
    }

    .kam-report-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f6fbff;
        color: var(--kam-ink);
        border-top: 0;
        border-bottom: 1px solid var(--kam-border);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        white-space: nowrap;
        vertical-align: middle;
    }

    .kam-report-table tbody td {
        vertical-align: top;
        border-color: #e8eff7;
        color: #334155;
    }

    .kam-report-table tbody tr:hover {
        background: #f8fcff;
    }

    .kam-id {
        font-weight: 700;
        color: #0f766e;
        white-space: nowrap;
    }

    .kam-last-update {
        min-width: 130px;
        color: var(--kam-muted);
        white-space: nowrap;
    }

    .kam-note-cell {
        min-width: 240px;
        max-width: 320px;
        white-space: normal;
        line-height: 1.45;
    }

    .kam-ref-cell {
        min-width: 220px;
        max-width: 280px;
        word-break: break-all;
        white-space: normal;
        line-height: 1.45;
    }

    .kam-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 6px 11px;
        font-size: 12px;
        font-weight: 700;
        text-transform: capitalize;
        white-space: nowrap;
    }

    .kam-status-badge.success {
        background: #ecfdf3;
        color: #027a48;
    }

    .kam-status-badge.failed {
        background: #fef3f2;
        color: #b42318;
    }

    .kam-empty {
        text-align: center;
        padding: 42px 16px;
        color: var(--kam-muted);
    }

    .kam-empty strong {
        color: var(--kam-ink);
        display: block;
        margin-bottom: 6px;
        font-size: 16px;
    }

    .kam-alert {
        border: 1px solid transparent;
        border-radius: 18px;
        padding: 16px 18px;
        box-shadow: var(--kam-shadow-soft);
        margin-bottom: 0;
    }

    .kam-alert.alert-warning {
        background: linear-gradient(135deg, #fff8eb 0%, #fffdf6 100%);
        border-color: #fde68a;
        color: #92400e;
    }

    .kam-alert.alert-info {
        background: linear-gradient(135deg, #eff6ff 0%, #f0fdfa 100%);
        border-color: #93c5fd;
        color: #0f3d67;
    }

    .kam-table-wrap {
        border: 1px solid rgba(219, 229, 240, 0.9);
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
    }

    .kam-table-wrap .dataTables_wrapper {
        padding: 16px;
    }

    .kam-table-wrap .dataTables_wrapper .row:first-child {
        margin-bottom: 12px;
    }

    .kam-table-wrap .dataTables_wrapper .row:last-child {
        margin-top: 14px;
    }

    .kam-table-wrap .dataTables_length label,
    .kam-table-wrap .dataTables_filter label,
    .kam-table-wrap .dataTables_info,
    .kam-table-wrap .dataTables_paginate {
        font-size: 13px;
        color: var(--kam-muted);
    }

    .kam-table-wrap .dataTables_filter input,
    .kam-table-wrap .dataTables_length select {
        border-radius: 12px;
        border: 1px solid var(--kam-border);
        box-shadow: none;
    }

    .kam-table-wrap .dataTables_filter input:focus,
    .kam-table-wrap .dataTables_length select:focus {
        border-color: rgba(20, 184, 166, 0.35);
        box-shadow: 0 0 0 0.2rem rgba(20, 184, 166, 0.08);
    }

    .kam-table-wrap .paginate_button .page-link {
        border-radius: 10px !important;
        margin: 0 2px;
        min-width: 36px;
        text-align: center;
    }

    .kam-table-wrap .page-item.active .page-link {
        background: #0f766e !important;
        border-color: #0f766e !important;
        color: #fff !important;
    }

    .kam-table-wrap .page-link:hover {
        background: #ecfeff !important;
        border-color: #a7f3d0 !important;
        color: #0f766e !important;
    }

    .kam-table-wrap .dataTables_paginate svg {
        width: 14px !important;
        height: 14px !important;
    }

    @media (max-width: 991.98px) {
        .kam-chip-grid {
            grid-template-columns: 1fr;
        }

        .kam-hero {
            padding: 22px 18px;
        }

        .kam-hero-title {
            font-size: 29px;
        }

        .kam-filter-head,
        .kam-table-head,
        .kam-filter-body,
        .kam-table-body {
            padding: 18px;
        }
    }
</style>
@endsection

@section('content')
<div class="kam-dashboard-shell">
    <section class="kam-hero">
        <div class="row align-items-center">
            <div class="col-lg-7 mb-4 mb-lg-0">
                <div class="kam-hero-content">
                    <div class="kam-hero-eyebrow">
                        <i class="fas fa-chart-line"></i>
                        Dashboard Monitoring KAM
                    </div>
                    <h1 class="kam-hero-title">Dashboard KAM</h1>
                    <p class="kam-hero-subtitle">Pantau ringkasan campaign dan telusuri detail report CSV dalam satu halaman.</p>
                    <div class="kam-hero-pills">
                        <div class="kam-hero-pill">
                            <i class="fas fa-bullhorn"></i>
                            {{ $selectedCampaignId ? ($selectedCampaign?->campaign_unique_id ?? 'Campaign dipilih') : 'Semua campaign aktif' }}
                        </div>
                        <div class="kam-hero-pill">
                            <i class="fas fa-database"></i>
                            {{ number_format($tableRowCount, 0, ',', '.') }} record report
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="kam-hero-stats">
                    <div class="kam-chip-grid">
                        <div class="kam-chip kam-chip-saldo">
                            <span class="kam-chip-label">Sisa Saldo</span>
                            <div class="kam-chip-value">Rp{{ number_format($sisaSaldo, 0, ',', '.') }}</div>
                        </div>
                        <div class="kam-chip kam-chip-balance">
                            <span class="kam-chip-label">Balance Terpakai</span>
                            <div class="kam-chip-value">Rp{{ number_format($balanceTerpakai, 0, ',', '.') }}</div>
                        </div>
                        <div class="kam-chip kam-chip-tosca">
                            <span class="kam-chip-label">Jumlah Terkirim</span>
                            <div class="kam-chip-value">{{ number_format($totalDelivered, 0, ',', '.') }}</div>
                        </div>
                        <div class="kam-chip kam-chip-failed">
                            <span class="kam-chip-label">Jumlah Gagal</span>
                            <div class="kam-chip-value">{{ number_format($failedReportCount, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="kam-filter-card">
        <div class="kam-filter-head">
            <div>
                <h2 class="kam-section-title">Filter Data</h2>
                <p class="kam-section-note">Pilih satu campaign untuk melihat isi CSV per ID iklan, atau biarkan kosong untuk melihat semua data upload.</p>
            </div>
        </div>

        <div class="kam-filter-body">
            <form method="GET" action="{{ route('campaign-kam-dashboard.index') }}">
                <div class="form-row align-items-end">
                    <div class="form-group col-lg-9 mb-lg-0">
                        <label for="campaign_id" class="font-weight-semibold">ID Iklan</label>
                        <select name="campaign_id" id="campaign_id" class="form-control">
                            <option value="">Semua Campaign</option>
                            @foreach($campaigns as $campaign)
                                <option value="{{ $campaign->id }}" {{ (string) $selectedCampaignId === (string) $campaign->id ? 'selected' : '' }}>
                                    {{ $campaign->campaign_unique_id }} - {{ $campaign->template_name ?? $campaign->sender_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-lg-3 mb-0">
                        <div class="kam-filter-actions">
                            <button type="submit" class="btn btn-primary kam-btn-primary flex-fill">Terapkan</button>
                            @if($selectedCampaignId)
                                <a href="{{ route('campaign-kam-dashboard.index') }}" class="btn btn-outline-secondary flex-fill">Reset</a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    @if($showMissingUploadWarning)
        <div class="alert alert-warning kam-alert">
            Data report CSV untuk campaign <strong>{{ $selectedCampaign?->campaign_unique_id ?? '-' }}</strong> belum di-upload.
        </div>
    @endif

    @if($selectedCampaignId && !$showMissingUploadWarning)
        <div class="alert alert-info kam-alert">
            Last update data untuk campaign <strong>{{ $selectedCampaign?->campaign_unique_id ?? '-' }}</strong>:
            <strong>{{ $selectedCampaignModel?->report_csv_uploaded_at ? $selectedCampaignModel->report_csv_uploaded_at->format('d-m-Y H:i') : '-' }}</strong>
        </div>
    @endif


    <section class="kam-table-card">
        <div class="kam-table-head">
            <div>
                <h2 class="kam-section-title">Isi Report CSV</h2>
                <p class="kam-section-note">Menampilkan baris data asli dari file CSV yang sudah di-upload.</p>
            </div>
            <div class="kam-table-meta">
                <i class="fas fa-table"></i>
                <span>{{ number_format($tableRowCount, 0, ',', '.') }} record</span>
            </div>
        </div>

        <div class="kam-table-body">
            <div class="table-responsive kam-table-wrap">
                <table class="table kam-report-table">
                    <thead>
                        <tr>
                            <th>Campaign ID</th>
                            <th>Last Update</th>
                            <th>Campaign Id CSV</th>
                            <th>Created Date</th>
                            <th>Created Time</th>
                            <th>Sender Name</th>
                            <th>Template Name</th>
                            <th>Category</th>
                            <th>MSISDN</th>
                            <th>Status</th>
                            <th>Vendor Ref Id</th>
                            <th>Sent Date</th>
                            <th>Sent Time</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function () {
    const table = $('.kam-report-table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        ajax: {
            url: "{{ route('campaign-kam-dashboard.data') }}",
            data: function (d) {
                d.campaign_id = $('#campaign_id').val();
            }
        },
        order: [[3, 'desc'], [4, 'desc'], [2, 'desc']],
        columns: [
            { data: 'campaign_unique_id', name: 'campaign_kam.campaign_unique_id', className: 'kam-id' },
            { data: 'report_csv_uploaded_at', name: 'campaign_kam.report_csv_uploaded_at', className: 'kam-last-update' },
            { data: 'campaign_id', name: 'campaign_kam_reports.campaign_id' },
            { data: 'created_date', name: 'campaign_kam_reports.created_date' },
            { data: 'created_time', name: 'campaign_kam_reports.created_time' },
            { data: 'sender_name', name: 'campaign_kam_reports.sender_name' },
            { data: 'template_name', name: 'campaign_kam_reports.template_name' },
            { data: 'category', name: 'campaign_kam_reports.category' },
            { data: 'msisdn', name: 'campaign_kam_reports.msisdn' },
            { data: 'status', name: 'campaign_kam_reports.status', orderable: false, searchable: false },
            { data: 'vendor_ref_id', name: 'campaign_kam_reports.vendor_ref_id', orderable: false },
            { data: 'sent_date', name: 'campaign_kam_reports.sent_date' },
            { data: 'sent_time', name: 'campaign_kam_reports.sent_time' },
            { data: 'note', name: 'campaign_kam_reports.note', orderable: false }
        ],
        language: {
            emptyTable: 'Belum ada data CSV untuk ditampilkan.',
            zeroRecords: 'Data tidak ditemukan.',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ record',
            infoEmpty: 'Menampilkan 0 record',
            paginate: {
                previous: '&lsaquo;',
                next: '&rsaquo;'
            }
        },
        drawCallback: function () {
            $('.dataTables_paginate .paginate_button').removeClass('btn btn-sm');
        }
    });

    $('#campaign_id').on('change', function () {
        if (!this.form) {
            table.ajax.reload();
        }
    });
});
</script>
@endsection





