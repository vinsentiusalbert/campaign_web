<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CampaignMobile;
use Yajra\DataTables\Facades\DataTables;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Auth;


class CampaignMobileController extends Controller
{
    private function ensureCampaignAccess(CampaignMobile $campaign): void
    {
        if (!in_array(auth()->user()->role, ['Admin', 'Super']) && $campaign->user_id !== auth()->id()) {
            abort(403);
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $query = CampaignMobile::get();
        // dd($query);
        return view('campaign-mobile.index');
    }
    public function data(Request $request)
    {
        $query = CampaignMobile::query()
            ->leftJoin('users', 'users.id', '=', 'campaign_mobile.user_id')
            ->select('campaign_mobile.*', 'users.vendor as vendor');
        if (auth()->user()->role !== 'Admin' && auth()->user()->role !== 'Super') {
            $query->where('campaign_mobile.user_id', auth()->id());
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
            ->addColumn('status_testing', function ($row) {
                if ($row->status_testing == 1) {
                    return '<span class="badge badge-success">Active</span>';
                }
                return '<span class="badge badge-secondary">Not Active</span>';
            })
            ->addColumn('aksi', function ($row) {
                $edit = route('campaign-mobile.edit', $row->id);
                $show = route('campaign-mobile.show', $row->id);

                $buttons = '
                    <a href="'.$show.'" class="btn btn-info btn-sm">Lihat</a>
                    <a href="'.$edit.'" class="btn btn-warning btn-sm">Edit</a>
                ';
                // tombol activate
                if (
                    in_array(Auth::user()->role, ['Admin', 'Super']) &&
                    $row->status == 0
                ) {
                    $buttons .= '
                        <button onclick="activateCampaign('.$row->id.')" 
                                class="btn btn-primary btn-sm">
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
                if (Auth::user()->role === 'Admin' || auth()->user()->role === 'Super') {
                    $downloadUrl = route('campaign-mobile.download', $row->id);
                    $buttons .= '
                        <a href="'.$downloadUrl.'" class="btn btn-success btn-sm">Download</a>
                    ';
                }

                $buttons .= '
                    <button onclick="deleteCampaign('.$row->id.')" class="btn btn-danger btn-sm">Hapus</button>
                ';

                return $buttons;
            })
            ->rawColumns(['aksi', 'status', 'status_testing'])
            ->make(true);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('campaign-mobile.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi data
        $validated = $request->validate([
            'area' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:255',
            'campaign_usecase' => 'nullable|string|in:ShortMax,Netflix,YouTube,MyTelkomsel',
            'message_body' => 'nullable|string',

            // KV IMAGE
            'kv_message_link' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',

            'shortmax_user_type' => 'nullable|string|in:Download,Belum Download',

            'campaign_type' => 'required|in:Broadcast,LBA',

            // WHITELIST FILE
            'nama_file_whitelist' => 'nullable|file|mimes:xlsx,xls|max:5120',
            
            'longitude_latitude' => 'nullable|string|max:255',
            'radius' => 'nullable|string|max:255',

            'periode_campaign_start' => 'nullable|date',
            'periode_campaign_end' => 'nullable|date|after_or_equal:periode_campaign_start',
            'jumlah_blast' => 'nullable|integer|min:0',

            // CC FILE (EXCEL)
            'cc' => 'nullable|file|mimes:xlsx,xls|max:5120',

            'nama_campaign' => 'nullable|string|max:255',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status'] = 0;

        if ($validated['campaign_type'] === 'LBA') {
            $validated['nama_file_whitelist'] = null;
        }

        if ($validated['campaign_type'] === 'Broadcast') {
            $validated['longitude_latitude'] = null;
            $validated['radius'] = null;
        }

        // =========================
        // KV MESSAGE IMAGE
        // =========================
        if ($request->hasFile('kv_message_link')) {
            $image = $request->file('kv_message_link');
            $imageName = time() . '_' . $image->getClientOriginalName();

            $image->storeAs('campaign/kv-message', $imageName, 'public');
            $validated['kv_message_link'] = $imageName; // simpan nama file
        }

        // =========================
        // WHITELIST FILE
        // =========================
        if ($request->hasFile('nama_file_whitelist')) {
            $file = $request->file('nama_file_whitelist');
            $fileName = time() . '_' . $file->getClientOriginalName();

            $file->storeAs('campaign/whitelist', $fileName, 'public');
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

        // Simpan ke database
        CampaignMobile::create($validated);

        return redirect()
            ->route('campaign-mobile.index')
            ->with('success', 'Campaign mobile berhasil dibuat.');
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $campaign = CampaignMobile::findOrFail($id);
        $this->ensureCampaignAccess($campaign);
        return view('campaign-mobile.show', compact('campaign'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $campaign = CampaignMobile::findOrFail($id);
        $this->ensureCampaignAccess($campaign);

        return view('campaign-mobile.edit', compact('campaign'));
    }

    public function update(Request $request, $id)
    {
        $campaign = CampaignMobile::findOrFail($id);
        $this->ensureCampaignAccess($campaign);

        // Validasi data
        $validated = $request->validate([
            'area' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:255',
            'campaign_usecase' => 'nullable|string|in:ShortMax,Netflix,YouTube,MyTelkomsel',
            'message_body' => 'nullable|string',

            // KV IMAGE
            'kv_message_link' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',

            'shortmax_user_type' => 'nullable|string|in:Download,Belum Download',

            'campaign_type' => 'required|in:Broadcast,LBA',

            // WHITELIST FILE
            'nama_file_whitelist' => 'nullable|file|mimes:xlsx,xls|max:5120',
            
            'longitude_latitude' => 'nullable|string|max:255',
            'radius' => 'nullable|string|max:255',

            'periode_campaign_start' => 'nullable|date',
            'periode_campaign_end' => 'nullable|date|after_or_equal:periode_campaign_start',
            'jumlah_blast' => 'nullable|integer|min:0',

            // ✅ CC FILE (EXCEL)
            'cc' => 'nullable|file|mimes:xlsx,xls|max:5120',

            'nama_campaign' => 'nullable|string|max:255',
        ]);

        if ($validated['campaign_type'] === 'LBA') {
            if ($campaign->nama_file_whitelist) {
                Storage::disk('public')->delete(
                    'campaign/whitelist/' . $campaign->nama_file_whitelist
                );
            }
            $validated['nama_file_whitelist'] = null;
        }

        if ($validated['campaign_type'] === 'Broadcast') {
            $validated['longitude_latitude'] = null;
            $validated['radius'] = null;
        }

        // =========================
        // KV MESSAGE IMAGE
        // =========================
        if ($request->hasFile('kv_message_link')) {

            if ($campaign->kv_message_link) {
                Storage::disk('public')->delete(
                    'campaign/kv-message/' . $campaign->kv_message_link
                );
            }

            $image = $request->file('kv_message_link');
            $imageName = time() . '_' . $image->getClientOriginalName();

            $image->storeAs('campaign/kv-message', $imageName, 'public');
            $validated['kv_message_link'] = $imageName;
        }

        // =========================
        // WHITELIST FILE
        // =========================
        if ($request->hasFile('nama_file_whitelist')) {

            if ($campaign->nama_file_whitelist) {
                Storage::disk('public')->delete(
                    'campaign/whitelist/' . $campaign->nama_file_whitelist
                );
            }

            $file = $request->file('nama_file_whitelist');
            $fileName = time() . '_' . $file->getClientOriginalName();

            $file->storeAs('campaign/whitelist', $fileName, 'public');
            $validated['nama_file_whitelist'] = $fileName;
        }

        // =========================
        // CC FILE
        // =========================
        if ($request->hasFile('cc')) {

            if ($campaign->cc) {
                Storage::disk('public')->delete(
                    'campaign/cc/' . $campaign->cc
                );
            }

            $ccFile = $request->file('cc');
            $ccFileName = time() . '_' . $ccFile->getClientOriginalName();

            $ccFile->storeAs('campaign/cc', $ccFileName, 'public');
            $validated['cc'] = $ccFileName;
        }

        $campaign->update($validated);

        return redirect()
            ->route('campaign-mobile.index')
            ->with('success', 'Campaign berhasil diperbarui.');
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $campaign = CampaignMobile::findOrFail($id);
        $this->ensureCampaignAccess($campaign);

        if ($campaign->kv_message_link) {
            Storage::disk('public')->delete('campaign/kv-message/' . $campaign->kv_message_link);
        }
        if ($campaign->nama_file_whitelist) {
            Storage::disk('public')->delete('campaign/whitelist/' . $campaign->nama_file_whitelist);
        }
        if ($campaign->cc) {
            Storage::disk('public')->delete('campaign/cc/' . $campaign->cc);
        }

        $campaign->delete();

        return response()->json([
            'status' => true,
            'message' => 'Campaign berhasil dihapus'
        ]);
    }

    public function download($id)
    {
        $campaign = CampaignMobile::findOrFail($id);
        $this->ensureCampaignAccess($campaign);

        // Temp zip path
        $tempPath = storage_path('app/public/temp');
        if (!is_dir($tempPath)) {
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
         * CSV DATA
         * ==========================
         */
        $csvHeader = [
            'Area',
            'Region',
            'Branch',
            'Campaign Usecase',
            'Message Body',
            'User Type',
            'Periode Start',
            'Periode End',
            'Jumlah Blast',
            'Nama Campaign'
        ];

        $csvRow = [
            $campaign->area,
            $campaign->region,
            $campaign->branch,
            $campaign->campaign_usecase,
            strip_tags($campaign->message_body),
            $campaign->shortmax_user_type,
            $campaign->periode_campaign_start,
            $campaign->periode_campaign_end,
            $campaign->jumlah_blast,
            $campaign->nama_campaign,
        ];

        $csvString  = '"' . implode('","', $csvHeader) . '"' . "\n";
        $csvString .= '"' . implode('","', $csvRow) . '"' . "\n";

        $zip->addFromString('campaign_data.csv', $csvString);

        /**
         * ==========================
         * ATTACHMENTS
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

        return response()
            ->download($zipPath)
            ->deleteFileAfterSend(true);
    }
    public function activate($id)
    {
        $campaign = CampaignMobile::findOrFail($id);

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
    public function toggleTesting($id)
    {
        $campaign = CampaignMobile::findOrFail($id);

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
}

