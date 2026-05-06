<?php

namespace App\Http\Controllers;

use App\Models\CampaignKam;
use App\Models\CampaignKamReport;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CampaignKamDashboardController extends Controller
{
    private function ensureKamDashboardAccess(): void
    {
        abort_unless(in_array(auth()->user()->role, ['Admin', 'Super', 'KAM']), 403);
    }

    private function accessibleCampaignsQuery()
    {
        $campaignsQuery = CampaignKam::query()->orderBy('campaign_unique_id');

        if (auth()->user()->role === 'KAM') {
            $campaignsQuery->where('user_id', auth()->id());
        }

        return $campaignsQuery;
    }

    public function index(Request $request)
    {
        $this->ensureKamDashboardAccess();

        $campaignsQuery = $this->accessibleCampaignsQuery();
        $campaigns = $campaignsQuery->get(['id', 'campaign_unique_id', 'template_name', 'sender_name']);
        $selectedCampaignId = $request->filled('campaign_id') ? (int) $request->campaign_id : null;
        $campaignTableQuery = CampaignKam::query()
            ->whereIn('id', $campaigns->pluck('id'))
            ->orderByDesc('report_csv_uploaded_at')
            ->orderBy('campaign_unique_id');

        if ($selectedCampaignId) {
            $campaignTableQuery->where('id', $selectedCampaignId);
        }

        $campaignRows = $campaignTableQuery->get();
        $selectedCampaign = $selectedCampaignId ? $campaigns->firstWhere('id', $selectedCampaignId) : null;
        $selectedCampaignModel = $selectedCampaignId ? $campaignRows->first() : null;
        $showMissingUploadWarning = $selectedCampaignId && $selectedCampaignModel && $selectedCampaignModel->report_csv_uploaded_at === null;

        $totalRead = (int) $campaignRows->sum(fn ($row) => (int) ($row->total_read ?? 0));
        $totalRevenue = (float) $campaignRows->sum(fn ($row) => (float) ($row->total_revenue ?? 0));
        $sisaSaldo = (float) $campaignRows->sum(fn ($row) => (float) ($row->sisa_saldo ?? 0));

        $reportRowsQuery = CampaignKamReport::query()
            ->whereIn('campaign_kam_id', $campaigns->pluck('id'))
            ->when($selectedCampaignId, fn ($query) => $query->where('campaign_kam_id', $selectedCampaignId));

        $successfulReportCount = (clone $reportRowsQuery)
            ->whereRaw('LOWER(status) = ?', ['succeeded'])
            ->count();

        $failedReportCount = (clone $reportRowsQuery)
            ->whereRaw('LOWER(status) = ?', ['failed'])
            ->count();

        $totalDelivered = $successfulReportCount;
        $balanceTerpakai = $successfulReportCount * 309;
        $tableRowCount = (clone $reportRowsQuery)->count();

        return view('campaign-kam.dashboard', compact(
            'campaigns',
            'selectedCampaignId',
            'totalDelivered',
            'totalRead',
            'totalRevenue',
            'sisaSaldo',
            'balanceTerpakai',
            'failedReportCount',
            'tableRowCount',
            'selectedCampaign',
            'showMissingUploadWarning',
            'selectedCampaignModel'
        ));
    }

    public function data(Request $request)
    {
        $this->ensureKamDashboardAccess();

        $campaignIds = $this->accessibleCampaignsQuery()->pluck('id');
        $selectedCampaignId = $request->filled('campaign_id') ? (int) $request->campaign_id : null;

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
            ]);

        return DataTables::of($query)
            ->editColumn('campaign_unique_id', fn ($row) => $row->campaign_unique_id ?? '-')
            ->editColumn('report_csv_uploaded_at', function ($row) {
                return $row->report_csv_uploaded_at
                    ? date('d-m-Y H:i', strtotime($row->report_csv_uploaded_at))
                    : 'Belum upload CSV';
            })
            ->editColumn('campaign_id', fn ($row) => $row->campaign_id ?? '-')
            ->editColumn('created_date', function ($row) {
                return $row->created_date ? date('d-m-Y', strtotime($row->created_date)) : '-';
            })
            ->editColumn('created_time', fn ($row) => $row->created_time ?? '-')
            ->editColumn('sender_name', fn ($row) => $row->sender_name ?? '-')
            ->editColumn('template_name', fn ($row) => $row->template_name ?? '-')
            ->editColumn('category', fn ($row) => $row->category ?? '-')
            ->editColumn('msisdn', fn ($row) => $row->msisdn ?? '-')
            ->editColumn('status', function ($row) {
                $status = strtolower((string) $row->status);
                $statusClass = $status === 'succeeded' ? 'success' : ($status === 'failed' ? 'failed' : '');

                return '<span class="kam-status-badge ' . $statusClass . '">' . e($row->status ?? '-') . '</span>';
            })
            ->editColumn('vendor_ref_id', function ($row) {
                return '<div class="kam-ref-cell">' . e($row->vendor_ref_id ?? '-') . '</div>';
            })
            ->editColumn('sent_date', function ($row) {
                return $row->sent_date ? date('d-m-Y', strtotime($row->sent_date)) : '-';
            })
            ->editColumn('sent_time', fn ($row) => $row->sent_time ?? '-')
            ->editColumn('note', function ($row) {
                return '<div class="kam-note-cell">' . e($row->note ?? '-') . '</div>';
            })
            ->rawColumns(['status', 'vendor_ref_id', 'note'])
            ->make(true);
    }
}



