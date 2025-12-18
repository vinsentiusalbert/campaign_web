<?php

namespace App\Http\Controllers;

use App\Models\CampaignIndihome;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

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
        if (auth()->user()->role !== 'Admin') {
            $query->where('user_id', auth()->id());
        }

        return DataTables::of($query)
            ->addColumn('aksi', function ($row) {
                $edit = route('campaign-indihome.edit', $row->id);
                $show = route('campaign-indihome.show', $row->id);

                return '
                    <a href="'.$show.'" class="btn btn-info btn-sm">Lihat</a>
                    <a href="'.$edit.'" class="btn btn-warning btn-sm">Edit</a>
                    <button onclick="deleteCampaign('.$row->id.')" class="btn btn-danger btn-sm">Hapus</button>
                ';
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
            ->rawColumns(['aksi'])
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

        /* ===============================
        | SAVE DATA
        =============================== */

        CampaignIndihome::create($validated);

        return redirect()
            ->route('campaign-indihome.index')
            ->with('success', 'Campaign berhasil dibuat');
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
        ]);

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
            'message' => 'Campaign berhasil dihapus'
        ]);
    }
}
