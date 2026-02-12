@extends('master')

@section('title', 'Edit Mobile Campaign')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
    .periode-container { display: flex; gap: 20px; }
    .periode-container > div { flex: 1; }
    #kvPreview {
        max-width: 300px;
        margin-top: 10px;
        border: 1px solid #ccc;
        padding: 5px;
        border-radius: 5px;
        display: {{ $campaign->kv_message_link ? 'block' : 'none' }};
    }
    #whitelistPreview { margin-top: 5px; font-style: italic; }
</style>
@endsection

@section('content')
<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title">Edit Mobile Campaign</h3>
    </div>

    <div class="card-body">
        <form action="{{ route('campaign-mobile.update', $campaign->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- AREA --}}
            <div class="form-group">
                <label for="area">Area</label>
                <select id="area" name="area" class="form-control select2">
                    <option value="">-- Pilih --</option>
                    <option value="AREA 1" {{ old('area', $campaign->area) == 'AREA 1' ? 'selected' : '' }}>AREA 1</option>
                    <option value="AREA 2" {{ old('area', $campaign->area) == 'AREA 2' ? 'selected' : '' }}>AREA 2</option>
                    <option value="AREA 3" {{ old('area', $campaign->area) == 'AREA 3' ? 'selected' : '' }}>AREA 3</option>
                    <option value="AREA 4" {{ old('area', $campaign->area) == 'AREA 4' ? 'selected' : '' }}>AREA 4</option>
                </select>
                @error('area') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- REGION --}}
            <div class="form-group">
                <label for="region">Region</label>
                <select id="region" name="region" class="form-control select2">
                    <option value="">-- Pilih --</option>
                </select>
                @error('region') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- BRANCH --}}
            <div class="form-group">
                <label for="branch">Branch</label>
                <input type="text" id="branch" name="branch" class="form-control" value="{{ old('branch', $campaign->branch) }}">
                @error('branch') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- CAMPAIGN USECASE --}}
            <div class="form-group">
                <label for="campaign_usecase">Campaign Usecase</label>
                <select id="campaign_usecase" name="campaign_usecase" class="form-control select2">
                    <option value="">-- Pilih Campaign Usecase --</option>
                    @foreach(['ShortMax', 'Netflix', 'YouTube', 'MyTelkomsel'] as $usecase)
                        <option value="{{ $usecase }}" {{ old('campaign_usecase', $campaign->campaign_usecase) == $usecase ? 'selected' : '' }}>{{ $usecase }}</option>
                    @endforeach
                </select>
                @error('campaign_usecase') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- MESSAGE BODY --}}
            <div class="form-group">
                <label for="message_body">Message Body</label>
                <textarea id="message_body" name="message_body" class="form-control">{{ old('message_body', $campaign->message_body) }}</textarea>
                @error('message_body') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- KV MESSAGE LINK (Upload Image + Preview) --}}
            <div class="form-group">
                <label>KV (Key-Visual) Message</label>
                <input type="file" id="kv_message_link" name="kv_message_link" class="form-control" accept="image/*" onchange="previewKV(this)">
                <small class="text-muted">Preview:</small><br>
                <img id="kvPreview" src="{{ $campaign->kv_message_link ? asset('storage/campaign/kv-message/'.$campaign->kv_message_link) : '#' }}" alt="KV Preview">
                @error('kv_message_link') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            {{-- SHORTMAX USER TYPE --}}
            <div class="form-group">
                <label for="shortmax_user_type">User Type</label>
                <select id="shortmax_user_type" name="shortmax_user_type" class="form-control select2">
                    <option value="">-- Pilih User Type --</option>
                    @foreach(['Download', 'Belum Download'] as $type)
                        <option value="{{ $type }}"
                            {{ old('shortmax_user_type', $campaign->shortmax_user_type) == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
                @error('shortmax_user_type')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
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

            {{-- Whitelist TYPE --}}
            <div class="form-group" id="whitelistWrapper">
                <label>Whitelist File (Excel)</label>
                <input type="file"
                    name="nama_file_whitelist"
                    id="file_whitelist"
                    class="form-control"
                    accept=".xls,.xlsx">

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

            {{-- PERIODE CAMPAIGN START & END --}}
            <div class="periode-container">
                <div class="form-group">
                    <label for="periode_campaign_start">Periode Campaign Start</label>
                    <input type="date" id="periode_campaign_start" name="periode_campaign_start" class="form-control" value="{{ old('periode_campaign_start', $campaign->periode_campaign_start) }}">
                    @error('periode_campaign_start') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="form-group">
                    <label for="periode_campaign_end">Periode Campaign End</label>
                    <input type="date" id="periode_campaign_end" name="periode_campaign_end" class="form-control" value="{{ old('periode_campaign_end', $campaign->periode_campaign_end) }}">
                    @error('periode_campaign_end') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>

            {{-- JUMLAH BLAST --}}
            <div class="form-group">
                <label for="jumlah_blast">Jumlah Blast</label>
                <input type="number" min="0" id="jumlah_blast" name="jumlah_blast" class="form-control" value="{{ old('jumlah_blast', $campaign->jumlah_blast) }}">
                @error('jumlah_blast') <small class="text-danger">{{ $message }}</small> @enderror
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

            {{-- NAMA CAMPAIGN --}}
            <div class="form-group">
                <label for="nama_campaign">Nama Campaign</label>
                <input type="text" id="nama_campaign" name="nama_campaign" class="form-control" value="{{ old('nama_campaign', $campaign->nama_campaign) }}">
                @error('nama_campaign') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="form-group d-flex gap-2">
                <a href="{{ route('campaign-mobile.index') }}" class="btn btn-secondary flex-grow-1 m-1">Kembali</a>
                <button type="submit" id="submitBtn" class="btn btn-primary flex-grow-1 m-1">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>

<script>
    // Mapping area ke region
    const areaRegionMap = {
        'AREA 1': ['SUMBAGSEL', 'SUMBAGTENG', 'SUMBAGUT'],
        'AREA 2': ['JABODETABEK', 'JABAR'],
        'AREA 3': ['JATENG DIY', 'JATIM', 'BALI NUSRA'],
        'AREA 4': ['KALIMANTAN', 'SULAWESI', 'Papua Maluku']
    };

$(document).ready(function() {

    // Handle area change
    $('#area').on('change', function () {
        const selectedArea = $(this).val();
        const regionSelect = $('#region');
        const oldRegion = "{{ old('region', $campaign->region) }}";

        regionSelect.empty().append('<option value="">-- Pilih --</option>');

        if (selectedArea && areaRegionMap[selectedArea]) {
            areaRegionMap[selectedArea].forEach(function (region) {
                const selected = oldRegion === region ? 'selected' : '';
                regionSelect.append(`<option value="${region}" ${selected}>${region}</option>`);
            });
        }

        regionSelect.trigger('change.select2');
    });

    // Trigger area change on page load if area was previously selected
    if ($('#area').val()) {
        $('#area').trigger('change');
    }

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

    // Tombol submit disable setelah klik
    $('form').on('submit', function() {
        $('#submitBtn').attr('disabled', true);
    });
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

// Preview KV Image
function previewKV(input) {
    const preview = document.getElementById('kvPreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = '#';
        preview.style.display = 'none';
    }
}

// Preview Excel filename
function previewWhitelist(input) {
    const preview = document.getElementById('whitelistPreview');
    if (input.files && input.files[0]) {
        preview.textContent = 'File terpilih: ' + input.files[0].name;
    } else {
        preview.textContent = '';
    }
}
</script>
@endsection
