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
        $request->validate([
            'user_id' => 'required',
            'area' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:255',
            'campaign_usecase' => 'nullable|string|max:255',
            'message_body' => 'nullable|string',
            'kv_message_link' => 'nullable|string|max:1000',
            'campaign_type' => 'nullable|string|max:255',
            'nama_file_whitelist' => 'nullable|string|max:255',
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
        // dd($request->all());    

        CampaignIndihome::create($request->all());

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
            'kv_message_link' => 'nullable|string|max:1000',
            'campaign_type' => 'nullable|string|max:255',
            'nama_file_whitelist' => 'nullable|string|max:255',
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

        $campaignIndihome->update($validated);

        return redirect()->route('campaign-indihome.index')->with('success', 'Campaign Indihome berhasil diperbarui!');
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
