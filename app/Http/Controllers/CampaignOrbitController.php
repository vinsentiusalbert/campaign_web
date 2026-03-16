<?php

namespace App\Http\Controllers;

use App\Models\CampaignOrbit;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\File;



class CampaignOrbitController extends Controller
{
    private function ensureCampaignAccess(CampaignOrbit $campaign): void
    {
        if (!in_array(auth()->user()->role, ['Admin', 'Super']) && $campaign->user_id !== auth()->id()) {
            abort(403);
        }
    }
    /**
     * Tampilkan semua campaign Orbit.
     */
    public function index()
    {
        $campaigns = CampaignOrbit::orderBy('created_at', 'desc')->paginate(10);
        return view('campaign-orbit.index', compact('campaigns'));
    }
    public function data(Request $request)
    {
        $query = CampaignOrbit::query()
            ->leftJoin('users', 'users.id', '=', 'campaign_orbit.user_id')
            ->select('campaign_orbit.*', 'users.vendor as vendor');

        // Jika bukan admin, hanya lihat data sendiri
        if (auth()->user()->role !== 'Admin' && auth()->user()->role !== 'Super') {
            $query->where('campaign_orbit.user_id', auth()->id());
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
            ->addColumn('aksi', function ($row) {
                $edit = route('campaign-orbit.edit', $row->id);
                $show = route('campaign-orbit.show', $row->id);

                $buttons = '
                    <a href="'.$show.'" class="btn btn-info btn-sm">Lihat</a>
                    <a href="'.$edit.'" class="btn btn-warning btn-sm">Edit</a>
                ';
                // tombol activate
                if (
                    in_array(Auth::user()->role, ['Admin', 'Super']) &&
                    $row->status == 0
                ) {
                    $activateUrl = route('campaign-orbit.activate', $row->id);
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
                if (Auth::user()->role === 'Admin' || Auth::user()->role === 'Super') {
                    $downloadUrl = route('campaign-orbit.download', $row->id);
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
            ->rawColumns(['aksi', 'status', 'status_testing'])
            ->make(true);
    }
    /**
     * Tampilkan form create campaign Orbit.
     */
    public function create()
    {
        return view('campaign-orbit.create');
    }

    /**
     * Simpan campaign Orbit baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',

            'area' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:255',

            'campaign_usecase' => 'nullable|string|in:Sales Activation',
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
            // CC FILE (EXCEL)
            'cc' => 'nullable|file|mimes:xlsx,xls|max:5120',
        ]);
        /* ===============================
        | HANDLE FILE UPLOAD
        =============================== */

        // 🔹 KV Message Image
        if ($request->hasFile('kv_message_image')) {

            $image = $request->file('kv_message_image');

            $imageName = time() . '_orbit_' . $image->getClientOriginalName();

            $image->storeAs('campaign/kv-message', $imageName, 'public');

            // SAVE ONLY FILE NAME
            $validated['kv_message_link'] = $imageName;
        }

        // 🔹 Whitelist Excel
        if ($request->hasFile('file_whitelist')) {

            $file = $request->file('file_whitelist');

            $fileName = time() . '_orbit_' . $file->getClientOriginalName();

            $file->storeAs('campaign/whitelist', $fileName, 'public');

            // SAVE ONLY FILE NAME
            $validated['nama_file_whitelist'] = $fileName;
        }
        // =========================
        // CC FILE
        // =========================
        if ($request->hasFile('cc')) {
            $ccFile = $request->file('cc');
            $ccFileName = time() . '_orbit_' . $ccFile->getClientOriginalName();

            $ccFile->storeAs('campaign/cc', $ccFileName, 'public');
            $validated['cc'] = $ccFileName;
        }
        // 🔹 KV Product 1 - 5
        for ($i = 1; $i <= 5; $i++) {

            if ($request->hasFile("kv_product_$i")) {

                $file = $request->file("kv_product_$i");

                $fileName = uniqid() . "_orbit_kv{$i}." . $file->getClientOriginalExtension();

                $file->storeAs('campaign/kv-product', $fileName, 'public');

                // Simpan hanya nama file
                $validated["kv_product_$i"] = $fileName;
            }
        }
        /* ===============================
        | SAVE DATA
        =============================== */

        CampaignOrbit::create($validated);

        return redirect()
            ->route('campaign-orbit.index')
            ->with('success', 'Campaign Orbit berhasil dibuat');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $campaign = CampaignOrbit::findOrFail($id);
        $this->ensureCampaignAccess($campaign);
        return view('campaign-orbit.show', compact('campaign'));
    }

    /**
     * Tampilkan form edit campaign Orbit.
     */
    public function edit($id)
    {
        $campaign = CampaignOrbit::findOrFail($id);
        $this->ensureCampaignAccess($campaign);

        return view('campaign-orbit.edit', compact('campaign'));
    }

    /**
     * Update campaign Orbit.
     */
    public function update(Request $request, CampaignOrbit $campaignOrbit)
    {
        $this->ensureCampaignAccess($campaignOrbit);
        $validated = $request->validate([
            'area' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:255',

            'campaign_usecase' => 'nullable|string|in:Sales Activation',
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

            // ✅ CC FILE (EXCEL)
            'cc' => 'nullable|file|mimes:xlsx,xls|max:5120',
        ]);
        if ($validated['campaign_type'] === 'LBA') {

            // 🔴 LBA → whitelist harus NULL
            if ($campaignOrbit->nama_file_whitelist) {
                Storage::disk('public')
                    ->delete('campaign/whitelist/' . $campaignOrbit->nama_file_whitelist);
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
            if ($campaignOrbit->kv_message_image) {
                Storage::disk('public')->delete(
                    'campaign/kv-message/'.$campaignOrbit->kv_message_image
                );
            }

            $image = $request->file('kv_message_image');
            $imageName = time().'_orbit_'.$image->getClientOriginalName();

            $image->storeAs('campaign/kv-message', $imageName, 'public');

            // save only name
            $validated['kv_message_link'] = $imageName;
        }

        /* ===============================
        | WHITELIST FILE
        =============================== */
        if ($request->hasFile('file_whitelist')) {

            // delete old file
            if ($campaignOrbit->file_whitelist) {
                Storage::disk('public')->delete(
                    'campaign/whitelist/'.$campaignOrbit->file_whitelist
                );
            }

            $file = $request->file('file_whitelist');
            $fileName = time().'_orbit_'.$file->getClientOriginalName();

            $file->storeAs('campaign/whitelist', $fileName, 'public');

            // save only name
            $validated['nama_file_whitelist'] = $fileName;
        }

        // =========================
        // CC FILE
        // =========================
        if ($request->hasFile('cc')) {

            if ($campaignOrbit->cc) {
                Storage::disk('public')->delete(
                    'campaign/cc/' . $campaignOrbit->cc
                );
            }

            $ccFile = $request->file('cc');
            $ccFileName = time() . '_orbit_' . $ccFile->getClientOriginalName();

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
                if ($campaignOrbit->{$dbField}) {
                    Storage::disk('public')->delete('campaign/kv-product/'.$campaignOrbit->{$dbField});
                }
                $file = $request->file($field);
                $fileName = time().'_'.$i.'_'.$file->getClientOriginalName();
                $file->storeAs('campaign/kv-product', $fileName, 'public');
                $validated[$dbField] = $fileName;
            }
        }
        /* ===============================
        | UPDATE DATA
        =============================== */
        $campaignOrbit->update($validated);

        return redirect()
            ->route('campaign-orbit.index')
            ->with('success', 'Campaign Orbit berhasil diperbarui!');
    }


    /**
     * Hapus campaign Orbit.
     */
    public function destroy(string $id)
    {
        $campaign = CampaignOrbit::findOrFail($id);
        $this->ensureCampaignAccess($campaign);
        // dd($campaign);
        $campaign->delete();

        return response()->json([
            'status' => true,
            'message' => 'Campaign Orbit berhasil dihapus'
        ]);
    }

    public function download($id)
    {
        $campaign = CampaignOrbit::findOrFail($id);
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
            'Area','Region','Branch','Campaign Usecase','Message Body','Campaign Type',
            'Message Body (Plain)','User Type',
            'Periode Start','Periode End','Jumlah Blast','Nama Campaign',
            'carousel_product_1','kv_product_1',
            'carousel_product_2','kv_product_2',
            'carousel_product_3','kv_product_3',
            'carousel_product_4','kv_product_4',
            'carousel_product_5','kv_product_5',
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
        $campaign = CampaignOrbit::findOrFail($id);

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
        $campaign = CampaignOrbit::findOrFail($id);

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


