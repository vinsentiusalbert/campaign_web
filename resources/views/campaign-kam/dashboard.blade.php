@extends('master')

@section('title', 'Dashboard KAM')

@section('css')
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
<style>
    :root {
        --kam-ink: #172033;
        --kam-muted: #64748b;
        --kam-border: #dbe5f0;
        --kam-border-strong: #c7d7ea;
        --kam-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
        --kam-shadow-soft: 0 8px 18px rgba(15, 23, 42, 0.05);
    }

    .kam-dashboard-shell { display: grid; gap: 18px; overflow-x: hidden; }
    .kam-dashboard-shell > * { min-width: 0; }
    .kam-hero, .kam-filter-card, .kam-table-card, .kam-admin-card {
        background: linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(250,252,255,0.98) 100%);
        border: 1px solid var(--kam-border);
        border-radius: 20px;
        box-shadow: var(--kam-shadow-soft);
    }

    .kam-hero {
        padding: 24px;
        background: linear-gradient(135deg, #f8fbff 0%, #eef7fb 100%);
        box-shadow: var(--kam-shadow);
    }

    .kam-hero-eyebrow, .kam-hero-pill, .kam-table-meta {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .kam-hero-eyebrow {
        padding: 6px 10px;
        margin-bottom: 12px;
        background: rgba(255,255,255,0.88);
        border: 1px solid rgba(148,163,184,0.16);
        color: #0f766e;
        font-size: 11px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .kam-hero-title, .kam-section-title {
        margin: 0;
        font-family: 'Poppins', sans-serif;
        color: var(--kam-ink);
    }

    .kam-hero-title {
        font-size: 30px;
        font-weight: 700;
        line-height: 1.14;
        letter-spacing: -0.04em;
    }

    .kam-hero-subtitle, .kam-section-note {
        color: var(--kam-muted);
        font-size: 13px;
        line-height: 1.6;
    }

    .kam-hero-subtitle { margin: 12px 0 0; max-width: 560px; font-size: 14px; }
    .kam-hero-pills, .kam-filter-actions, .kam-balance-summary { display: flex; gap: 10px; flex-wrap: wrap; }

    .kam-hero-pill {
        padding: 8px 12px;
        background: rgba(255,255,255,0.88);
        border: 1px solid rgba(148,163,184,0.14);
        color: #475569;
        font-weight: 600;
    }

    .kam-chip-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
    .kam-chip {
        position: relative;
        overflow: hidden;
        border-radius: 16px;
        min-height: 118px;
        padding: 20px 18px;
        border: 1px solid rgba(148,163,184,0.16);
        box-shadow: var(--kam-shadow-soft);
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .kam-chip::before { content: ''; position: absolute; inset: 0 auto 0 0; width: 3px; background: currentColor; opacity: 0.14; }
    .kam-chip-label { display: block; opacity: 0.75; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 8px; }
    .kam-chip-value { font-size: clamp(1.4rem, 1.45vw, 1.7rem); font-weight: 700; line-height: 1.12; letter-spacing: -0.02em; overflow-wrap: anywhere; }
    .kam-chip-saldo { background: linear-gradient(135deg, rgba(219,234,254,0.96) 0%, rgba(239,246,255,0.98) 100%); border-color: rgba(96,165,250,0.26); color: #1d4ed8; }
    .kam-chip-balance { background: linear-gradient(135deg, rgba(224,242,254,0.96) 0%, rgba(236,254,255,0.98) 100%); border-color: rgba(45,212,191,0.22); color: #0f766e; }
    .kam-chip-tosca { background: linear-gradient(135deg, rgba(204,251,241,0.92) 0%, rgba(224,242,254,0.98) 100%); border-color: rgba(45,212,191,0.24); color: #0f766e; }
    .kam-chip-failed { background: linear-gradient(135deg, rgba(254,226,226,0.90) 0%, rgba(255,241,242,0.98) 100%); border-color: rgba(248,113,113,0.22); color: #be123c; }
    .kam-chip-sms { background: linear-gradient(135deg, rgba(255,247,237,0.96) 0%, rgba(255,251,235,0.98) 100%); border-color: rgba(251,146,60,0.26); color: #c2410c; }
    .kam-chip-waba { background: linear-gradient(135deg, rgba(237,233,254,0.96) 0%, rgba(245,243,255,0.98) 100%); border-color: rgba(139,92,246,0.24); color: #6d28d9; }

    .kam-filter-head, .kam-table-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid rgba(219,229,240,0.9);
    }

    .kam-section-title { font-size: 20px; font-weight: 700; letter-spacing: -0.03em; }
    .kam-section-note { margin: 5px 0 0; }
    .kam-filter-body, .kam-table-body { padding: 20px; }

    .kam-filter-body .form-group label { color: #334155; font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 10px; }
    .kam-filter-body .form-control, .kam-admin-card .form-control {
        height: 48px;
        border-radius: 14px;
        border: 1px solid var(--kam-border-strong);
        background: #fff;
        color: #1e293b;
    }

    .kam-filter-card .select2-container {
        width: 100% !important;
    }

    .kam-filter-card .select2-container .select2-selection--single {
        height: 48px !important;
        border-radius: 14px !important;
        border: 1px solid var(--kam-border-strong) !important;
        display: flex;
        align-items: center;
        padding: 0 14px;
    }

    .kam-filter-card .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1e293b;
        line-height: 46px !important;
        padding-left: 0;
        padding-right: 24px;
    }

    .kam-filter-card .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px !important;
        right: 10px;
    }

    .kam-filter-card .select2-container--default.select2-container--focus .select2-selection--single,
    .kam-filter-card .select2-container--default.select2-container--open .select2-selection--single {
        border-color: rgba(45,212,191,0.44) !important;
        box-shadow: 0 0 0 0.22rem rgba(45,212,191,0.1);
    }

    .kam-filter-body .form-control:focus, .kam-admin-card .form-control:focus {
        border-color: rgba(45,212,191,0.44);
        box-shadow: 0 0 0 0.22rem rgba(45,212,191,0.1);
    }

    .kam-btn-primary {
        height: 48px;
        border-radius: 14px;
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        border-color: #0f766e;
        font-weight: 700;
        box-shadow: 0 12px 24px rgba(20,184,166,0.18);
    }

    .kam-table-meta {
        padding: 10px 14px;
        background: rgba(240,253,250,0.95);
        border: 1px solid rgba(45,212,191,0.2);
        color: #0f766e;
        white-space: nowrap;
    }

    .kam-table-wrap { border: 1px solid rgba(219,229,240,0.9); border-radius: 18px; overflow-x: auto; overflow-y: hidden; background: #fff; }
    .kam-table-wrap .dataTables_wrapper { padding: 16px; }
    .kam-report-table { margin-bottom: 0; font-size: 13px; width: 100% !important; }
    .kam-report-table thead th { background: #f6fbff; color: var(--kam-ink); border-top: 0; border-bottom: 1px solid var(--kam-border); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; white-space: nowrap; }
    .kam-report-table tbody td { vertical-align: top; border-color: #e8eff7; color: #334155; }
    .kam-report-table tbody tr:hover { background: #f8fcff; }
    .kam-id { font-weight: 700; color: #0f766e; white-space: nowrap; }
    .kam-last-update { min-width: 130px; color: var(--kam-muted); white-space: nowrap; }
    .kam-note-cell { min-width: 240px; max-width: 320px; white-space: normal; line-height: 1.45; }
    .kam-ref-cell { min-width: 220px; max-width: 280px; word-break: break-all; white-space: normal; line-height: 1.45; }
    .kam-status-badge { display: inline-flex; align-items: center; gap: 6px; border-radius: 999px; padding: 6px 11px; font-size: 12px; font-weight: 700; text-transform: capitalize; white-space: nowrap; }
    .kam-status-badge.success { background: #ecfdf3; color: #027a48; }
    .kam-status-badge.failed { background: #fef3f2; color: #b42318; }
    .kam-alert { border: 1px solid transparent; border-radius: 18px; padding: 16px 18px; box-shadow: var(--kam-shadow-soft); margin-bottom: 0; }
    .kam-alert.alert-warning { background: linear-gradient(135deg, #fff8eb 0%, #fffdf6 100%); border-color: #fde68a; color: #92400e; }
    .kam-alert.alert-info { background: linear-gradient(135deg, #eff6ff 0%, #f0fdfa 100%); border-color: #93c5fd; color: #0f3d67; }
    .kam-empty { text-align: center; padding: 42px 16px; color: var(--kam-muted); }
    .kam-empty strong { color: var(--kam-ink); display: block; margin-bottom: 6px; font-size: 16px; }
    .kam-balance-box { flex: 1 1 220px; border: 1px solid rgba(219,229,240,0.9); border-radius: 16px; padding: 14px 16px; background: #f8fbff; }
    .kam-balance-box span { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--kam-muted); margin-bottom: 6px; }
    .kam-balance-box strong { font-size: 22px; color: var(--kam-ink); line-height: 1.2; }

    .kam-hero .row, .kam-filter-body .form-row {
        margin-left: 0;
        margin-right: 0;
    }

    .kam-hero [class*='col-'], .kam-filter-body [class*='col-'] {
        padding-left: 10px;
        padding-right: 10px;
        min-width: 0;
    }

    @media (max-width: 1199.98px) {
        .kam-chip-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 991.98px) {
        .kam-chip-grid { grid-template-columns: 1fr; }
        .kam-hero { padding: 22px 18px; }
        .kam-hero-title { font-size: 29px; }
        .kam-filter-head, .kam-table-head, .kam-filter-body, .kam-table-body { padding: 18px; }
    }
</style>
@endsection

@section('content')
<div class="kam-dashboard-shell">
    <section class="kam-hero">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="kam-hero-eyebrow"><i class="fas fa-chart-line"></i>Dashboard Monitoring KAM</div>
                <h1 class="kam-hero-title">Dashboard KAM</h1>
                <p class="kam-hero-subtitle">Pantau ringkasan campaign dan telusuri detail report CSV dalam satu halaman.</p>
                <div class="kam-hero-pills">
                    <div class="kam-hero-pill"><i class="fas fa-bullhorn"></i>{{ $selectedCampaignId ? ($selectedCampaign?->campaign_unique_id ?? 'Campaign dipilih') : 'Semua campaign aktif' }}</div>
                    <div class="kam-hero-pill"><i class="fas fa-database"></i>{{ number_format($tableRowCount, 0, ',', '.') }} record report</div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="kam-chip-grid">
                    <div class="kam-chip kam-chip-saldo"><span class="kam-chip-label">Sisa Saldo</span><div class="kam-chip-value">Rp{{ number_format($sisaSaldo, 0, ',', '.') }}</div></div>
                    <div class="kam-chip kam-chip-balance"><span class="kam-chip-label">Balance Terpakai</span><div class="kam-chip-value">Rp{{ number_format($balanceTerpakai, 0, ',', '.') }}</div></div>
                    <div class="kam-chip kam-chip-tosca"><span class="kam-chip-label">Jumlah Terkirim</span><div class="kam-chip-value">{{ number_format($totalDelivered, 0, ',', '.') }}</div></div>
                    <div class="kam-chip kam-chip-failed"><span class="kam-chip-label">Jumlah Gagal</span><div class="kam-chip-value">{{ number_format($failedReportCount, 0, ',', '.') }}</div></div>
                    <div class="kam-chip kam-chip-sms"><span class="kam-chip-label">Terkirim SMS</span><div class="kam-chip-value">{{ number_format($smsDeliveredCount, 0, ',', '.') }}</div></div>
                    <div class="kam-chip kam-chip-waba"><span class="kam-chip-label">Terkirim WABA</span><div class="kam-chip-value">{{ number_format($wabaDeliveredCount, 0, ',', '.') }}</div></div>
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
                    <div class="form-group col-lg-5 mb-lg-0">
                        <label for="campaign_id">ID Iklan</label>
                        <select name="campaign_id" id="campaign_id" class="form-control kam-select2">
                            <option value="">Semua Campaign</option>
                            @foreach($campaigns as $campaign)
                                <option value="{{ $campaign->id }}" {{ (string) $selectedCampaignId === (string) $campaign->id ? 'selected' : '' }}>
                                    {{ $campaign->campaign_unique_id }} - {{ $campaign->template_name ?? $campaign->sender_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-6 col-lg-2 mb-lg-0">
                        <label for="update_from">Update Dari</label>
                        <input type="date" name="update_from" id="update_from" class="form-control" value="{{ request('update_from') }}">
                    </div>
                    <div class="form-group col-md-6 col-lg-2 mb-lg-0">
                        <label for="update_to">Update Sampai</label>
                        <input type="date" name="update_to" id="update_to" class="form-control" value="{{ request('update_to') }}">
                    </div>
                    <div class="form-group col-lg-3 mb-0">
                        <div class="kam-filter-actions">
                            <button type="submit" class="btn btn-primary kam-btn-primary flex-fill">Terapkan</button>
                            @if($selectedCampaignId || request('update_from') || request('update_to'))
                                <a href="{{ route('campaign-kam-dashboard.index') }}" class="btn btn-outline-secondary flex-fill">Reset</a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    @if(session('success'))
        <div class="alert alert-success kam-alert">{{ session('success') }}</div>
    @endif
    @if($canManageSaldo)
        <section class="kam-admin-card p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
                <div>
                    <h2 class="kam-section-title">Kelola Saldo</h2>
                    <p class="kam-section-note">Admin dan Super dapat menambahkan saldo global KAM. Sisa saldo otomatis dihitung dari saldo global dikurangi balance terpakai.</p>
                </div>
                <button type="button" class="btn btn-primary kam-btn-primary" data-toggle="modal" data-target="#saldoModal">Tambah Saldo</button>
            </div>
            <div class="kam-balance-summary mt-3">
                <div class="kam-balance-box"><span>Saldo Global</span><strong>Rp{{ number_format($saldoKam, 0, ',', '.') }}</strong></div>
                <div class="kam-balance-box"><span>Sisa Saldo</span><strong>Rp{{ number_format($sisaSaldo, 0, ',', '.') }}</strong></div>
            </div>
        </section>

        <div class="modal fade" id="saldoModal" tabindex="-1" role="dialog" aria-labelledby="saldoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form method="POST" action="{{ route('campaign-kam-dashboard.update-saldo') }}" id="kam-saldo-form">
                        @csrf
                        <input type="hidden" name="campaign_id" value="{{ $selectedCampaignId }}">
                        <input type="hidden" name="update_from" value="{{ request('update_from') }}">
                        <input type="hidden" name="update_to" value="{{ request('update_to') }}">
                        <div class="modal-header">
                            <h5 class="modal-title" id="saldoModalLabel">Tambah Saldo Global KAM</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-light border mb-3"><strong>Saldo Saat Ini:</strong> Rp{{ number_format($saldoKam, 0, ',', '.') }}</div>
                            <div class="form-group">
                                <label for="amount_input" class="font-weight-semibold">Nominal Tambah Saldo</label>
                                <input type="number" step="0.01" min="0" name="amount" id="amount_input" class="form-control" value="{{ old('amount') }}" placeholder="Masukkan nominal tambahan saldo">
                                @error('amount') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="form-group mb-0">
                                <label for="note_input" class="font-weight-semibold">Catatan</label>
                                <input type="text" name="note" id="note_input" class="form-control" value="{{ old('note') }}" placeholder="Contoh: top up awal bulan">
                                @error('note') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary kam-btn-primary" id="kam-saldo-submit">Tambah Saldo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <section class="kam-table-card">
        <div class="kam-table-head">
            <div>
                <h2 class="kam-section-title">Riwayat Saldo KAM</h2>
                <p class="kam-section-note">Menampilkan histori penambahan saldo global untuk dashboard KAM.</p>
            </div>
            <div class="kam-table-meta"><i class="fas fa-wallet"></i><span>Rp{{ number_format($saldoKam, 0, ',', '.') }}</span></div>
        </div>
        <div class="kam-table-body">
            @if($saldoHistories->isEmpty())
                <div class="kam-empty"><strong>Belum ada riwayat saldo</strong>Transaksi saldo global KAM akan muncul di sini setelah Admin atau Super menambahkan saldo.</div>
            @else
                <div class="table-responsive kam-table-wrap">
                    <table class="table kam-report-table mb-0" id="kam-saldo-history-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nominal</th>
                                <th>Saldo Setelah</th>
                                <th>Input Oleh</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($saldoHistories as $history)
                                <tr>
                                    <td>{{ $history->created_at?->format('d-m-Y H:i') ?? '-' }}</td>
                                    <td>Rp{{ number_format($history->amount, 0, ',', '.') }}</td>
                                    <td>Rp{{ number_format($history->balance_after, 0, ',', '.') }}</td>
                                    <td>{{ $history->creator?->name ?? '-' }}</td>
                                    <td>{{ $history->note ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>

    @if($showMissingUploadWarning)
        <div class="alert alert-warning kam-alert">Data report CSV untuk campaign <strong>{{ $selectedCampaign?->campaign_unique_id ?? '-' }}</strong> belum di-upload.</div>
    @endif

    @if($selectedCampaignId && !$showMissingUploadWarning)
        <div class="alert alert-info kam-alert">Last update data untuk campaign <strong>{{ $selectedCampaign?->campaign_unique_id ?? '-' }}</strong>: <strong>{{ $selectedCampaignModel?->report_csv_uploaded_at ? $selectedCampaignModel->report_csv_uploaded_at->format('d-m-Y H:i') : '-' }}</strong></div>
    @endif

    <section class="kam-table-card">
        <div class="kam-table-head">
            <div>
                <h2 class="kam-section-title">Detail Report</h2>
                <p class="kam-section-note">Menampilkan Detail Report untuk setiap Iklan.</p>
            </div>
            <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                <a href="{{ route('campaign-kam-dashboard.download-csv', array_filter([
                    'campaign_id' => $selectedCampaignId,
                    'update_from' => request('update_from'),
                    'update_to' => request('update_to'),
                ])) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-download"></i> Download CSV
                </a>
                <div class="kam-table-meta"><i class="fas fa-table"></i><span>{{ number_format($tableRowCount, 0, ',', '.') }} record</span></div>
            </div>
        </div>
        <div class="kam-table-body">
            <div class="table-responsive kam-table-wrap">
                <table class="table kam-report-table" id="kam-report-table">
                    <thead>
                        <tr>
                            <th>Campaign ID</th>
                            <th>Last Update</th>
                            <th>Unique ID</th>
                            <th>Sender ID</th>
                            <th>Campaign Unique ID</th>
                            <th>Sender Name</th>
                            <th>Template Name</th>
                            <th>Channel</th>
                            <th>MSISDN</th>
                            <th>Status</th>
                            <th>Send Date</th>
                            <th>Deliv Report Status</th>
                            <th>Deliv Report Date</th>
                            <th>Deliv Report Time</th>
                            <th>Deliv Read Date</th>
                            <th>Deliv Read Time</th>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function () {
    $('#campaign_id').select2({
        width: '100%',
        placeholder: 'Cari ID Iklan',
        allowClear: true
    });

    if ($('#kam-saldo-history-table').length) {
        $('#kam-saldo-history-table').DataTable({
            paging: false,
            searching: false,
            info: false,
            ordering: false,
            autoWidth: false,
            responsive: true,
            language: {
                emptyTable: 'Belum ada riwayat saldo.'
            }
        });
    }

    const table = $('#kam-report-table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        ajax: {
            url: "{{ route('campaign-kam-dashboard.data') }}",
            data: function (d) {
                d.campaign_id = $('#campaign_id').val();
                d.update_from = $('#update_from').val();
                d.update_to = $('#update_to').val();
            }
        },
        order: [[3, 'desc'], [2, 'desc']],
        columns: [
            { data: 'campaign_unique_id', name: 'campaign_kam.campaign_unique_id', className: 'kam-id' },
            { data: 'report_csv_uploaded_at', name: 'campaign_kam.report_csv_uploaded_at', className: 'kam-last-update' },
            { data: 'unique_id', name: 'campaign_kam_reports.unique_id' },
            { data: 'sender_id', name: 'campaign_kam_reports.sender_id' },
            { data: 'campaign_id', name: 'campaign_kam_reports.campaign_id' },
            { data: 'sender_name', name: 'campaign_kam_reports.sender_name' },
            { data: 'template_name', name: 'campaign_kam_reports.template_name' },
            { data: 'channel', name: 'campaign_kam.channel', orderable: false, searchable: false },
            { data: 'msisdn', name: 'campaign_kam_reports.msisdn' },
            { data: 'status', name: 'campaign_kam_reports.status', orderable: false, searchable: false },
            { data: 'send_date', name: 'campaign_kam_reports.send_date' },
            { data: 'deliv_report_status', name: 'campaign_kam_reports.deliv_report_status' },
            { data: 'deliv_report_date', name: 'campaign_kam_reports.deliv_report_date' },
            { data: 'deliv_report_time', name: 'campaign_kam_reports.deliv_report_time' },
            { data: 'deliv_read_date', name: 'campaign_kam_reports.deliv_read_date' },
            { data: 'deliv_read_time', name: 'campaign_kam_reports.deliv_read_time' },
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

    $('#kam-saldo-form').on('submit', function () {
        const submitButton = $('#kam-saldo-submit');

        if (submitButton.prop('disabled')) {
            return false;
        }

        submitButton.prop('disabled', true).text('Menyimpan...');
    });

    $('#campaign_id').on('change', function () {
        if (!this.form) {
            table.ajax.reload();
        }
    });
});
</script>
@endsection

