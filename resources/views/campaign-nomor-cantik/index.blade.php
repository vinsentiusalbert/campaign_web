@extends('master')
@section('title') Campaign Nomor Cantik @endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
        <h3 class="card-title font-weight-bold">Campaign Nomor Cantik</h3>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('campaign-nomor-cantik.create') }}" class="btn btn-info" id="btn-add-campaign">
                <i class="fas fa-plus"></i> Tambah Campaign
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-striped table-hover w-100" id="CampaignNomorCantikTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Vendor</th>
                        <th>Area</th>
                        <th>Region</th>
                        <th>Branch</th>
                        <th>Usecase</th>
                        {{-- <th>Message Body</th>
                        <th>KV Link</th> --}}
                        <th>User Type</th>
                        <th>Nama Template</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Jumlah Blast</th>
                        <th>CC</th>
                        <th>Nama Campaign</th>
                        {{-- <th>Created At</th>
                        <th>Updated At</th> --}}
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
@endsection
<div class="modal fade" id="reportLinkModal" tabindex="-1" role="dialog" aria-labelledby="reportLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="reportLinkForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="reportLinkModalLabel">Tambah Link Report</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-0">
                    <label for="report_link">Link Report</label>
                    <input type="url" name="report_link" id="report_link" class="form-control" placeholder="https://..." required>
                    <small class="text-muted">Link report akan disimpan ke campaign yang dipilih.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" id="reportLinkSubmitButton">Simpan Link</button>
            </div>
        </form>
    </div>
</div>
@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function openReportLinkModal(id, currentLink, submitUrl) {
    $('#reportLinkForm').attr('action', submitUrl);
    $('#report_link').val(currentLink || '');
    $('#reportLinkModal').modal('show');
}

$(function () {
    $('#reportLinkForm').on('submit', function (e) {
        e.preventDefault();

        const form = $(this);
        const submitButton = $('#reportLinkSubmitButton');
        const defaultText = 'Simpan Link';

        submitButton.prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function (res) {
                $('#reportLinkModal').modal('hide');
                Swal.fire('Berhasil!', res.message || 'Link report berhasil disimpan.', 'success');
                $('#CampaignNomorCantikTable').DataTable().ajax.reload(null, false);
            },
            error: function (xhr) {
                let message = 'Gagal menyimpan link report.';

                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }

                    if (xhr.responseJSON.errors && xhr.responseJSON.errors.report_link) {
                        message = xhr.responseJSON.errors.report_link[0];
                    }
                }

                Swal.fire('Error!', message, 'error');
            },
            complete: function () {
                submitButton.prop('disabled', false).text(defaultText);
            }
        });
    });
});
</script>
<script>
$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    var table = $('#CampaignNomorCantikTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('campaign-nomor-cantik.data') }}",
        columns: [
            { data: 'id', name: 'id' },
            { data: 'vendor', name: 'vendor' },
            { data: 'area', name: 'area' },
            { data: 'region', name: 'region' },
            { data: 'branch', name: 'branch' },
            { data: 'campaign_usecase', name: 'campaign_usecase' },
            // { data: 'message_body', name: 'message_body' },
            // { data: 'kv_message_link', name: 'kv_message_link' },
            { data: 'shortmax_user_type', name: 'shortmax_user_type' },
            { data: 'template_name', name: 'template_name' },
            { data: 'periode_campaign_start', name: 'periode_campaign_start' },
            { data: 'periode_campaign_end', name: 'periode_campaign_end' },
            { data: 'jumlah_blast', name: 'jumlah_blast' },
            { data: 'cc', name: 'cc' },
            { data: 'nama_campaign', name: 'nama_campaign' },
            // { data: 'created_at', name: 'created_at' },
            // { data: 'updated_at', name: 'updated_at' },
            { data: 'status_testing', name: 'status_testing' },
            { data: 'status', name: 'status' },
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
        ],
        columnDefs: [{ targets: '_all', className: 'text-center' }]
    });

    window.deleteCampaign = function(id){
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: "Data campaign akan hilang!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!'
        }).then((result) => {
            if(result.isConfirmed){
                $.ajax({
                    url: "{{ url('campaign-nomor-cantik') }}/" + id,
                    type: 'DELETE',
                    success: function(res){
                        table.ajax.reload(null, false);
                        Swal.fire('Terhapus!', res.message ?? 'Data campaign berhasil dihapus', 'success');
                    },
                    error: function(){ Swal.fire('Error!', 'Terjadi kesalahan.', 'error'); }
                });
            }
        });
    }
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
                `/campaign-nomor-cantik/${id}/activate`,
                {_token: '{{ csrf_token() }}'},
                function (res) {
                    Swal.fire('Berhasil!', res.message, 'success');
                    $('#CampaignNomorCantikTable').DataTable().ajax.reload(null, false);
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
                `/campaign-nomor-cantik/${id}/toggle-testing`,
                {_token: '{{ csrf_token() }}'},
                function (res) {

                    Swal.fire('Berhasil!', res.message, 'success');

                    $('#CampaignNomorCantikTable')
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


</script>
@endsection