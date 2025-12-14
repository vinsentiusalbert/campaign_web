<?php

namespace App\Http\Controllers;

use App\Models\CampaignIndihome;
use Illuminate\Http\Request;

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
            'user_id' => 'required|integer|exists:users,id',
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

        CampaignIndihome::create($validated);

        return redirect()->route('campaign-indihome.index')->with('success', 'Campaign Indihome berhasil dibuat!');
    }

    /**
     * Tampilkan form edit campaign Indihome.
     */
    public function edit(CampaignIndihome $campaignIndihome)
    {
        return view('campaign-indihome.edit', compact('campaignIndihome'));
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
    public function destroy(CampaignIndihome $campaignIndihome)
    {
        $campaignIndihome->delete();
        return redirect()->route('campaign-indihome.index')->with('success', 'Campaign Indihome berhasil dihapus!');
    }
}
