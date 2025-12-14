@extends('master')
@section('title') Leads Master @endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Loading overlay */
    #loading-overlay {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.7);
        z-index: 9999;
        display: none;
        justify-content: center;
        align-items: center;
    }
    #loading-message {
        font-size: 24px;
        color: white;
        text-align: center;
    }

    /* Table styling */
    .table {
        background-color: #f9f9f9;
        border-radius: 8px;
        overflow: hidden;
        width: 100%;
        max-width: 100%;
        margin-top: 15px;
        border: 0.5px solid #ccc;
        table-layout: auto;
    }
    .table th, .table td {
        padding: 8px !important;
        font-size: 14px !important;
        border: 0.5px solid #ccc;
        color: #313131;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
    }
    .table th {
        font-weight: bold;
        background-color: #343a40;
        color: #ffffff;
    }

    /* Buttons */
    .btn-group-sm > .btn, .btn-sm { margin: 0 2px; }

    /* Status badge */
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
        <h3 class="card-title font-weight-bold">Leads Master</h3>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('leads-master.create') }}" class="btn btn-info" id="btn-add-lead">
                <i class="fas fa-plus"></i> Tambah Leads
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-striped table-hover w-100" id="leadsMasterTable">
                <thead>
                    <tr>
                        <th>User Canvasser</th>
                        {{-- <th>Kode Voucher</th> --}}
                        <th>Nama Perusahaan / Instansi</th>
                        <th>No HP Pelanggan</th>
                        <th>Email Pelanggan</th>
                        <th>Lead Source</th>
                        <th>Nama Pelanggan</th>
                        <th>Sector</th>
                        <th>Status</th>
                        <th>Remarks</th>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    var table = $('#leadsMasterTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('leads-master.data') }}",
        columns: [
            { data: 'user_name', name: 'user_name' },       // User
            // { data: 'kode_voucher', name: 'kode_voucher' }, // Kode Voucher
            { data: 'company_name', name: 'company_name' }, // Nama Perusahaan / Instansi
            { data: 'mobile_phone', name: 'mobile_phone' }, // No HP
            { data: 'email', name: 'email' },               // Email
            { data: 'source_name', name: 'source_name' },   // Lead Source
            { data: 'nama', name: 'nama' },                 // Nama Pelanggan
            { data: 'sector_name', name: 'sector_name' },   // Sector
            { data: 'status', name: 'status' },             // Status
            { data: 'remarks', name: 'remarks' },           // Remarks
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false } // Aksi
        ],
        columnDefs: [
            { targets: '_all', className: 'text-center' }
        ]
    });


    // Simpan perubahan
    $('#btn-simpan-user').click(function() {
        var userId = $('#user_id').val();

        if(!/^62\d+$/.test($('#mobile_phone').val())){
            Swal.fire('Error!', 'Nomor HP harus diawali dengan 62.', 'error');
            return false;
        }

        $(this).html('Menyimpan..');

        $.ajax({
            url: "{{ url('leads-master/update') }}/" + userId,
            type: "POST",
            data: $('#userForm').serialize(),
            dataType: 'json',
            success: function(res){
                $('#userModal').modal('hide');
                table.ajax.reload(null, false);
                Swal.fire('Berhasil!', res.success, 'success');
            },
            error: function(err){
                let errors = err.responseJSON.errors;
                if(errors.nama) $('#nama-error').text(errors.nama[0]);
                if(errors.email) $('#email-error').text(errors.email[0]);
            },
            complete: function(){
                $('#btn-simpan-user').html('Simpan Perubahan');
            }
        });
    });

    // Tooltip
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@endsection
