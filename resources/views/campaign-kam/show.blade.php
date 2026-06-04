@extends('master')

@section('title', 'Detail Campaign KAM')

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
    .preview-image {
        max-width: 300px;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 5px;
        cursor: pointer;
        transition: 0.3s;
    }
    .preview-image:hover {
        transform: scale(1.05);
        border-color: #007bff;
    }
</style>
@endsection

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title font-weight-bold">Detail Campaign KAM</h3>
    </div>

    <div class="card-body">

        <div class="form-group">
            <label>Campaign ID</label>
            <input type="text" class="form-control" value="{{ $campaign->campaign_unique_id ?? '-' }}" readonly>
        </div>

        <div class="form-group">
            <label>Sender Name</label>
            <input type="text" class="form-control" value="{{ $campaign->sender_name ?? '-' }}" readonly>
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

        <div class="form-group">
            <label>Nama Template</label>
            <input type="text" class="form-control" value="{{ $campaign->template_name ?? '-' }}" readonly>
        </div>

        {{-- MESSAGE BODY --}}
        <div class="form-group">
            <label>Message Body</label>
            <textarea class="form-control" rows="5" readonly>{!! strip_tags($campaign->message_body) !!}</textarea>
        </div>

        <div class="form-group">
            <label>Text Button</label>
            <input type="text" class="form-control" value="{{ $campaign->text_button ?? '-' }}" readonly>
        </div>

        <div class="form-group">
            <label>Link Button</label>
            <input type="text" class="form-control" value="{{ $campaign->link_button ?? '-' }}" readonly>
        </div>

        {{-- KV MESSAGE IMAGE --}}
        <div class="form-group">
            <label>KV (Key-Visual) Message</label>
            @if($campaign->kv_message_link)
                <input type="text" class="form-control mb-2" value="{{ $campaign->kv_message_link }}" readonly>
                <img src="{{ asset('storage/campaign/kv-message/'.$campaign->kv_message_link) }}" 
                     alt="KV Message" class="preview-image" onclick="previewImage(this.src)">
            @else
                <input type="text" class="form-control" value="-" readonly>
            @endif
        </div>

        {{-- WHITELIST FILE --}}
        <div class="form-group">
            <label>Whitelist File (Excel)</label>
            @if($campaign->nama_file_whitelist)
                <input type="text" class="form-control mb-2" value="{{ $campaign->nama_file_whitelist }}" readonly>
                <a href="{{ asset('storage/campaign/whitelist/'.$campaign->nama_file_whitelist) }}" 
                   class="btn btn-sm btn-outline-primary" target="_blank">
                    Download {{ $campaign->nama_file_whitelist }}
                </a>
            @else
                <input type="text" class="form-control" value="-" readonly>
            @endif
        </div>

        {{-- LONG LAT --}}
        <div class="form-group">
            <label>Longitude & Latitude</label>
            <input type="text" class="form-control" value="{{ $campaign->longitude_latitude ?? '-' }}" readonly>
        </div>

        {{-- RADIUS --}}
        <div class="form-group">
            <label>Radius</label>
            <input type="text" class="form-control" value="{{ $campaign->radius ?? '-' }}" readonly>
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

        <div class="form-group">
            <label>Total Read</label>
            <input type="text" class="form-control" value="{{ number_format((float) ($campaign->total_read ?? 0), 0, ',', '.') }}" readonly>
        </div>

        <div class="form-group">
            <label>Total Revenue</label>
            <input type="text" class="form-control" value="Rp{{ number_format((float) ($campaign->total_revenue ?? 0), 0, ',', '.') }}" readonly>
        </div>

        <div class="form-group">
            <label>Sisa Saldo</label>
            <input type="text" class="form-control" value="Rp{{ number_format((float) ($campaign->sisa_saldo ?? 0), 0, ',', '.') }}" readonly>
        </div>

        <div class="form-group">
            <label>Balance Terpakai</label>
            <input type="text" class="form-control" value="Rp{{ number_format((float) ($campaign->balance_terpakai ?? 0), 0, ',', '.') }}" readonly>
        </div>

        <div class="form-group">
            <label>Last Update Data CSV</label>
            <input type="text" class="form-control" value="{{ $campaign->report_csv_uploaded_at ? $campaign->report_csv_uploaded_at->format('d-m-Y H:i') : '-' }}" readonly>
        </div>

        {{-- CC FILE --}}
        <div class="form-group">
            <label>CC File</label>
            @if($campaign->cc)
                <a href="{{ asset('storage/campaign/cc/'.$campaign->cc) }}" target="_blank">{{ basename($campaign->cc) }}</a>
            @else
                <span class="text-muted">-</span>
            @endif
        </div>

        {{-- CAROUSEL PRODUCT + KV --}}
        <hr>
        <h5 class="mb-3">Carousel Products</h5>
        <div class="row">
            @php $hasCarousel = false; @endphp
            @for($i = 1; $i <= 5; $i++)
                @php
                    $productName = $campaign->{'carousel_product_'.$i};
                    $productImage = $campaign->{'kv_product_'.$i};
                @endphp

                @if($productName || $productImage)
                    @php $hasCarousel = true; @endphp
                    <div class="col-md-4 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                <h6 class="font-weight-bold">Product {{ $i }}</h6>
                                <p class="text-muted">{{ $productName ?? '-' }}</p>
                                @if($productImage)
                                    <img 
                                        src="{{ asset('storage/campaign/kv-product/'.$productImage) }}"
                                        class="img-fluid rounded preview-image"
                                        style="max-height:200px"
                                        onclick="previewImage(this.src)">
                                @else
                                    <p class="text-muted small">Tidak ada gambar</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @endfor

            @if(!$hasCarousel)
                <div class="col-12">
                    <p class="text-muted">Tidak ada carousel product</p>
                </div>
            @endif
        </div>

        {{-- ACTION BUTTON --}}
        <div class="form-group d-flex gap-2 mt-3">
            <a href="{{ route('campaign-kam.index') }}" class="btn btn-secondary flex-grow-1 m-1">
                Kembali
            </a>
            <a href="{{ route('campaign-kam.edit', $campaign->id) }}" class="btn btn-warning flex-grow-1 m-1">
                Edit
            </a>
        </div>

    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function previewImage(url) {
    Swal.fire({
        imageUrl: url,
        imageAlt: 'Preview',
        showConfirmButton: false,
        showCloseButton: true,
        width: 700
    });
}
</script>
@endsection
