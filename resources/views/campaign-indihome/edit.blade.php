@extends('master')

@section('title', 'Edit Campaign Indihome')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css" rel="stylesheet">

<style>
    .form-group label { font-weight: 600; }
</style>
@endsection

@section('content')
<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title font-weight-bold">Edit Campaign Indihome</h3>
    </div>

    <form action="{{ route('campaign-indihome.update', $campaign->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card-body">

            {{-- AREA --}}
            <div class="form-group">
                <label>Area</label>
                <input type="text" name="area" class="form-control"
                    value="{{ old('area', $campaign->area) }}">
            </div>

            {{-- REGION --}}
            <div class="form-group">
                <label>Region</label>
                <input type="text" name="region" class="form-control"
                    value="{{ old('region', $campaign->region) }}">
            </div>

            {{-- BRANCH --}}
            <div class="form-group">
                <label>Branch</label>
                <input type="text" name="branch" class="form-control"
                    value="{{ old('branch', $campaign->branch) }}">
            </div>

            {{-- USECASE --}}
            <div class="form-group">
                <label>Campaign Usecase</label>
                <input type="text" name="campaign_usecase" class="form-control"
                    value="{{ old('campaign_usecase', $campaign->campaign_usecase) }}">
            </div>

            {{-- CAMPAIGN TYPE --}}
            <div class="form-group">
                <label>Campaign Type</label>
                <input type="text" name="campaign_type" class="form-control"
                    value="{{ old('campaign_type', $campaign->campaign_type) }}">
            </div>

            {{-- MESSAGE BODY --}}
            <div class="form-group">
                <label>Message Body</label>
                <textarea name="message_body"
                    class="form-control summernote">
                    {{ old('message_body', $campaign->message_body) }}
                </textarea>
            </div>

            {{-- KV MESSAGE LINK --}}
            <div class="form-group">
                <label>KV Message Link</label>
                <textarea name="kv_message_link" class="form-control"
                    rows="2">{{ old('kv_message_link', $campaign->kv_message_link) }}</textarea>
            </div>

            {{-- WHITELIST --}}
            <div class="form-group">
                <label>Nama File Whitelist</label>
                <input type="text" name="nama_file_whitelist"
                    class="form-control"
                    value="{{ old('nama_file_whitelist', $campaign->nama_file_whitelist) }}">
            </div>

            {{-- LOCATION --}}
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Longitude & Latitude</label>
                    <input type="text" name="longitude_latitude"
                        class="form-control"
                        value="{{ old('longitude_latitude', $campaign->longitude_latitude) }}">
                </div>
                <div class="form-group col-md-6">
                    <label>Radius</label>
                    <input type="text" name="radius"
                        class="form-control"
                        value="{{ old('radius', $campaign->radius) }}">
                </div>
            </div>

            {{-- PERIODE --}}
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Periode Start</label>
                    <input type="date" name="periode_campaign_start"
                        class="form-control"
                        value="{{ old('periode_campaign_start', optional($campaign->periode_campaign_start)->format('Y-m-d')) }}">
                </div>
                <div class="form-group col-md-6">
                    <label>Periode End</label>
                    <input type="date" name="periode_campaign_end"
                        class="form-control"
                        value="{{ old('periode_campaign_end', optional($campaign->periode_campaign_end)->format('Y-m-d')) }}">
                </div>
            </div>

            {{-- JUMLAH BLAST --}}
            <div class="form-group">
                <label>Jumlah Blast</label>
                <input type="number" name="jumlah_blast" class="form-control"
                    value="{{ old('jumlah_blast', $campaign->jumlah_blast) }}">
            </div>

            {{-- CAROUSEL PRODUCT 1 - 5 --}}
            @for($i=1; $i<=5; $i++)
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Carousel Product {{ $i }}</label>
                        <input type="text"
                            name="carousel_product_{{ $i }}"
                            class="form-control"
                            value="{{ old('carousel_product_'.$i, $campaign->{'carousel_product_'.$i}) }}">
                    </div>
                    <div class="form-group col-md-8">
                        <label>KV Product {{ $i }}</label>
                        <textarea name="kv_product_{{ $i }}"
                            class="form-control"
                            rows="2">{{ old('kv_product_'.$i, $campaign->{'kv_product_'.$i}) }}</textarea>
                    </div>
                </div>
                <hr>
            @endfor

        </div>

        <div class="form-group d-flex gap-2">
                <a href="{{ route('campaign-indihome.index') }}" class="btn btn-secondary flex-grow-1 m-1">Kembali</a>
                <button type="submit" class="btn btn-primary flex-grow-1 m-1">Update</button>
            </div>
    </form>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>

<script>
$(function () {
    $('.summernote').summernote({
        height: 250,
        toolbar: [
            ['style', ['bold', 'italic', 'underline']],
            ['para', ['ul', 'ol']],
            ['insert', ['link']],
            ['view', ['codeview']]
        ]
    });
});
</script>
@endsection
