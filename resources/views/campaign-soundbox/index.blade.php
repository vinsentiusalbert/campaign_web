@extends('master')
@section('title') Campaign Soundbox @endsection

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
        <h3 class="card-title font-weight-bold">Campaign Soundbox</h3>
    </div>

    <div class="card-body">

        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('campaign-soundbox.create') }}" class="btn btn-info">
                <i class="fas fa-plus"></i> Tambah Soundbox
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm w-100" id="CampaignSoundboxTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Area</th>
                        <th>Region</th>
                        <th>Branch</th>
                        <th>Usecase</th>
                        <th>Campaign Type</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Jumlah Blast</th>
                        {{-- <th>Radius</th> --}}
                        <th>Whitelist File</th>
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

    let table = $('#CampaignSoundboxTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('campaign-soundbox.data') }}",
        order: [[0, 'desc']],
        columns: [
            { data: 'id', name: 'id' },
            { data: 'area', name: 'area' },
            { data: 'region', name: 'region' },
            { data: 'branch', name: 'branch' },
            { data: 'campaign_usecase', name: 'campaign_usecase' },
            { data: 'campaign_type', name: 'campaign_type' },
            { data: 'periode_campaign_start', name: 'periode_campaign_start' },
            { data: 'periode_campaign_end', name: 'periode_campaign_end' },
            { data: 'jumlah_blast', name: 'jumlah_blast' },
            // { data: 'radius', name: 'radius' },
            { data: 'nama_file_whitelist', name: 'nama_file_whitelist' },
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
                    url: "{{ url('campaign-soundbox') }}/" + id,
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
                `/campaign-soundbox/${id}/activate`,
                {_token: '{{ csrf_token() }}'},
                function (res) {
                    Swal.fire('Berhasil!', res.message, 'success');
                    $('#CampaignSoundboxTable').DataTable().ajax.reload(null, false);
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
                `/campaign-soundbox/${id}/toggle-testing`,
                {_token: '{{ csrf_token() }}'},
                function (res) {

                    Swal.fire('Berhasil!', res.message, 'success');

                    $('#CampaignSoundboxTable')
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


