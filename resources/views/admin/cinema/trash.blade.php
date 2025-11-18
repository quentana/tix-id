@extends('templates.app')
@section('content')
    <div class="container my-5">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('admin.cinemas.index') }}" class="btn btn-secondary">kembali</a>
        </div>
        @if (Session::get('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
        @endif
        <h3 class="my-3"> Data SAMPAH : Data Bioskop</h3>
        <table class="table table-bordered">
            <tr>
                <td>#</td>
                <td>Nama Bioskop</td>
                <td> Lokasi </td>
                <td>Aksi</td>
            </tr>
            @foreach ($cinemas as $key =>$item )
            <tr>
                <td>{{$key + 1}}</td>
                <td>{{$item ['name']}}</td>
                <td>{{$item ['location']}}</td>
                 <td class="d-flex align-items-center">
                        <form action="{{ route('admin.cinemas.restore', $item['id']) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class=" btn btn-success"> Kembalikan</button>
                        </form>
                        <form action="{{ route('admin.cinemas.delete_permanent', $item['id']) }}" method="POST"
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
