<?php

namespace App\Http\Controllers;

use App\Models\CampaignIndihome;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\File;



class CampaignIndihomeController extends Controller
{
    /**
     * Tampilkan semua campaign Indihome.
     */
    public function index()
    {
        $campaigns = CampaignIndihome::orderBy('created_at', 'desc')->paginate(10);
        return view('campaign-indihome.index', compact('campaigns'));
    }
    public function data(Request $request)
    {
        $query = CampaignIndihome::query();

        // Jika bukan admin, hanya lihat data sendiri
        if (auth()->user()->role !== 'Admin' || auth()->user()->role !== 'Super') {
            $query->where('user_id', auth()->id());
        }

        return DataTables::of($query)
            ->addColumn('status', function ($row) {
                if ($row->status == 1) {
                    return '<span class="badge badge-success">Active</span>';
                }
                return '<span class="badge badge-secondary">Not Active</span>';
            })
            ->addColumn('aksi', function ($row) {
                $edit = route('campaign-indihome.edit', $row->id);
                $show = route('campaign-indihome.show', $row->id);

                $buttons = '
                    <a href="'.$show.'" class="btn btn-info btn-sm">Lihat</a>
                    <a href="'.$edit.'" class="btn btn-warning btn-sm">Edit</a>
                ';
                // tombol activate
                if (
                    in_array(Auth::user()->role, ['Admin', 'Super']) &&
                    $row->status == 0
                ) {
                    $activateUrl = route('campaign-indihome.activate', $row->id);
                    $buttons .= '
                        <button onclick="activateCampaign('.$row->id.')" 
                                class="btn btn-primary btn-sm">
                            Activate
                        </button>
                    ';
                }
                if (Auth::user()->role === 'Admin' || Auth::user()->role === 'Super') {
                    $downloadUrl = route('campaign-indihome.download', $row->id);
                    $buttons .= '
                        <a href="'.$downloadUrl.'" class="btn btn-success btn-sm">Download</a>
                    ';
                }

                $buttons .= '
                    <button onclick="deleteCampaign('.$row->id.')" class="btn btn-danger btn-sm">Hapus</button>
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
            ->rawColumns(['aksi', 'status'])
            ->make(true);
    }
    /**
     * Tampilkan form create campaign Indihome.
     */
    public function create()
    {
        return view('campaign-indihome.create');
    }

    /**
     * Simpan campaign Indihome baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',

            'area' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:255',

            'campaign_usecase' => 'nullable|string|max:255',
            'message_body' => 'nullable|string',

            // Image
            'kv_message_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            
            'campaign_type' => 'required|in:Broadcast,LBA',

            // Excel
            'file_whitelist' => 'nullable|file|mimes:xls,xlsx',

            'longitude_latitude' => 'nullable|string|max:255',
            'radius' => 'nullable|string|max:255',

            'periode_campaign_start' => 'nullable|date',
            'periode_campaign_end' => 'nullable|date|after_or_equal:periode_campaign_start',

            'jumlah_blast' => 'nullable|integer|min:0',
            'nama_template' => 'nullable|string|max:255',

            'carousel_product_1' => 'nullable|string|max:255',
            'kv_product_1' => 'nullable|string',
            'carousel_product_2' => 'nullable|string|max:255',
            'kv_product_2' => 'nullable|string',
            'carousel_product_3' => 'nullable|string|max:255',
            'kv_product_3' => 'nullable|string',
            'carousel_product_4' => 'nullable|string|max:255',
            'kv_product_4' => 'nullable|string',
            'carousel_product_5' => 'nullable|string|max:255',
            'kv_product_5' => 'nullable|string',
            // CC FILE (EXCEL)
            'cc' => 'nullable|file|mimes:xlsx,xls|max:5120',
        ]);
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
        /* ===============================
        | SAVE DATA
        =============================== */

        CampaignIndihome::create($validated);

        return redirect()
            ->route('campaign-indihome.index')
            ->with('success', 'Campaign Indihome berhasil dibuat');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $campaign = CampaignIndihome::findOrFail($id);
        return view('campaign-indihome.show', compact('campaign'));
    }

    /**
     * Tampilkan form edit campaign Indihome.
     */
    public function edit($id)
    {
        $campaign = CampaignIndihome::findOrFail($id);

        return view('campaign-indihome.edit', compact('campaign'));
    }

    /**
     * Update campaign Indihome.
     */
    public function update(Request $request, CampaignIndihome $campaignIndihome)
    {
        $validated = $request->validate([
            'area' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:255',

            'campaign_usecase' => 'nullable|string|max:255',
            'message_body' => 'nullable|string',

            // IMAGE
            'kv_message_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            'campaign_type' => 'required|in:Broadcast,LBA',

            // EXCEL
            'file_whitelist' => 'nullable|mimes:xls,xlsx|max:5120',

            'longitude_latitude' => 'nullable|string|max:255',
            'radius' => 'nullable|string|max:255',

            'periode_campaign_start' => 'nullable|date',
            'periode_campaign_end' => 'nullable|date|after_or_equal:periode_campaign_start',

            'jumlah_blast' => 'nullable|integer|min:0',
            'nama_template' => 'nullable|string|max:255',

            'carousel_product_1' => 'nullable|string|max:255',
            'kv_product_1' => 'nullable|string',
            'carousel_product_2' => 'nullable|string|max:255',
            'kv_product_2' => 'nullable|string',
            'carousel_product_3' => 'nullable|string|max:255',
            'kv_product_3' => 'nullable|string',
            'carousel_product_4' => 'nullable|string|max:255',
            'kv_product_4' => 'nullable|string',
            'carousel_product_5' => 'nullable|string|max:255',
            'kv_product_5' => 'nullable|string',

            // ✅ CC FILE (EXCEL)
            'cc' => 'nullable|file|mimes:xlsx,xls|max:5120',
        ]);
        if ($validated['campaign_type'] === 'LBA') {

            // 🔴 LBA → whitelist harus NULL
            if ($campaignIndihome->nama_file_whitelist) {
                Storage::disk('public')
                    ->delete('campaign/whitelist/' . $campaignIndihome->nama_file_whitelist);
            }

            $validated['nama_file_whitelist'] = null;

        } elseif ($validated['campaign_type'] === 'Broadcast') {

            // 🔴 Broadcast → LBA data harus NULL
            $validated['longitude_latitude'] = null;
            $validated['radius'] = null;
        }
        /* ===============================
        | KV MESSAGE IMAGE
        =============================== */
        if ($request->hasFile('kv_message_image')) {

            // delete old image
            if ($campaignIndihome->kv_message_image) {
                Storage::disk('public')->delete(
                    'campaign/kv-message/'.$campaignIndihome->kv_message_image
                );
            }

            $image = $request->file('kv_message_image');
            $imageName = time().'_'.$image->getClientOriginalName();

            $image->storeAs('campaign/kv-message', $imageName, 'public');

            // save only name
            $validated['kv_message_link'] = $imageName;
        }

        /* ===============================
        | WHITELIST FILE
        =============================== */
        if ($request->hasFile('file_whitelist')) {

            // delete old file
            if ($campaignIndihome->file_whitelist) {
                Storage::disk('public')->delete(
                    'campaign/whitelist/'.$campaignIndihome->file_whitelist
                );
            }

            $file = $request->file('file_whitelist');
            $fileName = time().'_'.$file->getClientOriginalName();

            $file->storeAs('campaign/whitelist', $fileName, 'public');

            // save only name
            $validated['nama_file_whitelist'] = $fileName;
        }

        // =========================
        // CC FILE
        // =========================
        if ($request->hasFile('cc')) {

            if ($campaignIndihome->cc) {
                Storage::disk('public')->delete(
                    'campaign/cc/' . $campaignIndihome->cc
                );
            }

            $ccFile = $request->file('cc');
            $ccFileName = time() . '_' . $ccFile->getClientOriginalName();

            $ccFile->storeAs('campaign/cc', $ccFileName, 'public');
            $validated['cc'] = $ccFileName;
        }

        /* ===============================
        | UPDATE DATA
        =============================== */
        $campaignIndihome->update($validated);

        return redirect()
            ->route('campaign-indihome.index')
            ->with('success', 'Campaign Indihome berhasil diperbarui!');
    }


    /**
     * Hapus campaign Indihome.
     */
    public function destroy(string $id)
    {
        $campaign = CampaignIndihome::findOrFail($id);
        // dd($campaign);
        $campaign->delete();

        return response()->json([
            'status' => true,
            'message' => 'Campaign Indihome berhasil dihapus'
        ]);
    }

    public function download($id)
    {
        $campaign = CampaignIndihome::findOrFail($id);

        if (!in_array(auth()->user()->role, ['Admin', 'Super']) && $campaign->user_id !== auth()->id()) {
            abort(403);
        }

        $tempPath = storage_path('app/public/temp');
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
            'Area','Region','Branch','Campaign Usecase', 'Message Body','Campaign Type',
            'Message Body','User Type',
            'Periode Start','Periode End',
            'Jumlah Blast','Nama Campaign',
            'carousel_product_1',
            'kv_product_1',
            'carousel_product_2',
            'kv_product_2',
            'carousel_product_3',
            'kv_product_3',
            'carousel_product_4',
            'kv_product_4',
            'carousel_product_5',
            'kv_product_5',
        ];

        $csvData[] = [
            $campaign->area,
            $campaign->region,
            $campaign->branch,
            $campaign->campaign_usecase,
            $campaign->message_body,
            $campaign->campaign_type,
            strip_tags($campaign->message_body),
            $campaign->shortmax_user_type,
            $campaign->periode_campaign_start,
            $campaign->periode_campaign_end,
            $campaign->jumlah_blast,
            $campaign->nama_template,
            $campaign
        ];

        $csvString = '';
        foreach ($csvData as $row) {
            $csvString .= '"' . implode('","', $row) . '"' . "\n";
        }

        $zip->addFromString('campaign_data.csv', $csvString);

        /**
         * ==========================
         * ADD ATTACHMENTS (SAFE)
         * ==========================
         */
        $attachments = [
            'KV_Image' => $campaign->kv_message_link
                ? 'campaign/kv-message/'.$campaign->kv_message_link
                : null,

            'Whitelist' => $campaign->nama_file_whitelist
                ? 'campaign/whitelist/'.$campaign->nama_file_whitelist
                : null,

            'CC' => $campaign->cc
                ? 'campaign/cc/'.$campaign->cc
                : null,
        ];

        foreach ($attachments as $folder => $relativePath) {

            if (!$relativePath) {
                continue;
            }

            if (Storage::disk('public')->exists($relativePath)) {
                $zip->addFile(
                    storage_path('app/public/'.$relativePath),
                    $folder.'/'.basename($relativePath)
                );
            }
        }

        $zip->close();

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }


    public function activate($id)
    {
        $campaign = CampaignIndihome::findOrFail($id);

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

}
