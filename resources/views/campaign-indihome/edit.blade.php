@extends('master')

@section('title', 'Edit Campaign Indihome')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css" rel="stylesheet">

<style>
    .form-group label { font-weight: 600; }
    .select2-container .select2-selection--single {
        height: 35px !important;
        padding: 8px 12px;
    }
    .image-preview img {
        max-width: 250px;
        border-radius: 8px;
        margin-top: 10px;
        border: 1px solid #ddd;
        padding: 5px;
    }
</style>
@endsection

@section('content')
<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title">Edit Campaign Indihome</h3>
    </div>

    <form action="{{ route('campaign-indihome.update', $campaign->id) }}"
          method="POST"
          enctype="multipart/form-data">
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
                <select name="campaign_usecase" class="form-control select2">
                    <option value="">-- Pilih --</option>
                    @foreach(['Sales Activation','Retention','Reminder'] as $usecase)
                        <option value="{{ $usecase }}"
                            {{ old('campaign_usecase', $campaign->campaign_usecase) == $usecase ? 'selected' : '' }}>
                            {{ $usecase }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- MESSAGE BODY --}}
            <div class="form-group">
                <label>Message Body</label>
                <textarea name="message_body" class="form-control summernote">
                    {{ old('message_body', $campaign->message_body) }}
                </textarea>
            </div>

            {{-- KV MESSAGE IMAGE --}}
            <div class="form-group">
                <label>KV (Key-Visual) Message</label>
                <input type="file"
                    name="kv_message_image"
                    class="form-control"
                    accept="image/*">
                <small class="text-muted">Format: JPG, JPEG, PNG</small>

                @if($campaign->kv_message_link)
                    <div class="mt-2">
                        <p class="mb-1 text-muted">Current Image:</p>
                        <img src="{{ asset('storage/campaign/kv-message/'.$campaign->kv_message_link) }}"
                            alt="KV Image"
                            style="max-width: 250px; border:1px solid #ddd; border-radius:8px; padding:5px;">
                    </div>
                @endif
            </div>

            {{-- CAMPAIGN TYPE --}}
            <div class="form-group">
                <label>Campaign Type</label>
                <select name="campaign_type" id="campaign_type" class="form-control select2">
                    <option value="">-- Pilih --</option>
                    <option value="Broadcast"
                        {{ old('campaign_type', $campaign->campaign_type) == 'Broadcast' ? 'selected' : '' }}>
                        Broadcast 
                    </option>
                    <option value="LBA"
                        {{ old('campaign_type', $campaign->campaign_type) == 'LBA' ? 'selected' : '' }}>
                        LBA
                    </option>
                </select>
                @error('campaign_type')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- WHITELIST FILE --}}
            <div class="form-group" id="whitelistWrapper">
                <label>Upload Whitelist (Excel)</label>
                <input type="file"
                    name="file_whitelist"
                    id="file_whitelist"
                    class="form-control"
                    accept=".xls,.xlsx">

                <small class="text-muted">Format: XLS, XLSX</small>

                @if($campaign->nama_file_whitelist)
                    <div class="mt-2">
                        <p class="mb-1 text-muted">Current File:</p>
                        <a href="{{ asset('storage/campaign/whitelist/'.$campaign->nama_file_whitelist) }}"
                        target="_blank"
                        class="btn btn-sm btn-outline-primary">
                            Download {{ $campaign->nama_file_whitelist }}
                        </a>
                    </div>
                @endif
            </div>

            {{-- LOCATION (LBA) --}}
            <div class="form-row" id="lbaWrapper">
                <div class="form-group col-md-6">
                    <label>Longitude & Latitude</label>
                    <input type="text"
                        name="longitude_latitude"
                        id="longitude_latitude"
                        class="form-control"
                        value="{{ old('longitude_latitude', $campaign->longitude_latitude) }}"
                        placeholder="-6.200000,106.816666">
                </div>

                <div class="form-group col-md-6">
                    <label>Radius</label>
                    <input type="text"
                        name="radius"
                        id="radius"
                        class="form-control"
                        value="{{ old('radius', $campaign->radius) }}">
                </div>
            </div>


            {{-- PERIODE --}}
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Periode Start</label>
                    <input type="datetime-local" name="periode_campaign_start"
                           class="form-control"
                           value="{{ optional($campaign->periode_campaign_start)->format('Y-m-d\TH:i') }}">
                </div>
                <div class="form-group col-md-6">
                    <label>Periode End</label>
                    <input type="datetime-local" name="periode_campaign_end"
                           class="form-control"
                           value="{{ optional($campaign->periode_campaign_end)->format('Y-m-d\TH:i') }}">
                </div>
            </div>

            {{-- JUMLAH BLAST --}}
            <div class="form-group">
                <label>Jumlah Blast</label>
                <input type="number" name="jumlah_blast" class="form-control"
                       value="{{ old('jumlah_blast', $campaign->jumlah_blast) }}">
            </div>

            {{-- TEMPLATE --}}
            <div class="form-group" style="display: none; ">
                <label>Nama Template</label>
                <input type="text" name="nama_template" class="form-control"
                       value="{{ old('nama_template', $campaign->nama_template) }}">
            </div>
            
            {{-- CC --}}
            <div class="form-group">
                <label>No CC (Excel)</label>
                <input type="file"
                    name="cc"
                    class="form-control"
                    accept=".xls,.xlsx">

                @if($campaign->cc)
                    <div class="mt-2">
                        <p class="mb-1 text-muted">Current File:</p>
                        <a href="{{ asset('storage/campaign/cc/'.$campaign->cc) }}"
                        target="_blank"
                        class="btn btn-sm btn-outline-primary">
                            Download {{ $campaign->cc }}
                        </a>
                    </div>
                @endif
            </div>  

            <hr>

            {{-- CAROUSEL --}}
            @for($i=1; $i<=5; $i++)
                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label>Carousel Product {{ $i }}</label>
                        <textarea name="carousel_product_{{ $i }}" class="form-control">
                            {{ old('carousel_product_'.$i, $campaign->{'carousel_product_'.$i}) }}
                        </textarea>
                    </div>
                    <div class="form-group col-md-4">
                        <label>KV Product {{ $i }}</label>
                        <textarea name="kv_product_{{ $i }}" class="form-control">
                            {{ old('kv_product_'.$i, $campaign->{'kv_product_'.$i}) }}
                        </textarea>
                    </div>
                </div>
                <hr>
            @endfor

        </div>

        <div class="card-footer d-flex">
            <a href="{{ route('campaign-indihome.index') }}" class="btn btn-secondary flex-grow-1 mr-2">
                Kembali
            </a>
            <button type="submit" class="btn btn-primary flex-grow-1 " 
                        onclick="this.disabled=true; this.form.submit();">
                    Update
                </button>
        </div>
    </form>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>

<script>
$(document).ready(function () {
    
    function toggleCampaignType() {
        let type = $('#campaign_type').val();

        if (type === 'Broadcast') {

            // Enable whitelist
            $('#file_whitelist').prop('disabled', false);
            $('#whitelistWrapper').show();

            // Disable LBA
            $('#longitude_latitude, #radius')
                .val('')
                .prop('disabled', true);
            $('#lbaWrapper').hide();

        } else if (type === 'LBA') {

            // Enable LBA
            $('#longitude_latitude, #radius').prop('disabled', false);
            $('#lbaWrapper').show();

            // Disable whitelist
            $('#file_whitelist')
                .val('')
                .prop('disabled', true);
            $('#whitelistWrapper').hide();

        } else {
            // Default (belum pilih)
            $('#file_whitelist, #longitude_latitude, #radius')
                .val('')
                .prop('disabled', true);

            $('#whitelistWrapper, #lbaWrapper').hide();
        }
    }

    // Jalankan saat halaman edit dibuka
    toggleCampaignType();

    // Saat user ganti campaign type
    $('#campaign_type').on('change', function () {
        toggleCampaignType();
    });

});
$(document).ready(function () {
    $('.select2').select2({ width: '100%' });
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
