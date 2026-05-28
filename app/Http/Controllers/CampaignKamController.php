<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\CampaignKam;
use App\Models\CampaignKamReport;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;




class CampaignKamController extends Controller
{
    private function vendorOptions()
    {
        return Vendor::query()
            ->orderBy('name')
            ->pluck('name');
    }

    private function ensureReportLinkAccess(): void
    {
        if (! in_array(auth()->user()->role, ['Admin', 'Super'])) {
            abort(403);
        }
    }
    private const REPORT_CSV_HEADERS = [
        'Campaign Id',
        'Created Date',
        'Created Time',
        'Sender Name',
        'Template Name',
        'Category',
        'MSISDN',
        'Status',
        'Vendor Ref Id',
        'Sent Date',
        'Sent Time',
        'Note',
    ];

    private const SENDER_NAME_OPTIONS = [
        'Halo Care',
        'Indihome Care',
        'PT KAM Via Telkomsel',
    ];

    private function ensureCampaignAccess(CampaignKam $campaign): void
    {
        $this->ensureKamModuleAccess();

        if (!in_array(auth()->user()->role, ['Admin', 'Super']) && $campaign->user_id !== auth()->id()) {
            abort(403);
        }
    }

    private function ensureKamModuleAccess(): void
    {
        if (! in_array(auth()->user()->role, ['Admin', 'Super', 'KAM'])) {
            abort(403);
        }
    }

    private function senderNameRules(): string
    {
        return 'required|string|in:' . implode(',', self::SENDER_NAME_OPTIONS);
    }

    private function buildCampaignUniqueId(string $senderName, int $id): string
    {
        $prefix = match ($senderName) {
            'Halo Care' => 'HC',
            'Indihome Care' => 'IC',
            'PT KAM Via Telkomsel' => 'PKVT',
            default => 'KAM',
        };

        return sprintf('%s-%04d', $prefix, $id);
    }
    /**
     * Tampilkan semua campaign KAM.
     */
    public function index()
    {
        $this->ensureKamModuleAccess();
        $campaigns = CampaignKam::orderBy('created_at', 'desc')->paginate(10);
        return view('campaign-kam.index', compact('campaigns'));
    }
    public function data(Request $request)
    {
        $this->ensureKamModuleAccess();
        $query = CampaignKam::query()
            ->select('campaign_kam.*');

        // Jika bukan admin, hanya lihat data sendiri
        if (auth()->user()->role !== 'Admin' && auth()->user()->role !== 'Super') {
            $query->where('campaign_kam.user_id', auth()->id());
        }

        return DataTables::of($query)
            ->addColumn('status', function ($row) {
                if ($row->status == 1) {
                    return '<span class="badge badge-success">Active</span>';
                }
                return '<span class="badge badge-secondary">Not Active</span>';
            })
            ->addColumn('status_testing', function ($row) {
                if ($row->status_testing == 1) {
                    return '<span class="badge badge-success">Active</span>';
                }
                return '<span class="badge badge-secondary">Not Active</span>';
            })
            ->editColumn('report_csv_file', function ($row) {
                if (!$row->report_csv_file) {
                    return '<span class="text-muted">Belum upload</span>';
                }

                $url = asset('storage/campaign/report-csv/' . $row->report_csv_file);

                return '<a href="' . $url . '" target="_blank" class="btn btn-outline-secondary btn-sm">Lihat XLSX</a>';
            })
            ->addColumn('aksi', function ($row) {
                $uploadUrl = route('campaign-kam.upload-report', $row->id);
                $edit = route('campaign-kam.edit', $row->id);
                $show = route('campaign-kam.show', $row->id);

                $buttons = '
                    <a href="'.$show.'" class="btn btn-info btn-sm">Lihat</a>
                    <a href="'.$edit.'" class="btn btn-warning btn-sm ml-1">Edit</a>
                    <button 
                        type="button"
                        class="btn btn-secondary btn-sm ml-1"
                        onclick="openUploadReportModal('.$row->id.', \''.e($row->campaign_unique_id ?? ('ID '.$row->id)).'\', \''.e($uploadUrl).'\')">
                        Upload XLSX
                    </button>
                ';

                // Activate Campaign
                if (
                    in_array(Auth::user()->role, ['Admin', 'Super']) &&
                    $row->status == 0
                ) {
                    $buttons .= '
                        <button onclick="activateCampaign('.$row->id.')" 
                                class="btn btn-primary btn-sm ml-1">
                            Activate
                        </button>
                    ';
                }

                // Activate / Non Active Testing
                if (in_array(Auth::user()->role, ['Admin', 'Super'])) {

                    if ($row->status_testing == 1) {
                        $buttons .= '
                            <button onclick="toggleTesting('.$row->id.')" 
                                    class="btn btn-outline-dark btn-sm ml-1">
                                Non Active Testing
                            </button>
                        ';
                    } else {
                        $buttons .= '
                            <button onclick="toggleTesting('.$row->id.')" 
                                    class="btn btn-outline-info btn-sm ml-1">
                                Activate Testing
                            </button>
                        ';
                    }
                }

                if (! empty($row->report_link)) {
                    $buttons .= '
                        <a href="'.e($row->report_link).'" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm ml-1">
                            Lihat Report
                        </a>
                    ';
                }

                if (in_array(Auth::user()->role, ['Admin', 'Super'])) {
                    $reportLinkUrl = route('campaign-kam.update-report-link', $row->id);
                    $currentReportLink = htmlspecialchars(json_encode($row->report_link), ENT_QUOTES, 'UTF-8');
                    $reportLinkUrlJs = htmlspecialchars(json_encode($reportLinkUrl), ENT_QUOTES, 'UTF-8');
                    $buttons .= '
                        <button type="button" class="btn btn-outline-secondary btn-sm ml-1" onclick="openReportLinkModal('.$row->id.', '.$currentReportLink.', '.$reportLinkUrlJs.')">
                            Tambah Link Report
                        </button>
                    ';
                }

                // Download
                if (in_array(Auth::user()->role, ['Admin', 'Super'])) {
                    $downloadUrl = route('campaign-kam.download', $row->id);
                    $buttons .= '
                        <a href="'.$downloadUrl.'" 
                        class="btn btn-success btn-sm ml-1">
                            Download
                        </a>
                    ';
                }

                // Delete
                $buttons .= '
                    <button onclick="deleteCampaign('.$row->id.')" 
                            class="btn btn-danger btn-sm ml-1">
                        Hapus
                    </button>
                ';

                return $buttons;
            })

            ->editColumn('periode_campaign_start', function ($row) {
                return $row->periode_campaign_start
                    ? date('d-m-Y H:i', strtotime($row->periode_campaign_start))
                    : '-';
            })
            ->editColumn('periode_campaign_end', function ($row) {
                return $row->periode_campaign_end
                    ? date('d-m-Y H:i', strtotime($row->periode_campaign_end))
                    : '-';
            })
            ->rawColumns(['aksi', 'status', 'status_testing', 'report_csv_file'])
            ->make(true);
    }
    /**
     * Tampilkan form create campaign KAM.
     */
    public function create()
    {
        $this->ensureKamModuleAccess();
        $senderNameOptions = self::SENDER_NAME_OPTIONS;
        $vendors = $this->vendorOptions();

        return view('campaign-kam.create', compact('senderNameOptions', 'vendors'));
    }

    /**
     * Simpan campaign KAM baru.
     */
    public function store(Request $request)
    {
        $this->ensureKamModuleAccess();
        $isPrivileged = in_array(auth()->user()->role, ['Admin', 'Super']);
        $canEditVendor = in_array(auth()->user()->role, ['Admin', 'Super']);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'sender_name' => $this->senderNameRules(),

            'campaign_usecase' => 'nullable|string|max:255',
            'message_body' => 'nullable|string',

            // Image
            'kv_message_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            
            'campaign_type' => 'nullable|in:Broadcast',

            // Excel
            'file_whitelist' => 'nullable|file|mimes:xls,xlsx',

            'longitude_latitude' => 'nullable|string|max:255',
            'radius' => 'nullable|string|max:255',

            'periode_campaign_start' => 'nullable|date',
            'periode_campaign_end' => 'nullable|date|after_or_equal:periode_campaign_start',

            'jumlah_blast' => 'nullable|integer|min:0',
            'total_read' => 'nullable|integer|min:0',
            'total_revenue' => 'nullable|numeric|min:0',
            'sisa_saldo' => 'nullable|numeric|min:0',
            'balance_terpakai' => 'nullable|numeric|min:0',
            'nama_template' => 'nullable|string|max:255',
            'template_name' => 'nullable|string|max:255',
            'vendor' => 'nullable|string|exists:vendors,name',

            'carousel_product_1' => 'nullable|string|max:255',
            'carousel_product_2' => 'nullable|string|max:255',
            'carousel_product_3' => 'nullable|string|max:255',
            'carousel_product_4' => 'nullable|string|max:255',
            'carousel_product_5' => 'nullable|string|max:255',
            'kv_product_1' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'kv_product_2' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'kv_product_3' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'kv_product_4' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'kv_product_5' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            // CC FILE (EXCEL)
            'cc' => 'nullable|file|mimes:xlsx,xls|max:5120',
        ]);

        $validated['campaign_type'] = 'Broadcast';

        if ($isPrivileged) {
            $validated['template_name'] = $validated['template_name'] ?? null;
            $validated['nama_template'] = $validated['template_name'];
        } else {
            unset($validated['template_name'], $validated['nama_template']);
        }

        if (! $canEditVendor) {
            unset($validated['vendor']);
        } else {
            $validated['vendor'] = $validated['vendor'] ?? null;
        }
        /* ===============================
        | HANDLE FILE UPLOAD
        =============================== */

        // 🔹 KV Message Image
        if ($request->hasFile('kv_message_image')) {

            $image = $request->file('kv_message_image');

            $imageName = time() . '_' . $image->getClientOriginalName();

            $image->storeAs('campaign/kv-message', $imageName, 'public');

            // SAVE ONLY FILE NAME
            $validated['kv_message_link'] = $imageName;
        }

        // 🔹 Whitelist Excel
        if ($request->hasFile('file_whitelist')) {

            $file = $request->file('file_whitelist');

            $fileName = time() . '_' . $file->getClientOriginalName();

            $file->storeAs('campaign/whitelist', $fileName, 'public');

            // SAVE ONLY FILE NAME
            $validated['nama_file_whitelist'] = $fileName;
        }
        // =========================
        // CC FILE
        // =========================
        if ($request->hasFile('cc')) {
            $ccFile = $request->file('cc');
            $ccFileName = time() . '_' . $ccFile->getClientOriginalName();

            $ccFile->storeAs('campaign/cc', $ccFileName, 'public');
            $validated['cc'] = $ccFileName;
        }
        // 🔹 KV Product 1 - 5
        for ($i = 1; $i <= 5; $i++) {

            if ($request->hasFile("kv_product_$i")) {

                $file = $request->file("kv_product_$i");

                $fileName = uniqid() . "_kv{$i}." . $file->getClientOriginalExtension();

                $file->storeAs('campaign/kv-product', $fileName, 'public');

                // Simpan hanya nama file
                $validated["kv_product_$i"] = $fileName;
            }
        }
        /* ===============================
        | SAVE DATA
        =============================== */

        $campaign = CampaignKam::create($validated);
        $campaign->campaign_unique_id = $this->buildCampaignUniqueId($campaign->sender_name, $campaign->id);
        $campaign->save();

        return redirect()
            ->route('campaign-kam.index')
            ->with('success', 'Campaign KAM berhasil dibuat');
    }

    public function downloadTemplate()
    {
        $this->ensureKamModuleAccess();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(self::REPORT_CSV_HEADERS, null, 'A1');

        foreach (range('A', 'L') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'kam_report_template_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return response()->download(
            $tempFile,
            'dummy_report_kam.xlsx',
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        )->deleteFileAfterSend(true);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->ensureKamModuleAccess();
        $campaign = CampaignKam::findOrFail($id);
        $this->ensureCampaignAccess($campaign);
        return view('campaign-kam.show', compact('campaign'));
    }

    /**
     * Tampilkan form edit campaign KAM.
     */
    public function edit($id)
    {
        $this->ensureKamModuleAccess();
        $campaign = CampaignKam::findOrFail($id);
        $this->ensureCampaignAccess($campaign);
        $senderNameOptions = self::SENDER_NAME_OPTIONS;

        return view('campaign-kam.edit', compact('campaign', 'senderNameOptions'));
    }

    /**
     * Update campaign KAM.
     */
    public function update(Request $request, CampaignKam $campaignKam)
    {
        $this->ensureKamModuleAccess();
        $this->ensureCampaignAccess($campaignKam);
        $isPrivileged = in_array(auth()->user()->role, ['Admin', 'Super']);
        $canEditVendor = in_array(auth()->user()->role, ['Admin', 'Super']);

        // VALIDASI
        $validated = $request->validate([
            'sender_name' => $this->senderNameRules(),
            'campaign_usecase' => 'nullable|string|max:255',
            'message_body' => 'nullable|string',
            'kv_message_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'campaign_type' => 'nullable|in:Broadcast',
            'file_whitelist' => 'nullable|mimes:xls,xlsx|max:5120',
            'longitude_latitude' => 'nullable|string|max:255',
            'radius' => 'nullable|string|max:255',
            'periode_campaign_start' => 'nullable|date',
            'periode_campaign_end' => 'nullable|date|after_or_equal:periode_campaign_start',
            'jumlah_blast' => 'nullable|integer|min:0',
            'total_read' => 'nullable|integer|min:0',
            'total_revenue' => 'nullable|numeric|min:0',
            'sisa_saldo' => 'nullable|numeric|min:0',
            'balance_terpakai' => 'nullable|numeric|min:0',
            'nama_template' => 'nullable|string|max:255',
            'template_name' => 'nullable|string|max:255',
            'vendor' => 'nullable|string|exists:vendors,name',
            'cc' => 'nullable|file|mimes:xls,xlsx|max:5120',
            // KV Product Images
            'kv_product_image_1' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'kv_product_image_2' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'kv_product_image_3' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'kv_product_image_4' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'kv_product_image_5' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            // Carousel text
            'carousel_product_1' => 'nullable|string|max:255',
            'carousel_product_2' => 'nullable|string|max:255',
            'carousel_product_3' => 'nullable|string|max:255',
            'carousel_product_4' => 'nullable|string|max:255',
            'carousel_product_5' => 'nullable|string|max:255',
        ]);

        $validated['campaign_type'] = 'Broadcast';

        if ($isPrivileged) {
            $validated['template_name'] = $validated['template_name'] ?? null;
            $validated['nama_template'] = $validated['template_name'];
        } else {
            unset($validated['template_name'], $validated['nama_template']);
        }

        if (! $canEditVendor) {
            unset($validated['vendor']);
        } else {
            $validated['vendor'] = $validated['vendor'] ?? null;
        }

        // HANDLE TYPE
        if ($validated['campaign_type'] === 'Broadcast') {
            $validated['longitude_latitude'] = null;
            $validated['radius'] = null;
        }

        // =========================
        // KV MESSAGE IMAGE
        // =========================
        if ($request->hasFile('kv_message_image')) {
            if ($campaignKam->kv_message_link) {
                Storage::disk('public')->delete('campaign/kv-message/'.$campaignKam->kv_message_link);
            }
            $image = $request->file('kv_message_image');
            $imageName = time().'_'.$image->getClientOriginalName();
            $image->storeAs('campaign/kv-message', $imageName, 'public');
            $validated['kv_message_link'] = $imageName;
        }

        // =========================
        // WHITELIST FILE
        // =========================
        if ($request->hasFile('file_whitelist')) {
            if ($campaignKam->nama_file_whitelist) {
                Storage::disk('public')->delete('campaign/whitelist/'.$campaignKam->nama_file_whitelist);
            }
            $file = $request->file('file_whitelist');
            $fileName = time().'_'.$file->getClientOriginalName();
            $file->storeAs('campaign/whitelist', $fileName, 'public');
            $validated['nama_file_whitelist'] = $fileName;
        }

        // =========================
        // CC FILE
        // =========================
        if ($request->hasFile('cc')) {
            if ($campaignKam->cc) {
                Storage::disk('public')->delete('campaign/cc/'.$campaignKam->cc);
            }
            $ccFile = $request->file('cc');
            $ccFileName = time().'_'.$ccFile->getClientOriginalName();
            $ccFile->storeAs('campaign/cc', $ccFileName, 'public');
            $validated['cc'] = $ccFileName;
        }

        // =========================
        // KV PRODUCT IMAGES
        // =========================
        for ($i = 1; $i <= 5; $i++) {
            $field = 'kv_product_image_'.$i;
            $dbField = 'kv_product_'.$i;

            if ($request->hasFile($field)) {
                // Delete old
                if ($campaignKam->{$dbField}) {
                    Storage::disk('public')->delete('campaign/kv-product/'.$campaignKam->{$dbField});
                }
                $file = $request->file($field);
                $fileName = time().'_'.$i.'_'.$file->getClientOriginalName();
                $file->storeAs('campaign/kv-product', $fileName, 'public');
                $validated[$dbField] = $fileName;
            }
        }

        // =========================
        // UPDATE DATABASE
        // =========================
        $validated['campaign_unique_id'] = $this->buildCampaignUniqueId(
            $validated['sender_name'],
            $campaignKam->id
        );

        $campaignKam->update($validated);

        return redirect()->route('campaign-kam.index')->with('success', 'Campaign KAM berhasil diperbarui!');
    }

    public function uploadReport(Request $request, string $id)
    {
        $this->ensureKamModuleAccess();
        $campaign = CampaignKam::findOrFail($id);
        $this->ensureCampaignAccess($campaign);

        $validated = $request->validate([
            'report_csv' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        try {
            $spreadsheet = IOFactory::load($validated['report_csv']->getRealPath());
        } catch (\Throwable $e) {
            return redirect()
                ->route('campaign-kam.index')
                ->with('error', 'File Excel tidak bisa dibaca.');
        }

        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();
        $highestColumn = $sheet->getHighestDataColumn();

        $header = $sheet->rangeToArray('A1:' . $highestColumn . '1', null, true, false, false)[0] ?? [];
        if (empty(array_filter($header, fn ($value) => trim((string) $value) !== ''))) {
            return redirect()
                ->route('campaign-kam.index')
                ->with('error', 'File Excel kosong.');
        }

        $normalizedHeader = array_map([$this, 'normalizeCsvHeader'], $header);
        $expectedHeader = array_map([$this, 'normalizeCsvHeader'], self::REPORT_CSV_HEADERS);

        if ($normalizedHeader !== $expectedHeader) {
            return redirect()
                ->route('campaign-kam.index')
                ->with('error', 'Format Excel tidak sesuai template dummy report KAM.');
        }

        $rowsToInsert = [];
        $now = now();
        for ($rowNumber = 2; $rowNumber <= $highestRow; $rowNumber++) {
            $row = [];
            for ($columnIndex = 1; $columnIndex <= count(self::REPORT_CSV_HEADERS); $columnIndex++) {
                $cell = $sheet->getCell(Coordinate::stringFromColumnIndex($columnIndex) . $rowNumber);
                $row[] = $this->extractSpreadsheetValue($cell);
            }

            if ($this->isEmptySpreadsheetRow($row)) {
                continue;
            }

            $msisdn = $this->parseMsisdnForStorage($row[6], $rowNumber);
            if ($msisdn['error']) {
                return redirect()
                    ->route('campaign-kam.index')
                    ->with('error', $msisdn['error']);
            }

            $rowsToInsert[] = [
                'campaign_kam_id' => $campaign->id,
                'campaign_id' => $this->cleanCsvValue($row[0]),
                'created_date' => $this->parseCsvDate($row[1]),
                'created_time' => $this->cleanCsvValue($row[2]),
                'sender_name' => $this->cleanCsvValue($row[3]),
                'template_name' => $this->cleanCsvValue($row[4]),
                'category' => $this->cleanCsvValue($row[5]),
                'msisdn' => $msisdn['value'],
                'status' => $this->cleanCsvValue($row[7]),
                'vendor_ref_id' => $this->cleanCsvValue($row[8]),
                'sent_date' => $this->parseCsvDate($row[9]),
                'sent_time' => $this->cleanCsvValue($row[10]),
                'note' => $this->cleanCsvValue($row[11]),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $csvFile = $validated['report_csv'];

        DB::transaction(function () use ($campaign, $rowsToInsert, $csvFile) {
            if ($campaign->report_csv_file) {
                Storage::disk('public')->delete('campaign/report-csv/'.$campaign->report_csv_file);
            }

            $reportFileName = time().'_'.$campaign->id.'_'.$csvFile->getClientOriginalName();
            $csvFile->storeAs('campaign/report-csv', $reportFileName, 'public');

            $campaign->reports()->delete();
            $campaign->update([
                'report_csv_file' => $reportFileName,
                'report_csv_uploaded_at' => now(),
            ]);

            if (! empty($rowsToInsert)) {
                foreach (array_chunk($rowsToInsert, 500) as $chunk) {
                    CampaignKamReport::insert($chunk);
                }
            }
        });

        return redirect()
            ->route('campaign-kam.index')
            ->with('success', count($rowsToInsert).' baris report Excel berhasil di-upload untuk campaign '.$campaign->campaign_unique_id.'.');
    }


    /**
     * Hapus campaign KAM.
     */
    public function destroy(string $id)
    {
        $this->ensureKamModuleAccess();
        $campaign = CampaignKam::findOrFail($id);
        $this->ensureCampaignAccess($campaign);

        if ($campaign->report_csv_file) {
            Storage::disk('public')->delete('campaign/report-csv/'.$campaign->report_csv_file);
        }

        $campaign->delete();

        return response()->json([
            'status' => true,
            'message' => 'Campaign KAM berhasil dihapus'
        ]);
    }

    public function download($id)
    {
        $this->ensureKamModuleAccess();
        $campaign = CampaignKam::findOrFail($id);
        $this->ensureCampaignAccess($campaign);

        $tempPath = storage_path('app/public/temp');
        if (!file_exists($tempPath)) {
            mkdir($tempPath, 0755, true);
        }

        $zipFileName = 'campaign_'.$campaign->id.'.zip';
        $zipPath = $tempPath.'/'.$zipFileName;

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Cannot create ZIP file');
        }

        /**
         * ==========================
         * CSV → DIRECT STRING
         * ==========================
         */
        $csvData = [];
        $csvData[] = [
            'Campaign Unique ID', 'Sender Name', 'Campaign Usecase', 'Message Body', 'Campaign Type',
            'Message Body (Plain)','User Type',
            'Periode Start','Periode End','Jumlah Blast','Nama Campaign',
            'carousel_product_1','kv_product_1',
            'carousel_product_2','kv_product_2',
            'carousel_product_3','kv_product_3',
            'carousel_product_4','kv_product_4',
            'carousel_product_5','kv_product_5',
        ];

        $csvData[] = [
            $campaign->campaign_unique_id,
            $campaign->sender_name,
            $campaign->campaign_usecase,
            $campaign->message_body,
            $campaign->campaign_type,
            strip_tags($campaign->message_body),
            $campaign->shortmax_user_type,
            $campaign->periode_campaign_start,
            $campaign->periode_campaign_end,
            $campaign->jumlah_blast,
            $campaign->nama_template,
            $campaign->carousel_product_1,
            $campaign->kv_product_1,
            $campaign->carousel_product_2,
            $campaign->kv_product_2,
            $campaign->carousel_product_3,
            $campaign->kv_product_3,
            $campaign->carousel_product_4,
            $campaign->kv_product_4,
            $campaign->carousel_product_5,
            $campaign->kv_product_5,
        ];

        $csvString = '';
        foreach ($csvData as $row) {
            $csvString .= '"' . implode('","', $row) . '"' . "\n";
        }

        $zip->addFromString('campaign_data.csv', $csvString);

        /**
         * ==========================
         * ADD ATTACHMENTS
         * ==========================
         */
        $attachments = [
            'KV_Message' => $campaign->kv_message_link ? 'campaign/kv-message/'.$campaign->kv_message_link : null,
            'Whitelist' => $campaign->nama_file_whitelist ? 'campaign/whitelist/'.$campaign->nama_file_whitelist : null,
            'CC' => $campaign->cc ? 'campaign/cc/'.$campaign->cc : null,
        ];

        foreach ($attachments as $folder => $relativePath) {
            if (!$relativePath) continue;
            if (Storage::disk('public')->exists($relativePath)) {
                $zip->addFile(
                    storage_path('app/public/'.$relativePath),
                    $folder.'/'.basename($relativePath)
                );
            }
        }

        /**
         * ==========================
         * ADD KV PRODUCT IMAGES
         * ==========================
         */
        for ($i = 1; $i <= 5; $i++) {
            $kvFile = $campaign->{'kv_product_'.$i};
            if ($kvFile && Storage::disk('public')->exists('campaign/kv-product/'.$kvFile)) {
                $zip->addFile(
                    storage_path('app/public/campaign/kv-product/'.$kvFile),
                    'KV_Product_'.$i.'/'.basename($kvFile)
                );
            }
        }

        $zip->close();

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }


    public function activate($id)
    {
        $this->ensureKamModuleAccess();
        $campaign = CampaignKam::findOrFail($id);

        // hanya Admin & Super
        if (!in_array(auth()->user()->role, ['Admin', 'Super'])) {
            abort(403);
        }

        $campaign->status = 1;
        $campaign->save();

        return response()->json([
            'success' => true,
            'message' => 'Campaign berhasil diaktifkan'
        ]);
    }

    public function updateReportLink(Request $request, $id)
    {
        $this->ensureReportLinkAccess();

        $campaign = CampaignKam::findOrFail($id);
        $this->ensureCampaignAccess($campaign);

        $validated = $request->validate([
            'report_link' => 'required|url|max:2048',
        ]);

        $campaign->report_link = $validated['report_link'];
        $campaign->save();

        return response()->json([
            'success' => true,
            'message' => 'Link report berhasil disimpan.',
        ]);
    }
    public function toggleTesting($id)
    {
        $this->ensureKamModuleAccess();
        $campaign = CampaignKam::findOrFail($id);

        // Optional: batasi hanya Admin & Super
        if (!in_array(auth()->user()->role, ['Admin', 'Super'])) {
            abort(403);
        }

        // Toggle value
        $campaign->status_testing = $campaign->status_testing == 1 ? 0 : 1;
        $campaign->save();

        return response()->json([
            'success' => true,
            'message' => 'Status testing berhasil diubah'
        ]);
    }

    private function normalizeCsvHeader(?string $value): string
    {
        return trim(str_replace("\xEF\xBB\xBF", '', (string) $value));
    }

    private function cleanCsvValue(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function parseCsvDate(?string $value): ?string
    {
        $value = $this->cleanCsvValue($value);

        if ($value === null) {
            return null;
        }

        try {
            return Carbon::createFromFormat('n/j/Y', $value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function isEmptyCsvRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($this->cleanCsvValue($value) !== null) {
                return false;
            }
        }

        return true;
    }

    private function isEmptySpreadsheetRow(array $row): bool
    {
        return $this->isEmptyCsvRow($row);
    }

    private function extractSpreadsheetValue($cell): ?string
    {
        $value = $cell->getValue();

        if ($value === null) {
            return null;
        }

        $formattedValue = $cell->getFormattedValue();

        return trim((string) $formattedValue) !== ''
            ? trim((string) $formattedValue)
            : trim((string) $value);
    }

    private function parseMsisdnForStorage(?string $value, int $rowNumber): array
    {
        $value = $this->cleanCsvValue($value);

        if ($value === null) {
            return [
                'value' => null,
                'error' => null,
            ];
        }

        if (preg_match('/e[+-]?\d+/i', $value)) {
            return [
                'value' => null,
                'error' => "MSISDN pada baris {$rowNumber} masih dalam format scientific notation ({$value}). Simpan CSV dengan format MSISDN angka penuh/text dulu, lalu upload ulang.",
            ];
        }

        if (! preg_match('/^\d+$/', $value)) {
            return [
                'value' => null,
                'error' => "MSISDN pada baris {$rowNumber} tidak valid ({$value}). MSISDN harus berupa angka penuh tanpa simbol atau spasi.",
            ];
        }

        return [
            'value' => $value,
            'error' => null,
        ];
    }

}















