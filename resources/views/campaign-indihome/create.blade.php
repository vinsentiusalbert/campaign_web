@extends('master')

@section('title', 'Create Mobile Campaign')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- Summernote CSS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<style>
    .card-title { font-weight: bold; }
    .form-group label { font-weight: 600; }
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
    .text-danger { font-size: 13px; }
    /* Container for periode start/end side by side */
    .periode-container {
        display: flex;
        gap: 20px;
    }
    .periode-container > div {
        flex: 1;
    }
</style>
@endsection

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Create Mobile Campaign</h3>
    </div>

    <div class="card-body">
        <form action="{{ route('campaign-mobile.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
            {{-- AREA --}}
            <div class="form-group">
                <label for="area">Area</label>
                <input type="text" id="area" name="area" class="form-control" value="{{ old('area') }}">
                @error('area')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- REGION --}}
            <div class="form-group">
                <label for="region">Region</label>
                <input type="text" id="region" name="region" class="form-control" value="{{ old('region') }}">
                @error('region')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- BRANCH --}}
            <div class="form-group">
                <label for="branch">Branch</label>
                <input type="text" id="branch" name="branch" class="form-control" value="{{ old('branch') }}">
                @error('branch')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- CAMPAIGN USECASE (Dropdown) --}}
            <div class="form-group">
                <label for="campaign_usecase">Campaign Usecase</label>
                <select id="campaign_usecase" name="campaign_usecase" class="form-control select2">
                    <option value="">-- Pilih Campaign Usecase --</option>
                    @foreach(['ShortMax', 'Netflix', 'YouTube', 'MyTelkomsel'] as $usecase)
                        <option value="{{ $usecase }}" {{ old('campaign_usecase') == $usecase ? 'selected' : '' }}>{{ $usecase }}</option>
                    @endforeach
                </select>
                @error('campaign_usecase')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- MESSAGE BODY (Summernote WYSIWYG) --}}
            <div class="form-group">
                <label for="message_body">Message Body</label>
                <textarea id="message_body" name="message_body" class="form-control">{{ old('message_body') }}</textarea>
                @error('message_body')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- KV MESSAGE LINK --}}
            <div class="form-group">
                <label for="kv_message_link">KV Message Link (GDrive)</label>
                <input type="text" id="kv_message_link" name="kv_message_link" class="form-control" value="{{ old('kv_message_link') }}">
                @error('kv_message_link')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- SHORTMAX USER TYPE (Dropdown) --}}
            <div class="form-group">
                <label for="shortmax_user_type">Shortmax User Type</label>
                <select id="shortmax_user_type" name="shortmax_user_type" class="form-control select2">
                    <option value="">-- Pilih Shortmax User Type --</option>
                    @foreach(['Download', 'Belum Download'] as $type)
                        <option value="{{ $type }}" {{ old('shortmax_user_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
                @error('shortmax_user_type')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- NAMA FILE WHITELIST --}}
            <div class="form-group">
                <label for="nama_file_whitelist">Nama File Whitelist</label>
                <textarea id="nama_file_whitelist" name="nama_file_whitelist" class="form-control">{{ old('nama_file_whitelist') }}</textarea>
                @error('nama_file_whitelist')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- PERIODE CAMPAIGN START & END (side by side) --}}
            <div class="periode-container">
                <div class="form-group">
                    <label for="periode_campaign_start">Periode Campaign Start</label>
                    <input type="date" id="periode_campaign_start" name="periode_campaign_start" class="form-control" value="{{ old('periode_campaign_start') }}">
                    @error('periode_campaign_start')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="periode_campaign_end">Periode Campaign End</label>
                    <input type="date" id="periode_campaign_end" name="periode_campaign_end" class="form-control" value="{{ old('periode_campaign_end') }}">
                    @error('periode_campaign_end')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            {{-- JUMLAH BLAST (number only) --}}
            <div class="form-group">
                <label for="jumlah_blast">Jumlah Blast</label>
                <input type="number" min="0" id="jumlah_blast" name="jumlah_blast" class="form-control" value="{{ old('jumlah_blast') }}">
                @error('jumlah_blast')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- CC --}}
            <div class="form-group">
                <label for="cc">CC</label>
                <textarea id="cc" name="cc" class="form-control">{{ old('cc') }}</textarea>
                @error('cc')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- NAMA CAMPAIGN --}}
            <div class="form-group">
                <label for="nama_campaign">Nama Campaign</label>
                <input type="text" id="nama_campaign" name="nama_campaign" class="form-control" value="{{ old('nama_campaign') }}">
                @error('nama_campaign')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group d-flex gap-2">
                <a href="{{ route('campaign-mobile.index') }}" class="btn btn-secondary flex-grow-1 m-1">Kembali</a>
                <button type="submit" class="btn btn-primary flex-grow-1 m-1">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Summernote JS -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "-- Pilih --",
            allowClear: true,
            width: '100%'
        });

        $('#message_body').summernote({
            height: 300,
            toolbar: [
              ['style', ['bold', 'italic', 'underline', 'clear']],
              ['font', ['strikethrough']],
              ['para', ['ul', 'ol', 'paragraph']],
              ['insert', ['link', 'picture']],
              ['view', ['codeview']]
            ]
        });
    });
</script>
@endsection
