@extends('master')

@section('title', 'Edit Campaign KAM')

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
        cursor: pointer;
        transition: 0.3s;
    }
    .image-preview img:hover {
        transform: scale(1.05);
        border-color: #007bff;
    }
</style>
@endsection

@section('content')
<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title">Edit Campaign KAM</h3>
    </div>

    <form action="{{ route('campaign-kam.update', $campaign->id) }}"
          method="POST"
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card-body">

            @if(in_array(auth()->user()->role, ['Admin', 'Super']))
            <div class="form-group">
                <label for="template_name">Nama Template</label>
                <input type="text" id="template_name" name="template_name" class="form-control" value="{{ old('template_name', $campaign->template_name) }}">
                @error('template_name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            @endif

            <div class="form-group">
                <label for="sender_name">Sender Name</label>
                <select name="sender_name" id="sender_name" class="form-control select2">
                    <option value="">-- Pilih --</option>
                    @foreach($senderNameOptions as $senderNameOption)
                        <option value="{{ $senderNameOption }}" {{ old('sender_name', $campaign->sender_name) == $senderNameOption ? 'selected' : '' }}>
                            {{ $senderNameOption }}
                        </option>
                    @endforeach
                </select>
                @error('sender_name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label>Campaign ID</label>
                <input type="text" class="form-control" value="{{ $campaign->campaign_unique_id ?? '-' }}" readonly>
            </div>

            {{-- USECASE --}}
            <div class="form-group">
                <label>Campaign Usecase</label>
                <select name="campaign_usecase" class="form-control select2">
                    <option value="">-- Pilih --</option>
                    @foreach(['Sales Activation','Retention','Reminder'] as $usecase)
                        <option value="{{ $usecase }}" {{ old('campaign_usecase', $campaign->campaign_usecase) == $usecase ? 'selected' : '' }}>
                            {{ $usecase }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- MESSAGE BODY --}}
            <div class="form-group">
                <label>Message Body</label>
                <textarea name="message_body" class="form-control summernote">{{ old('message_body', $campaign->message_body) }}</textarea>
            </div>

            {{-- KV MESSAGE IMAGE --}}
            <div class="form-group">
                <label>KV (Key-Visual) Message</label>
                <input type="file" name="kv_message_image" class="form-control" accept="image/*">
                <small class="text-muted">Format: JPG, JPEG, PNG</small>

                @if($campaign->kv_message_link)
                    <div class="image-preview">
                        <p class="mb-1 text-muted">Current Image:</p>
                        <img src="{{ asset('storage/campaign/kv-message/'.$campaign->kv_message_link) }}" alt="KV Message" onclick="previewImage(this.src)">
                    </div>
                @endif
            </div>

                        <div class="form-group">
                <label>Campaign Type</label>
                <select name="campaign_type" id="campaign_type" class="form-control select2">
                    <option value="Broadcast" selected>Broadcast</option>
                </select>
            </div>

            {{-- WHITELIST --}}
            <div class="form-group" id="whitelistWrapper">
                <label>Upload Whitelist (Excel)</label>
                <input type="file" name="file_whitelist" id="file_whitelist" class="form-control" accept=".xls,.xlsx">
                @if($campaign->nama_file_whitelist)
                    <div class="mt-2">
                        <a href="{{ asset('storage/campaign/whitelist/'.$campaign->nama_file_whitelist) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            Download {{ $campaign->nama_file_whitelist }}
                        </a>
                    </div>
                @endif
            </div>

            {{-- LBA --}}
            <div class="form-row" id="lbaWrapper">
                <div class="form-group col-md-6">
                    <label>Longitude & Latitude</label>
                    <input type="text" name="longitude_latitude" class="form-control" value="{{ old('longitude_latitude', $campaign->longitude_latitude) }}">
                </div>
                <div class="form-group col-md-6">
                    <label>Radius</label>
                    <input type="text" name="radius" class="form-control" value="{{ old('radius', $campaign->radius) }}">
                </div>
            </div>

            {{-- PERIODE --}}
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Periode Start</label>
                    <input type="datetime-local" name="periode_campaign_start" class="form-control" value="{{ optional($campaign->periode_campaign_start)->format('Y-m-d\TH:i') }}">
                </div>
                <div class="form-group col-md-6">
                    <label>Periode End</label>
                    <input type="datetime-local" name="periode_campaign_end" class="form-control" value="{{ optional($campaign->periode_campaign_end)->format('Y-m-d\TH:i') }}">
                </div>
            </div>

            {{-- JUMLAH BLAST --}}
            <div class="form-group">
                <label>Jumlah Blast</label>
                <input type="number" name="jumlah_blast" class="form-control" value="{{ old('jumlah_blast', $campaign->jumlah_blast) }}">
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Total Read</label>
                    <input type="number" name="total_read" class="form-control" value="{{ old('total_read', $campaign->total_read) }}" min="0">
                </div>
                <div class="form-group col-md-6">
                    <label>Total Revenue</label>
                    <input type="number" step="0.01" name="total_revenue" class="form-control" value="{{ old('total_revenue', $campaign->total_revenue) }}" min="0">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Sisa Saldo</label>
                    <input type="number" step="0.01" name="sisa_saldo" class="form-control" value="{{ old('sisa_saldo', $campaign->sisa_saldo) }}" min="0">
                </div>
                <div class="form-group col-md-6">
                    <label>Balance Terpakai</label>
                    <input type="number" step="0.01" name="balance_terpakai" class="form-control" value="{{ old('balance_terpakai', $campaign->balance_terpakai) }}" min="0">
                </div>
            </div>

            {{-- CC --}}
            <div class="form-group">
                <label>No CC (Excel)</label>
                <input type="file" name="cc" class="form-control" accept=".xls,.xlsx">
                @if($campaign->cc)
                    <div class="mt-2">
                        <a href="{{ asset('storage/campaign/cc/'.$campaign->cc) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            Download {{ $campaign->cc }}
                        </a>
                    </div>
                @endif
            </div>

            <hr>

            {{-- CAROUSEL PRODUCT + KV IMAGE --}}
            <h5>Carousel Product & KV Product</h5>
            @for($i = 1; $i <= 5; $i++)
                <div class="form-row mb-3">
                    <div class="form-group col-md-6">
                        <label>Carousel Product {{ $i }}</label>
                        <textarea name="carousel_product_{{ $i }}" class="form-control">{{ old('carousel_product_'.$i, $campaign->{'carousel_product_'.$i}) }}</textarea>
                    </div>
                    <div class="form-group col-md-6">
                        <label>KV Product {{ $i }}</label>
                        <input type="file" name="kv_product_image_{{ $i }}" class="form-control" accept="image/*">
                        @php $kvImage = $campaign->{'kv_product_'.$i}; @endphp
                        @if($kvImage)
                            <div class="image-preview mt-2">
                                <img src="{{ asset('storage/campaign/kv-product/'.$kvImage) }}" alt="KV Product {{ $i }}" onclick="previewImage(this.src)">
                            </div>
                        @endif
                    </div>
                </div>
                <hr>
            @endfor

        </div>

        <div class="card-footer d-flex">
            <a href="{{ route('campaign-kam.index') }}" class="btn btn-secondary flex-grow-1 mr-2">Kembali</a>
            <button type="submit" class="btn btn-primary flex-grow-1" onclick="this.disabled=true; this.form.submit();">Update</button>
        </div>
    </form>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>

<script>
$(document).ready(function() {
    $('.select2').select2({ width: '100%' });
    $('.summernote').summernote({ height:250, toolbar:[['style',['bold','italic','underline']],['para',['ul','ol']],['insert',['link']],['view',['codeview']]] });

    function toggleCampaignType() {
        $('#file_whitelist').prop('disabled', false);
        $('#whitelistWrapper').show();
        $('#longitude_latitude,#radius').val('').prop('disabled', true);
        $('#lbaWrapper').hide();
    }
    toggleCampaignType();
});

// Image preview
function previewImage(url) {
    Swal.fire({ imageUrl: url, imageAlt:'Preview', showConfirmButton:false, showCloseButton:true, width:700 });
}
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection


