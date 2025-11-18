@extends('templates.app')
@section('content')
    <div class="container mt-5">
        @if (Session::get('failed'))
            <div class="alert alert-success">{{ Session::get('failed') }}</div>
        @endif
        <div class="d-flex justify-content-end">
            <a href="{{route('admin.cinemas.trash')}}" class="btn btn-secondary me-2"> Data Sampah</a>
            <a href="{{route('admin.cinemas.export')}}" class="btn btn-secondary me-2">Export (.xlsx)</a>
            <a href="{{ Route('admin.cinemas.create') }}" class="btn btn-success">Tambah Data</a>
        </div>
            <table class="table table-responsive table-bordered mt-3 " id="cinemaTable">
                <thead>
                <tr>
                    <th>id</th>
                    <th>Nama Bioskop</th>
                    <th>Lokasi</th>
                    <th>Aksi</th>
                </tr>
                </thead>
            </table>

    </div>
@endsection
@push('script')
<script>
    $(function(){
        $('#cinemaTable').DataTable({
            processing:true,
            serverside:true,
            ajax:"{{ route('admin.cinemas.datatables') }}",
            columns : [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable : false,
                searchable :false
            },
            {
                data:'name',
                name:'name',
                orderable:true,
                searchable:true
            },
            {
                data:'location',
                name:' location',
                orderable :true,
                searchable :true
            },
            {
                data: 'buttons',
                name: 'buttons',
                orderable:true,
                searchable:false,
            }
        ]
        })
    })
</script>
@endpush
