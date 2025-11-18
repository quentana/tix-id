@extends('templates.app')

@section('content')
    <div class='container mt-5'>
        @if (Session::get('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
        @endif
        <div class="d-flex justify-content-end">
            <a href="{{ Route('admin.users.trash') }}" class="btn btn-secondary me-2"> DATA SAMPAH</a>
            <a href="{{ Route('admin.users.export') }}" class="btn btn-secondary me-2">Export (.xlsx)
                <a href="{{ Route('admin.users.create') }}" class="btn btn-success">Tambah Data</a>
        </div>
        <h5 class="mt-3">Data Pengguna (Admin & Staff)</h5>
        <table class="table table-responsive table-bordered mt-3" id="petugasTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection
@push('script')
    <script>
        $(function() {
            $('#petugasTable').DataTable({
                proccesing: true,
                serverside: true,
                ajax: "{{ route('admin.users.datatables') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_Rowindex',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'name',
                        name: 'name',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'email',
                        name: 'email',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'statusBadge',
                        name: 'statusBadge',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'buttons',
                        name: 'buttons',
                        orderable: true,
                        searchable: false
                    }
                ]
            })
        })
    </script>
@endpush
