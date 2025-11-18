@extends('templates.app')

@section('content')
    <div class="container my-5">
        <div class="d-felx justify-content-end mb-3">
            <a href="{{ route('staff.promos.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
        @if (Session::get('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
        @endif
        <h3 class="my-3"> Data SAMPAH : Data promo</h3>
        <table class="table table-bordered">
            <tr>
                <th>No</th>
                <th>Kode promo</th>
                <th>Total Potongan </th>
                <th>Aksi</th>
            </tr>
            @foreach ($promos as $key => $item)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $item['promo_code'] }}</td>
                    <td>
                        @if ($item->type == 'percent')
                            {{ min($item->discount, 100) }}%
                        @else
                            Rp. {{ number_format(max($item->discount, 1000), 0, ',', '.') }}
                        @endif
                    </td>
                    <td class="d-flex align-items-center">
                        <form action="{{ route('staff.promos.restore', $item['id']) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class=" btn btn-success"> Kembalikan</button>
                        </form>
                        <form action="{{ route('staff.promos.delete_permanent', $item['id']) }}" method="POST"
                            class="ms-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class=" btn btn-danger"> Hapus Selamanya</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection
