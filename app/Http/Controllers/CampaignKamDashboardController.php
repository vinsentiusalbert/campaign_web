<?php

namespace App\Http\Controllers;

use App\Models\CampaignKam;
use App\Models\CampaignKamReport;
use App\Models\KamGlobalSaldo;
use App\Models\KamGlobalSaldoHistory;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CampaignKamDashboardController extends Controller
{
    private const CHANNEL_PRICES = [
        'WABA' => 395,
        'SMS' => 100,
        'MMS' => 180,
    ];

    private function ensureKamDashboardAccess(): void
    {
        abort_unless(in_array(auth()->user()->role, ['Admin', 'Super', 'KAM']), 403);
    }

    private function canManageSaldo(): bool
    {
        return in_array(auth()->user()->role, ['Admin', 'Super']);
    }

    private function accessibleCampaignsQuery()
    {
        return CampaignKam::query()->orderBy('campaign_unique_id');
    }

    private function globalSaldo(): KamGlobalSaldo
    {
        return KamGlobalSaldo::query()->firstOrCreate(
            ['id' => 1],
            ['name' => 'Global KAM', 'balance' => 0]
        );
    }

    public function index(Request $request)
    {
        $this->ensureKamDashboardAccess();

        $campaignsQuery = $this->accessibleCampaignsQuery();
        $campaigns = $campaignsQuery->get(['id', 'user_id', 'campaign_unique_id', 'template_name', 'sender_name']);
        $selectedCampaignId = $request->filled('campaign_id') ? (int) $request->campaign_id : null;
        [$updateFrom, $updateTo] = $this->resolveLastUpdateRange($request);
        $campaignTableQuery = CampaignKam::query()
            ->whereIn('id', $campaigns->pluck('id'))
            ->orderByDesc('report_csv_uploaded_at')
            ->orderBy('campaign_unique_id');

        if ($selectedCampaignId) {
            $campaignTableQuery->where('id', $selectedCampaignId);
        }

        $this->applyLastUpdateFilter($campaignTableQuery, $updateFrom, $updateTo, 'report_csv_uploaded_at');

        $campaignRows = $campaignTableQuery->get();
        $selectedCampaign = $selectedCampaignId ? $campaigns->firstWhere('id', $selectedCampaignId) : null;
        $selectedCampaignModel = $selectedCampaignId ? $campaignRows->first() : null;
        $showMissingUploadWarning = $selectedCampaignId && $selectedCampaignModel && $selectedCampaignModel->report_csv_uploaded_at === null;

        $totalRead = (int) $campaignRows->sum(fn ($row) => (int) ($row->total_read ?? 0));
        $totalRevenue = (float) $campaignRows->sum(fn ($row) => (float) ($row->total_revenue ?? 0));

        $reportRowsQuery = CampaignKamReport::query()
            ->leftJoin('campaign_kam', 'campaign_kam.id', '=', 'campaign_kam_reports.campaign_kam_id')
            ->whereIn('campaign_kam_reports.campaign_kam_id', $campaigns->pluck('id'))
            ->when($selectedCampaignId, fn ($query) => $query->where('campaign_kam_reports.campaign_kam_id', $selectedCampaignId));

        $this->applyLastUpdateFilter($reportRowsQuery, $updateFrom, $updateTo, 'campaign_kam.report_csv_uploaded_at');

        $successfulReportCount = (clone $reportRowsQuery)
            ->whereRaw('LOWER(campaign_kam_reports.status) = ?', ['succeeded'])
            ->count();

        $failedReportCount = (clone $reportRowsQuery)
            ->whereRaw('LOWER(campaign_kam_reports.status) = ?', ['failed'])
            ->count();

        $globalSaldo = $this->globalSaldo();
        $totalDelivered = $successfulReportCount;
        $successfulReportsByChannel = (clone $reportRowsQuery)
            ->whereRaw('LOWER(campaign_kam_reports.status) = ?', ['succeeded'])
            ->selectRaw('UPPER(COALESCE(campaign_kam.channel, ?)) as channel_key, COUNT(*) as total_rows', ['WABA'])
            ->groupBy('channel_key')
            ->get();
        $balanceTerpakai = (float) $successfulReportsByChannel->sum(function ($row) {
            $channel = (string) $row->channel_key;
            $price = self::CHANNEL_PRICES[$channel] ?? self::CHANNEL_PRICES['WABA'];

            return ((int) $row->total_rows) * $price;
        });
        $successfulChannelCounts = $successfulReportsByChannel
            ->pluck('total_rows', 'channel_key')
            ->map(fn ($count) => (int) $count);
        $smsDeliveredCount = $successfulChannelCounts->get('SMS', 0);
        $wabaDeliveredCount = $successfulChannelCounts->get('WABA', 0);
        $saldoKam = (float) $globalSaldo->balance;
        $sisaSaldo = $saldoKam - $balanceTerpakai;
        $saldoHistories = KamGlobalSaldoHistory::query()
            ->with('creator:id,name')
            ->where('kam_global_saldo_id', $globalSaldo->id)
            ->latest()
            ->limit(10)
            ->get();
        $tableRowCount = (clone $reportRowsQuery)->count('campaign_kam_reports.id');
        $canManageSaldo = $this->canManageSaldo();

        return view('campaign-kam.dashboard', compact(
            'campaigns',
            'selectedCampaignId',
            'updateFrom',
            'updateTo',
            'totalDelivered',
            'smsDeliveredCount',
            'wabaDeliveredCount',
            'totalRead',
            'totalRevenue',
            'sisaSaldo',
            'balanceTerpakai',
            'failedReportCount',
            'tableRowCount',
            'canManageSaldo',
            'saldoKam',
            'saldoHistories',
            'selectedCampaign',
            'showMissingUploadWarning',
            'selectedCampaignModel'
        ));
    }

    public function updateSaldo(Request $request)
    {
        $this->ensureKamDashboardAccess();
        abort_unless($this->canManageSaldo(), 403);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            $globalSaldo = $this->globalSaldo();
            $newBalance = (float) $globalSaldo->balance + (float) $validated['amount'];

            $globalSaldo->update([
                'balance' => $newBalance,
            ]);

            KamGlobalSaldoHistory::create([
                'kam_global_saldo_id' => $globalSaldo->id,
                'amount' => $validated['amount'],
                'balance_after' => $newBalance,
                'note' => $validated['note'] ?? null,
                'created_by' => auth()->id(),
            ]);
        });

        return redirect()
            ->route('campaign-kam-dashboard.index', array_filter([
                'campaign_id' => $request->campaign_id,
                'update_from' => $request->update_from,
                'update_to' => $request->update_to,
            ]))
            ->with('success', 'Saldo global KAM berhasil ditambahkan.');
    }

    public function data(Request $request)
    {
        $this->ensureKamDashboardAccess();

        $query = $this->detailReportQuery($request);

        return DataTables::of($query)
            ->editColumn('campaign_unique_id', fn ($row) => $row->campaign_unique_id ?? '-')
            ->editColumn('report_csv_uploaded_at', function ($row) {
                return $row->report_csv_uploaded_at
                    ? date('d-m-Y H:i', strtotime($row->report_csv_uploaded_at))
                    : 'Belum upload CSV';
            })
            ->editColumn('unique_id', fn ($row) => $row->unique_id ?? '-')
            ->editColumn('sender_id', fn ($row) => $row->sender_id ?? '-')
            ->editColumn('campaign_id', fn ($row) => $row->campaign_id ?? '-')
            ->editColumn('sender_name', fn ($row) => $row->sender_name ?? '-')
            ->editColumn('template_name', fn ($row) => $row->template_name ?? '-')
            ->editColumn('channel', fn ($row) => $row->channel ?? 'WABA')
            ->editColumn('msisdn', fn ($row) => $row->msisdn ?? '-')
            ->editColumn('status', function ($row) {
                $status = strtolower((string) $row->status);
                $statusClass = $status === 'succeeded' ? 'success' : ($status === 'failed' ? 'failed' : '');

                return '<span class="kam-status-badge ' . $statusClass . '">' . e($row->status ?? '-') . '</span>';
            })
            ->editColumn('send_date', function ($row) {
                return $row->send_date ? date('d-m-Y', strtotime($row->send_date)) : '-';
            })
            ->editColumn('deliv_report_status', fn ($row) => $row->deliv_report_status ?? '-')
            ->editColumn('deliv_report_date', function ($row) {
                return $row->deliv_report_date ? date('d-m-Y', strtotime($row->deliv_report_date)) : '-';
            })
            ->editColumn('deliv_report_time', fn ($row) => $row->deliv_report_time ?? '-')
            ->editColumn('deliv_read_date', function ($row) {
                return $row->deliv_read_date ? date('d-m-Y', strtotime($row->deliv_read_date)) : '-';
            })
            ->editColumn('deliv_read_time', fn ($row) => $row->deliv_read_time ?? '-')
            ->editColumn('note', function ($row) {
                return '<div class="kam-note-cell">' . e($row->note ?? '-') . '</div>';
            })
            ->rawColumns(['status', 'note'])
            ->make(true);
    }

    public function downloadCsv(Request $request): StreamedResponse
    {
        $this->ensureKamDashboardAccess();

        $selectedCampaignId = $request->filled('campaign_id') ? (int) $request->campaign_id : null;
        $selectedCampaign = $selectedCampaignId
            ? CampaignKam::query()->select('id', 'campaign_unique_id')->find($selectedCampaignId)
            : null;
        $fileName = $selectedCampaign?->campaign_unique_id
            ? 'kam_report_detail_' . strtolower(str_replace(' ', '_', $selectedCampaign->campaign_unique_id)) . '.csv'
            : 'kam_report_detail_all_campaigns.csv';

        $headers = [
            'Campaign Unique ID',
            'Last Update',
            'Unique ID',
            'Sender ID',
            'Campaign ID',
            'Sender Name',
            'Template Name',
            'Channel',
            'MSISDN',
            'Status',
            'Send Date',
            'Deliv Report Status',
            'Deliv Report Date',
            'Deliv Report Time',
            'Deliv Read Date',
            'Deliv Read Time',
            'Note',
        ];

        return response()->streamDownload(function () use ($request, $headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            $this->detailReportQuery($request)
                ->orderBy('campaign_kam_reports.id')
                ->chunk(1000, function ($rows) use ($handle) {
                    foreach ($rows as $row) {
                        fputcsv($handle, [
                            $row->campaign_unique_id ?? '-',
                            $row->report_csv_uploaded_at ? date('d-m-Y H:i', strtotime($row->report_csv_uploaded_at)) : 'Belum upload CSV',
                            $row->unique_id ?? '-',
                            $row->sender_id ?? '-',
                            $row->campaign_id ?? '-',
                            $row->sender_name ?? '-',
                            $row->template_name ?? '-',
                            $row->channel ?? 'WABA',
                            $row->msisdn ?? '-',
                            $row->status ?? '-',
                            $row->send_date ? date('d-m-Y', strtotime($row->send_date)) : '-',
                            $row->deliv_report_status ?? '-',
                            $row->deliv_report_date ? date('d-m-Y', strtotime($row->deliv_report_date)) : '-',
                            $row->deliv_report_time ?? '-',
                            $row->deliv_read_date ? date('d-m-Y', strtotime($row->deliv_read_date)) : '-',
                            $row->deliv_read_time ?? '-',
                            $row->note ?? '-',
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function detailReportQuery(Request $request)
    {
        $campaignIds = $this->accessibleCampaignsQuery()->pluck('id');
        $selectedCampaignId = $request->filled('campaign_id') ? (int) $request->campaign_id : null;
        [$updateFrom, $updateTo] = $this->resolveLastUpdateRange($request);

        $query = CampaignKamReport::query()
            ->leftJoin('campaign_kam', 'campaign_kam.id', '=', 'campaign_kam_reports.campaign_kam_id')
            ->whereIn('campaign_kam_reports.campaign_kam_id', $campaignIds)
            ->when($selectedCampaignId, function ($builder) use ($selectedCampaignId) {
                $builder->where('campaign_kam_reports.campaign_kam_id', $selectedCampaignId);
            })
            ->select([
                'campaign_kam_reports.*',
                'campaign_kam.campaign_unique_id as campaign_unique_id',
                'campaign_kam.report_csv_uploaded_at as report_csv_uploaded_at',
                'campaign_kam.channel as channel',
            ]);

        $this->applyLastUpdateFilter($query, $updateFrom, $updateTo, 'campaign_kam.report_csv_uploaded_at');

        return $query;
    }

    private function resolveLastUpdateRange(Request $request): array
    {
        $from = $this->parseDateBoundary($request->input('update_from'), false);
        $to = $this->parseDateBoundary($request->input('update_to'), true);

        return [$from, $to];
    }

    private function parseDateBoundary(?string $value, bool $endOfDay): ?Carbon
    {
        if (!$value) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', $value);
        } catch (\Throwable $exception) {
            return null;
        }

        return $endOfDay ? $date->endOfDay() : $date->startOfDay();
    }

    private function applyLastUpdateFilter($query, ?Carbon $from, ?Carbon $to, string $column): void
    {
        if ($from) {
            $query->where($column, '>=', $from);
        }

        if ($to) {
            $query->where($column, '<=', $to);
        }
    }
}
