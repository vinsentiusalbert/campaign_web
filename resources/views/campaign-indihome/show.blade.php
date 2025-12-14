@extends('master')

@section('title', 'Detail Campaign Indihome')

@section('css')
<style>
    .form-group label {
        font-weight: 600;
    }
    .form-control[readonly],
    textarea[readonly] {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }
</style>
@endsection

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title font-weight-bold">Detail Campaign Indihome</h3>
    </div>

    <div class="card-body">

        {{-- AREA --}}
        <div class="form-group">
            <label>Area</label>
            <input type="text" class="form-control" value="{{ $campaign->area }}" readonly>
        </div>

        {{-- REGION --}}
        <div class="form-group">
            <label>Region</label>
            <input type="text" class="form-control" value="{{ $campaign->region }}" readonly>
        </div>

        {{-- BRANCH --}}
        <div class="form-group">
            <label>Branch</label>
            <input type="text" class="form-control" value="{{ $campaign->branch }}" readonly>
        </div>

        {{-- CAMPAIGN USECASE --}}
        <div class="form-group">
            <label>Campaign Usecase</label>
            <input type="text" class="form-control" value="{{ $campaign->campaign_usecase }}" readonly>
        </div>

        {{-- CAMPAIGN TYPE --}}
        <div class="form-group">
            <label>Campaign Type</label>
            <input type="text" class="form-control" value="{{ $campaign->campaign_type }}" readonly>
        </div>

        {{-- MESSAGE BODY --}}
        <div class="form-group">
            <label>Message Body</label>
            <textarea class="form-control" rows="5" readonly>{!! strip_tags($campaign->message_body) !!}</textarea>
        </div>

        {{-- KV MESSAGE LINK --}}
        <div class="form-group">
            <label>KV Message Link</label>
            <input type="text" class="form-control" value="{{ $campaign->kv_message_link }}" readonly>
        </div>

        {{-- WHITELIST FILE --}}
        <div class="form-group">
            <label>Nama File Whitelist</label>
            <input type="text" class="form-control" value="{{ $campaign->nama_file_whitelist }}" readonly>
        </div>

        {{-- LONG LAT --}}
        <div class="form-group">
            <label>Longitude & Latitude</label>
            <input type="text" class="form-control" value="{{ $campaign->longitude_latitude }}" readonly>
        </div>

        {{-- RADIUS --}}
        <div class="form-group">
            <label>Radius</label>
            <input type="text" class="form-control" value="{{ $campaign->radius }}" readonly>
        </div>

        {{-- PERIODE --}}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Periode Start</label>
                    <input type="text" class="form-control"
                        value="{{ $campaign->periode_campaign_start ? date('d-m-Y H:i', strtotime($campaign->periode_campaign_start)) : '-' }}"
                        readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Periode End</label>
                    <input type="text" class="form-control"
                        value="{{ $campaign->periode_campaign_end ? date('d-m-Y H:i', strtotime($campaign->periode_campaign_end)) : '-' }}"
                        readonly>
                </div>
            </div>
        </div>

        {{-- JUMLAH BLAST --}}
        <div class="form-group">
            <label>Jumlah Blast</label>
            <input type="number" class="form-control" value="{{ $campaign->jumlah_blast }}" readonly>
        </div>

        {{-- CAROUSEL PRODUCT --}}
        @for($i = 1; $i <= 5; $i++)
            <div class="form-group">
                <label>Carousel Product {{ $i }}</label>
                <input type="text" class="form-control"
                    value="{{ $campaign->{'carousel_product_'.$i} }}" readonly>
            </div>

            <div class="form-group">
                <label>KV Product {{ $i }}</label>
                <input type="text" class="form-control"
                    value="{{ $campaign->{'kv_product_'.$i} }}" readonly>
            </div>
        @endfor

        {{-- ACTION --}}
        <div class="form-group d-flex gap-2 mt-3">
            <a href="{{ route('campaign-indihome.index') }}" class="btn btn-secondary flex-grow-1">
                Kembali
            </a>

            <a href="{{ route('campaign-indihome.edit', $campaign->id) }}" class="btn btn-warning flex-grow-1">
                Edit
            </a>
        </div>

    </div>
</div>
@endsection
