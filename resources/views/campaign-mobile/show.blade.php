@extends('master')

@section('title', 'Detail Mobile Campaign')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .detail-label {
        font-weight: 600;
        margin-bottom: 3px;
    }
    .detail-box {
        padding: 10px 15px;
        background: #f8f9fa;
        border-radius: 6px;
        border: 1px solid #ddd;
        margin-bottom: 15px;
        word-break: break-word;
    }
    .message-box {
        background: #fff;
        border-radius: 6px;
        border: 1px solid #ddd;
        padding: 15px;
        min-height: 150px;
    }
    .kv-preview {
        max-width: 300px;
        border: 1px solid #ccc;
        border-radius: 5px;
        padding: 5px;
        display: block;
        margin-top: 5px;
    }
</style>
@endsection

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Detail Mobile Campaign</h3>
    </div>

    <div class="card-body">

        {{-- AREA --}}
        <div>
            <div class="detail-label">Area</div>
            <div class="detail-box">{{ $campaign->area ?? '-' }}</div>
        </div>

        {{-- REGION --}}
        <div>
            <div class="detail-label">Region</div>
            <div class="detail-box">{{ $campaign->region ?? '-' }}</div>
        </div>

        {{-- BRANCH --}}
        <div>
            <div class="detail-label">Branch</div>
            <div class="detail-box">{{ $campaign->branch ?? '-' }}</div>
        </div>

        {{-- CAMPAIGN USECASE --}}
        <div>
            <div class="detail-label">Campaign Usecase</div>
            <div class="detail-box">{{ $campaign->campaign_usecase ?? '-' }}</div>
        </div>

        {{-- MESSAGE BODY --}}
        <div>
            <div class="detail-label">Message Body</div>
            <div class="message-box">{!! $campaign->message_body !!}</div>
        </div>

        {{-- KV MESSAGE LINK (Preview Image) --}}
        <div>
            <label>KV (Key-Visual) Message</label>
            <div class="detail-box">
                @if($campaign->kv_message_link)
                    <img src="{{ asset('storage/campaign/kv-message/'.$campaign->kv_message_link) }}" alt="KV Image" class="kv-preview">
                @else
                    -
                @endif
            </div>
        </div>

        {{-- SHORTMAX USER TYPE --}}
        <div>
            <div class="detail-label">User Type</div>
            <div class="detail-box">{{ $campaign->shortmax_user_type ?? '-' }}</div>
        </div>
        {{-- CAMPAIGN TYPE --}}
        <div class="form-group">
            <label>Campaign Type</label>
            <div class="detail-box">{{ $campaign->campaign_type }}</div>
        </div>

        <div>
            <div class="detail-label">Nama Template</div>
            <div class="detail-box">{{ $campaign->template_name ?? '-' }}</div>
        </div>

        {{-- NAMA FILE WHITELIST --}}
        <div>
            <div class="detail-label">Nama File Whitelist</div>
            <div class="detail-box">
                @if($campaign->nama_file_whitelist)
                    <a href="{{ asset('storage/campaign/whitelist/'.$campaign->nama_file_whitelist) }}" target="_blank">
                        {{ basename($campaign->nama_file_whitelist) }}
                    </a>
                @else
                    -
                @endif
            </div>
        </div>

        {{-- LONG LAT --}}
        <div class="form-group">
            <label>Longitude & Latitude</label>
            <div class="detail-box">{{ $campaign->longitude_latitude ?? '-' }}</div>
        </div>

        {{-- RADIUS --}}
        <div class="form-group">
            <label>Radius</label>
            <div class="detail-box">{{ $campaign->radius ?? '-' }}</div>
        </div>
        {{-- PERIODE --}}
        <div class="periode-container" style="display:flex; gap:20px;">
            <div style="flex:1;">
                <div class="detail-label">Periode Campaign Start</div>
                <div class="detail-box">{{ $campaign->periode_campaign_start ?? '-' }}</div>
            </div>
            <div style="flex:1;">
                <div class="detail-label">Periode Campaign End</div>
                <div class="detail-box">{{ $campaign->periode_campaign_end ?? '-' }}</div>
            </div>
        </div>

        {{-- JUMLAH BLAST --}}
        <div>
            <div class="detail-label">Jumlah Blast</div>
            <div class="detail-box">{{ $campaign->jumlah_blast ?? '-' }}</div>
        </div>

        {{-- CC --}}
        <div>
            <div class="detail-label">No CC</div>
            <div class="detail-box">
                @if($campaign->cc)
                    <a href="{{ asset('storage/campaign/cc/'.$campaign->cc) }}" target="_blank">
                        {{ basename($campaign->cc) }}
                    </a>
                @else
                    -
                @endif
            </div>
        </div>

        {{-- NAMA CAMPAIGN --}}
        <div>
            <div class="detail-label">Nama Campaign</div>
            <div class="detail-box">{{ $campaign->nama_campaign ?? '-' }}</div>
        </div>

        {{-- TOMBOL --}}
        <div class="form-group d-flex gap-2 mt-4">
            <a href="{{ route('campaign-mobile.index') }}" class="btn btn-secondary flex-grow-1 m-1">Kembali</a>
            <a href="{{ route('campaign-mobile.edit', $campaign->id) }}" class="btn btn-warning flex-grow-1 m-1">Edit</a>
        </div>

    </div>
</div>
@endsection
