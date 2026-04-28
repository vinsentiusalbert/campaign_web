<?php

namespace App\Http\Controllers;

use App\Models\CampaignSoundbox;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\File;



class CampaignSoundboxController extends Controller
{
    private function ensureCampaignAccess(CampaignSoundbox $campaign): void
    {
        if (!in_array(auth()->user()->role, ['Admin', 'Super']) && $campaign->user_id !== auth()->id()) {
            abort(403);
        }
    }
    /**
     * Tampilkan semua Campaign Soundbox.
     */
    public function index()
    {
        $campaigns = CampaignSoundbox::orderBy('created_at', 'desc')->paginate(10);
        return view('campaign-soundbox.index', compact('campaigns'));
    }
    public function data(Request $request)
    {
        $query = CampaignSoundbox::query()
            ->leftJoin('users', 'users.id', '=', 'campaign_soundbox.user_id')
            ->select('campaign_soundbox.*', 'users.vendor as vendor');

        // Jika bukan admin, hanya lihat data sendiri
        if (auth()->user()->role !== 'Admin' && auth()->user()->role !== 'Super') {
            $query->where('campaign_soundbox.user_id', auth()->id());
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
                $edit = route('campaign-soundbox.edit', $row->id);
                $show = route('campaign-soundbox.show', $row->id);

                $buttons = '
                    <a href="'.$show.'" class="btn btn-info btn-sm">Lihat</a>
                    <a href="'.$edit.'" class="btn btn-warning btn-sm">Edit</a>
                ';
                // tombol activate
                if (
                    in_array(Auth::user()->role, ['Admin', 'Super']) &&
                    $row->status == 0
                ) {
                    $activateUrl = route('campaign-soundbox.activate', $row->id);
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
                    $downloadUrl = route('campaign-soundbox.download', $row->id);
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
     * Tampilkan form create Campaign Soundbox.
     */
    public function create()
    {
        return view('campaign-soundbox.create');
    }

    /**
     * Simpan Campaign Soundbox baru.
     */
    public function store(Request $request)
    {
        $isPrivileged = in_array(auth()->user()->role, ['Admin', 'Super']);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',

            'area' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:255',

            'campaign_usecase' => 'nullable|string|in:Rental,Beli Putus',
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
            'template_name' => 'nullable|string|max:255',

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

        if ($isPrivileged) {
            $validated['template_name'] = $validated['template_name'] ?? null;
            $validated['nama_template'] = $validated['template_name'];
        } else {
            unset($validated['template_name'], $validated['nama_template']);
        }
        /* ===============================
        | HANDLE FILE UPLOAD
        =============================== */

        // 🔹 KV Message Image
        if ($request->hasFile('kv_message_image')) {

            $image = $request->file('kv_message_image');

            $imageName = time() . '_Soundbox_' . $image->getClientOriginalName();

            $image->storeAs('campaign/kv-message', $imageName, 'public');

            // SAVE ONLY FILE NAME
            $validated['kv_message_link'] = $imageName;
        }

        // 🔹 Whitelist Excel
        if ($request->hasFile('file_whitelist')) {

            $file = $request->file('file_whitelist');

            $fileName = time() . '_Soundbox_' . $file->getClientOriginalName();

            $file->storeAs('campaign/whitelist', $fileName, 'public');

            // SAVE ONLY FILE NAME
            $validated['nama_file_whitelist'] = $fileName;
        }
        // =========================
        // CC FILE
        // =========================
        if ($request->hasFile('cc')) {
            $ccFile = $request->file('cc');
            $ccFileName = time() . '_Soundbox_' . $ccFile->getClientOriginalName();

            $ccFile->storeAs('campaign/cc', $ccFileName, 'public');
            $validated['cc'] = $ccFileName;
        }
        // 🔹 KV Product 1 - 5
        for ($i = 1; $i <= 5; $i++) {

            if ($request->hasFile("kv_product_$i")) {

                $file = $request->file("kv_product_$i");

                $fileName = uniqid() . "_soundbox_kv{$i}." . $file->getClientOriginalExtension();

                $file->storeAs('campaign/kv-product', $fileName, 'public');

                // Simpan hanya nama file
                $validated["kv_product_$i"] = $fileName;
            }
        }
        /* ===============================
        | SAVE DATA
        =============================== */

        CampaignSoundbox::create($validated);

        return redirect()
            ->route('campaign-soundbox.index')
            ->with('success', 'Campaign Soundbox berhasil dibuat');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $campaign = CampaignSoundbox::findOrFail($id);
        $this->ensureCampaignAccess($campaign);
        return view('campaign-soundbox.show', compact('campaign'));
    }

    /**
     * Tampilkan form edit Campaign Soundbox.
     */
    public function edit($id)
    {
        $campaign = CampaignSoundbox::findOrFail($id);
        $this->ensureCampaignAccess($campaign);

        return view('campaign-soundbox.edit', compact('campaign'));
    }

    /**
     * Update Campaign Soundbox.
     */
    public function update(Request $request, CampaignSoundbox $campaignSoundbox)
    {
        $this->ensureCampaignAccess($campaignSoundbox);
        $isPrivileged = in_array(auth()->user()->role, ['Admin', 'Super']);
        $validated = $request->validate([
            'area' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:255',

            'campaign_usecase' => 'nullable|string|in:Rental,Beli Putus',
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
            'template_name' => 'nullable|string|max:255',

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

        if ($isPrivileged) {
            $validated['template_name'] = $validated['template_name'] ?? null;
            $validated['nama_template'] = $validated['template_name'];
        } else {
            unset($validated['template_name'], $validated['nama_template']);
        }
        if ($validated['campaign_type'] === 'LBA') {

            // 🔴 LBA → whitelist harus NULL
            if ($campaignSoundbox->nama_file_whitelist) {
                Storage::disk('public')
                    ->delete('campaign/whitelist/' . $campaignSoundbox->nama_file_whitelist);
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
            if ($campaignSoundbox->kv_message_image) {
                Storage::disk('public')->delete(
                    'campaign/kv-message/'.$campaignSoundbox->kv_message_image
                );
            }

            $image = $request->file('kv_message_image');
            $imageName = time().'_Soundbox_'.$image->getClientOriginalName();

            $image->storeAs('campaign/kv-message', $imageName, 'public');

            // save only name
            $validated['kv_message_link'] = $imageName;
        }

        /* ===============================
        | WHITELIST FILE
        =============================== */
        if ($request->hasFile('file_whitelist')) {

            // delete old file
            if ($campaignSoundbox->file_whitelist) {
                Storage::disk('public')->delete(
                    'campaign/whitelist/'.$campaignSoundbox->file_whitelist
                );
            }

            $file = $request->file('file_whitelist');
            $fileName = time().'_Soundbox_'.$file->getClientOriginalName();

            $file->storeAs('campaign/whitelist', $fileName, 'public');

            // save only name
            $validated['nama_file_whitelist'] = $fileName;
        }

        // =========================
        // CC FILE
        // =========================
        if ($request->hasFile('cc')) {

            if ($campaignSoundbox->cc) {
                Storage::disk('public')->delete(
                    'campaign/cc/' . $campaignSoundbox->cc
                );
            }

            $ccFile = $request->file('cc');
            $ccFileName = time() . '_Soundbox_' . $ccFile->getClientOriginalName();

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
                if ($campaignSoundbox->{$dbField}) {
                    Storage::disk('public')->delete('campaign/kv-product/'.$campaignSoundbox->{$dbField});
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
        $campaignSoundbox->update($validated);

        return redirect()
            ->route('campaign-soundbox.index')
            ->with('success', 'Campaign Soundbox berhasil diperbarui!');
    }


    /**
     * Hapus Campaign Soundbox.
     */
    public function destroy(string $id)
    {
        $campaign = CampaignSoundbox::findOrFail($id);
        $this->ensureCampaignAccess($campaign);
        // dd($campaign);
        $campaign->delete();

        return response()->json([
            'status' => true,
            'message' => 'Campaign Soundbox berhasil dihapus'
        ]);
    }

    public function download($id)
    {
        $campaign = CampaignSoundbox::findOrFail($id);
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
        $campaign = CampaignSoundbox::findOrFail($id);

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
        $campaign = CampaignSoundbox::findOrFail($id);

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



