@extends('templates.app')
@section('content')
    <div class="container my-5">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('staff.promos.trash') }}" class="btn btn-secondary me-2">Data Sampah</a>
            <a href="{{ route('staff.promos.export') }}" class="btn btn-secondary me-2">Export(.xlsx)</a>
            <a href="{{ route('staff.promos.create') }}" class="btn btn-success">Tambah data</a>
        </div>
        @if (Session::get('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
        @endif
        <h5 class="my-3">Data Promo</h5>
        <table class="table table-bordered" id="promoTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode promo</th>
                    <th>Total Potongan </th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection
@push('script')
    <script>
        $(function() {
            $('#promoTable').DataTable({
                processing: true,
                serverside: true,
                ajax: "{{ route('staff.promos.datatables') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'promo_code',
                        name: 'promo_code',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'totaldiscount',
                        name: "totaldiscount",
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'buttons',
                        name: 'buttons',
                        orderable: true,
                        sreachable: false
                    }
                ]
            })
        })
    </script>
@endpush

{{-- kalo persen jangan lebih dari 100 kalo rupiah jangan kurang dari 1.0000 --}}
