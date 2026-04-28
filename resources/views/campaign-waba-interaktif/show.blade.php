@extends('master')

@section('title', 'Detail Campaign WABA Interaktif')

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
    }
</style>
@endsection

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title font-weight-bold">Detail Campaign WABA Interaktif</h3>
    </div>

    <div class="card-body">

        <!-- {{-- AREA --}}
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
        </div> -->

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

        {{-- KV MESSAGE IMAGE --}}
        <div class="form-group">
            <label>KV (Key-Visual) Message</label>
            {{$campaign->kv_message_link}}
            @if($campaign->kv_message_link)
                <input type="text" class="form-control" value="{{ $campaign->kv_message_link }}" readonly>
                <div class="mb-2">
                    <img src="{{ asset('storage/campaign/kv-message/'.$campaign->kv_message_link) }}" 
                         alt="KV Message" class="preview-image">
                </div>
            @else
                <input type="text" class="form-control" value="-" readonly>
            @endif
        </div>

        {{-- WHITELIST FILE --}}
        <div class="form-group">
            <label>Whitelist File (Excel)</label>
            @if($campaign->nama_file_whitelist)
                <input type="text" class="form-control" value="{{ $campaign->nama_file_whitelist }}" readonly>
                <div class="mb-2">
                    <a href="{{ asset('storage/campaign/whitelist/'.$campaign->nama_file_whitelist) }}" 
                       class="btn btn-sm btn-outline-primary" target="_blank">
                        Download {{ $campaign->nama_file_whitelist }}
                    </a>
                </div>
            @else
                <input type="text" class="form-control" value="-" readonly>
            @endif
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
        <hr>
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

        {{-- ACTION --}}
        <div class="form-group d-flex gap-2 mt-3">
            <a href="{{ route('campaign-waba-interaktif.index') }}" class="btn btn-secondary flex-grow-1 m-1">
                Kembali
            </a>
            <a href="{{ route('campaign-waba-interaktif.edit', $campaign->id) }}" class="btn btn-warning flex-grow-1 m-1">
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
