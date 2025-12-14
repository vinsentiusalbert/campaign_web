@extends('master')

@section('title', 'Create Campaign Indihome')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">

<style>
    .form-group label { font-weight: 600; }
    .text-danger { font-size: 13px; }
    .select2-container .select2-selection--single {
        height: 35px !important;
        padding: 8px 12px;
        border: 1px solid #ced4da !important;
        border-radius: 6px !important;
        display: flex;
        align-items: center;
        font-size: 15px;
        background-color: #fff;
    }
    .periode-wrapper {
        display: flex;
        gap: 15px;
    }
    .periode-wrapper > div {
        flex: 1;
    }
</style>
@endsection

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Create Campaign Indihome</h3>
    </div>

    <div class="card-body">
        <form action="{{ route('campaign-indihome.store') }}" method="POST">
            @csrf
            <input type="hidden" name="user_id" value="{{ auth()->id() }}">

            {{-- AREA --}}
            <div class="form-group">
                <label>Area</label>
                <input type="text" name="area" class="form-control" value="{{ old('area') }}">
            </div>

            {{-- REGION --}}
            <div class="form-group">
                <label>Region</label>
                <input type="text" name="region" class="form-control" value="{{ old('region') }}">
            </div>

            {{-- BRANCH --}}
            <div class="form-group">
                <label>Branch</label>
                <input type="text" name="branch" class="form-control" value="{{ old('branch') }}">
            </div>

            {{-- CAMPAIGN USECASE --}}
            <div class="form-group">
                <label>Campaign Usecase</label>
                <select name="campaign_usecase" class="form-control select2">
                    <option value="">-- Pilih --</option>
                    <option value="Sales Activation">Sales Activation</option>
                    <option value="Retention">Retention</option>
                    <option value="YouTube">YouTube</option>
                    <option value="Reminder">Reminder</option>
                </select>
            </div>

            {{-- MESSAGE BODY --}}
            <div class="form-group">
                <label>Message Body</label>
                <textarea name="message_body" id="message_body" class="form-control">{{ old('message_body') }}</textarea>
            </div>

            {{-- KV MESSAGE LINK --}}
            <div class="form-group">
                <label>KV Message Link</label>
                <input type="text" name="kv_message_link" class="form-control" value="{{ old('kv_message_link') }}">
            </div>

            {{-- CAMPAIGN TYPE --}}
            <div class="form-group">
                <label>Campaign Type</label>
                <select name="campaign_type" class="form-control select2">
                    <option value="">-- Pilih --</option>
                    <option value="Whitelist">Whitelist</option>
                    <option value="LBA">LBA</option>
                </select>
            </div>

            {{-- WHITELIST --}}
            <div class="form-group">
                <label>Nama File Whitelist</label>
                <input type="text" name="nama_file_whitelist" class="form-control">
            </div>

            {{-- LOKASI --}}
            <div class="form-group">
                <label>Longitude, Latitude</label>
                <input type="text" name="longitude_latitude" class="form-control" placeholder="-6.200000,106.816666">
            </div>

            <div class="form-group">
                <label>Radius</label>
                <input type="text" name="radius" class="form-control">
            </div>

            {{-- PERIODE --}}
            <div class="periode-wrapper">
                <div class="form-group">
                    <label>Periode Campaign Start</label>
                    <input type="datetime-local" name="periode_campaign_start" class="form-control">
                </div>
                <div class="form-group">
                    <label>Periode Campaign End</label>
                    <input type="datetime-local" name="periode_campaign_end" class="form-control">
                </div>
            </div>

            {{-- JUMLAH BLAST --}}
            <div class="form-group">
                <label>Jumlah Blast</label>
                <input type="number" name="jumlah_blast" class="form-control">
            </div>

            <div class="form-group">
                <label>Nama Template</label>
                <input type="text" name="nama_template" class="form-control">
            </div>
            <hr>

            {{-- CAROUSEL PRODUCTS --}}
            @for ($i = 1; $i <= 5; $i++)
            <div class="form-row">
                <div class="form-group col-md-8">
                    <label>Carousel Product {{ $i }}</label>
                    <textarea name="carousel_product_{{ $i }}" class="form-control"></textarea>
                </div>

                <div class="form-group col-md-4">
                    <label>KV Product {{ $i }}</label>
                    <textarea name="kv_product_{{ $i }}" class="form-control"></textarea>
                </div>
            </div>
                <hr>
            @endfor

            <div class="form-group d-flex gap-2">
                <a href="{{ route('campaign-indihome.index') }}" class="btn btn-secondary flex-grow-1 m-1">Kembali</a>
                <button type="submit" class="btn btn-primary flex-grow-1 m-1">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>

<script>
$(document).ready(function () {
    $('.select2').select2({ width: '100%' });

    $('#message_body').summernote({
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
