<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CampaignMobile;
use Yajra\DataTables\Facades\DataTables;

class CampaignMobileController extends Controller
{
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
        $query = CampaignMobile::query();
        if (auth()->user()->role !== 'Admin') {
            $query->where('user_id', auth()->id());
        }

        return DataTables::of($query)
            ->addColumn('aksi', function($row){
                $editUrl = route('campaign-mobile.edit', $row->id);
                $showUrl = route('campaign-mobile.show', $row->id);

                return '
                    <a href="'.$showUrl.'" class="btn btn-info btn-sm">Lihat</a>
                    <a href="'.$editUrl.'" class="btn btn-warning btn-sm">Edit</a>
                    <button onclick="deleteCampaign('.$row->id.')" class="btn btn-danger btn-sm">Hapus</button>
                ';
            })
            ->rawColumns(['aksi'])
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
            'kv_message_link' => 'nullable|url|max:1000',
            'shortmax_user_type' => 'nullable|string|in:Download,Belum Download',
            'nama_file_whitelist' => 'nullable|string',
            'periode_campaign_start' => 'nullable|date',
            'periode_campaign_end' => 'nullable|date|after_or_equal:periode_campaign_start',
            'jumlah_blast' => 'nullable|integer|min:0',
            'cc' => 'nullable|string',
            'nama_campaign' => 'nullable|string|max:255',
        ]);
        
        $validated['user_id'] = auth()->id();

        // Simpan ke database
        $campaign = CampaignMobile::create($validated);

        // Redirect ke halaman index atau detail dengan pesan sukses
        return redirect()->route('campaign-mobile.index')
            ->with('success', 'Campaign mobile berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $campaign = CampaignMobile::findOrFail($id);
        return view('campaign-mobile.show', compact('campaign'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $campaign = CampaignMobile::findOrFail($id);

        return view('campaign-mobile.edit', compact('campaign'));
    }

    public function update(Request $request, $id)
    {
        $campaign = CampaignMobile::findOrFail($id);

        $validated = $request->validate([
            'area' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:255',
            'campaign_usecase' => 'nullable|string|in:ShortMax,Netflix,YouTube,MyTelkomsel',
            'message_body' => 'nullable|string',
            'kv_message_link' => 'nullable|url|max:1000',
            'shortmax_user_type' => 'nullable|string|in:Download,Belum Download',
            'nama_file_whitelist' => 'nullable|string',
            'periode_campaign_start' => 'nullable|date',
            'periode_campaign_end' => 'nullable|date|after_or_equal:periode_campaign_start',
            'jumlah_blast' => 'nullable|integer|min:0',
            'cc' => 'nullable|string',
            'nama_campaign' => 'nullable|string|max:255',
        ]);

        $campaign->update($validated);

        return redirect()->route('campaign-mobile.index')->with('success', 'Campaign berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $campaign = CampaignMobile::findOrFail($id);
        // dd($campaign);
        $campaign->delete();

        return response()->json([
            'status' => true,
            'message' => 'Campaign berhasil dihapus'
        ]);
    }
}
