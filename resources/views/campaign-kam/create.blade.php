@extends('master')

@section('title', 'Create Campaign KAM')

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

    /* Image Preview */
    .image-preview {
        margin-top: 10px;
        display: none;
    }
    .image-preview img {
        max-width: 250px;
        border-radius: 8px;
        border: 1px solid #ddd;
        padding: 5px;
    }
</style>
@endsection

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Create Campaign KAM</h3>
    </div>

    <div class="card-body">
        <form action="{{ route('campaign-kam.store') }}" 
              method="POST" 
              enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="user_id" value="{{ auth()->id() }}">

            @if(in_array(auth()->user()->role, ['Admin', 'Super']))
            <div class="form-group">
                <label for="template_name">Nama Template</label>
                <input type="text" id="template_name" name="template_name" class="form-control" value="{{ old('template_name') }}">
                @error('template_name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            @endif
            @if(in_array(auth()->user()->role, ['Admin', 'Super']))
            <div class="form-group">
                <label for="vendor">Vendor</label>
                                <select id="vendor" name="vendor" class="form-control">
                    <option value="">-- Pilih Vendor --</option>
                    @foreach($vendors as $vendorOption)
                        <option value="{{ $vendorOption }}" {{ old('vendor') == $vendorOption ? 'selected' : '' }}>{{ $vendorOption }}</option>
                    @endforeach
                </select>
                @error('vendor') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            @endif

            <div class="form-group">
                <label for="sender_name">Sender Name</label>
                <select name="sender_name" id="sender_name" class="form-control select2">
                    <option value="">-- Pilih --</option>
                    @foreach($senderNameOptions as $senderNameOption)
                        <option value="{{ $senderNameOption }}" {{ old('sender_name') == $senderNameOption ? 'selected' : '' }}>
                            {{ $senderNameOption }}
                        </option>
                    @endforeach
                </select>
                @error('sender_name') <small class="text-danger">{{ $message }}</small> @enderror
                {{-- <small class="text-muted">Campaign ID akan dibuat otomatis dari sender name dan nomor ID data.</small> --}}
            </div>

            {{-- CAMPAIGN USECASE --}}
            <div class="form-group">
                <label>Campaign Usecase</label>
                <select name="campaign_usecase" class="form-control select2">
                    <option value="">-- Pilih --</option>
                    <option value="Sales Activation">Sales Activation</option>
                    <option value="Retention">Retention</option>
                    <option value="Reminder">Reminder</option>
                </select>
            </div>

            {{-- MESSAGE BODY --}}
            <div class="form-group">
                <label>Message Body</label>
                <textarea name="message_body" id="message_body" class="form-control">{{ old('message_body') }}</textarea>
            </div>

            {{-- KV MESSAGE IMAGE --}}
            <div class="form-group">
                <label>KV (Key-Visual) Message</label>
                <input type="file" 
                       name="kv_message_image" 
                       id="kv_message_image"
                       class="form-control"
                       accept="image/*">

                <div class="image-preview" id="kvPreview">
                    <img id="kvPreviewImg" src="#" alt="Preview Image">
                </div>

                <small class="text-muted">Format: JPG, JPEG, PNG</small>
            </div>

                        <div class="form-group">
                <label>Campaign Type</label>
                <select name="campaign_type" id="campaign_type" class="form-control select2">
                    <option value="Broadcast" selected>Broadcast</option>
                </select>
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
                @error('file_whitelist')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- LOKASI --}}
            <div class="form-row" id="lbaWrapper">
                <div class="form-group col-md-6">
                    <label>Longitude, Latitude</label>
                    <input type="text"
                        name="longitude_latitude"
                        id="longitude_latitude"
                        class="form-control"
                        placeholder="-6.200000,106.816666">
                    @error('longitude_latitude')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group col-md-6">
                    <label>Radius</label>
                    <input type="text"
                        name="radius"
                        id="radius"
                        class="form-control">
                    @error('radius')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
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

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Total Read</label>
                    <input type="number" name="total_read" class="form-control" value="{{ old('total_read') }}" min="0">
                </div>
                <div class="form-group col-md-6">
                    <label>Total Revenue</label>
                    <input type="number" step="0.01" name="total_revenue" class="form-control" value="{{ old('total_revenue') }}" min="0">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Sisa Saldo</label>
                    <input type="number" step="0.01" name="sisa_saldo" class="form-control" value="{{ old('sisa_saldo') }}" min="0">
                </div>
                <div class="form-group col-md-6">
                    <label>Balance Terpakai</label>
                    <input type="number" step="0.01" name="balance_terpakai" class="form-control" value="{{ old('balance_terpakai') }}" min="0">
                </div>
            </div>

            {{-- CC FILE --}}
            <div class="form-group">
                <label>Upload CC (Excel)</label>
                <input type="file"
                    name="cc"
                    class="form-control"
                    accept=".xls,.xlsx">

                <small class="text-muted">Format: XLS, XLSX</small>

                @error('cc')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            <hr>

            {{-- CAROUSEL --}}
            @for ($i = 1; $i <= 5; $i++)
            <div class="form-row">
                <div class="form-group col-md-8">
                    <label>Carousel Product {{ $i }}</label>
                    <textarea name="carousel_product_{{ $i }}" class="form-control"></textarea>
                </div>

                <div class="form-group col-md-4">
                    <label>KV Product {{ $i }}</label>
                    <input 
                        type="file" 
                        name="kv_product_{{ $i }}" 
                        class="form-control-file"
                        accept="image/*">
                    <small class="text-muted">Format: JPG, PNG, JPEG (Max 2MB)</small>
                </div>
            </div>
            <hr>
            @endfor

            <div class="form-group d-flex gap-2">
                <a href="{{ route('campaign-kam.index') }}" 
                   class="btn btn-secondary flex-grow-1 m-1">Kembali</a>
                <button type="submit" class="btn btn-primary flex-grow-1 m-1" 
                        onclick="this.disabled=true; this.form.submit();">
                    Simpan
                </button>
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
    function toggleCampaignType() {
        $('#file_whitelist').prop('disabled', false);
        $('#whitelistWrapper').show();
        $('#longitude_latitude, #radius').val('').prop('disabled', true);
        $('#lbaWrapper').hide();
    }

    toggleCampaignType();

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

    $('#kv_message_image').on('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (e) {
            $('#kvPreviewImg').attr('src', e.target.result);
            $('#kvPreview').show();
        };
        reader.readAsDataURL(file);
    });
});
</script>
@endsection








