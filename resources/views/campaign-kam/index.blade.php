@extends('master')
@section('title') Campaign KAM @endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css" rel="stylesheet" />

<style>
    #loading-overlay {
        position: fixed; top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.7); z-index: 9999;
        display: none; justify-content: center; align-items: center;
    }
    #loading-message { font-size: 24px; color: white; text-align: center; }

    .table { background-color: #f9f9f9; border-radius: 8px; overflow: hidden; width: 100%; margin-top: 15px; border: 1px solid #ccc; table-layout: auto; }
    .table th, .table td { padding: 8px !important; font-size: 14px !important; border: 0.5px solid #ccc; color: #313131; text-align: center; vertical-align: middle; white-space: nowrap; }
    .table th { font-weight: bold; background-color: #343a40; color: #fff; }

    .btn-group-sm > .btn, .btn-sm { margin: 0 2px; }
    .badge-ok { background-color: #28a745; color: #fff; }
    .badge-no { background-color: #6c757d; color: #fff; }
</style>
@endsection

@section('content')
<div id="loading-overlay">
    <div id="loading-message">Loading, mohon tunggu...</div>
</div>

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title font-weight-bold">Campaign KAM</h3>
    </div>

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <a href="{{ route('campaign-kam.template.download') }}" class="btn btn-outline-secondary mb-2">
                <i class="fas fa-file-excel"></i> Download Template XLSX
            </a>
            <a href="{{ route('campaign-kam.create') }}" class="btn btn-info">
                <i class="fas fa-plus"></i> Tambah KAM
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm w-100" id="campaignKamTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Campaign ID</th>
                        <th>Vendor</th>
                        <th>Sender Name</th>
                        <th>Usecase</th>
                        <th>Campaign Type</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Jumlah Blast</th>
                        {{-- <th>Radius</th> --}}
                        <th>Nama Template</th>
                        <th>Report CSV</th>
                        <th>Status Testing</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

    </div>
</div>

<div class="modal fade" id="uploadReportModal" tabindex="-1" role="dialog" aria-labelledby="uploadReportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" id="uploadReportForm" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="uploadReportModalLabel">Upload XLSX Report KAM</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Campaign: <strong id="uploadReportCampaignLabel">-</strong></p>
                <div class="form-group mb-0">
                    <label for="report_csv">File Excel</label>
                    <input type="file" name="report_csv" id="report_csv" class="form-control" accept=".xlsx,.xls" required>
                    <small class="text-muted">Format harus mengikuti template XLSX. Upload baru akan mengganti data report lama campaign ini.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Upload</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let table = $('#campaignKamTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('campaign-kam.data') }}",
        order: [[0, 'desc']],
        columns: [
            { data: 'id', name: 'id' },
            { data: 'campaign_unique_id', name: 'campaign_unique_id' },
            { data: 'vendor', name: 'vendor' },
            { data: 'sender_name', name: 'sender_name' },
            { data: 'campaign_usecase', name: 'campaign_usecase' },
            { data: 'campaign_type', name: 'campaign_type' },
            { data: 'periode_campaign_start', name: 'periode_campaign_start' },
            { data: 'periode_campaign_end', name: 'periode_campaign_end' },
            { data: 'jumlah_blast', name: 'jumlah_blast' },
            // { data: 'radius', name: 'radius' },
            { data: 'template_name', name: 'template_name' },
            { data: 'report_csv_file', name: 'report_csv_file' },
            { data: 'status_testing', name: 'status_testing' },
            { data: 'status', name: 'status' },
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
        ],
        columnDefs: [
            { targets: '_all', className: 'text-center' }
        ]
    });

    // DELETE
    window.deleteCampaign = function(id) {
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: 'Data campaign akan dihapus permanen!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('campaign-kam') }}/" + id,
                    type: 'DELETE',
                    success: function (res) {
                        table.ajax.reload(null, false);
                        Swal.fire('Berhasil!', res.success ?? 'Data dihapus', 'success');
                    },
                    error: function () {
                        Swal.fire('Error!', 'Terjadi kesalahan.', 'error');
                    }
                });
            }
        });
    };
});

function activateCampaign(id) {
    Swal.fire({
        title: 'Activate campaign?',
        text: 'Campaign ini akan diaktifkan',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, activate'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(
                `/campaign-kam/${id}/activate`,
                {_token: '{{ csrf_token() }}'},
                function (res) {
                    Swal.fire('Berhasil!', res.message, 'success');
                    $('#campaignKamTable').DataTable().ajax.reload(null, false);
                }
            ).fail(function () {
                Swal.fire('Error!', 'Gagal activate campaign', 'error');
            });
        }
    });
}

function toggleTesting(id) {

    Swal.fire({
        title: 'Ubah status testing?',
        text: 'Status testing campaign akan diubah',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#007bff',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, ubah'
    }).then((result) => {

        if (result.isConfirmed) {

            $.post(
                `/campaign-kam/${id}/toggle-testing`,
                {_token: '{{ csrf_token() }}'},
                function (res) {

                    Swal.fire('Berhasil!', res.message, 'success');

                    $('#campaignKamTable')
                        .DataTable()
                        .ajax.reload(null, false);

                }
            ).fail(function () {

                Swal.fire(
                    'Error!',
                    'Gagal mengubah status testing',
                    'error'
                );

            });

        }

    });
}

function openUploadReportModal(id, campaignLabel, uploadUrl) {
    $('#uploadReportCampaignLabel').text(campaignLabel);
    $('#uploadReportForm').attr('action', uploadUrl);
    $('#report_csv').val('');
    $('#uploadReportModal').modal('show');
}


</script>

@endsection

