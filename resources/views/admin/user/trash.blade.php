@extends('templates.app')
@section('content')
    <div class="container my-5">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{route('admin.users.index')}}" class="btn btn-secondary"> Kembali</a>
        </div>
        @if (Session::get('success'))
            <div class="alert alert-success">{{Session::get('success')}}</div>
        @endif
        <h3 class="my-3"> DATA SAMPAH : Data Petugas </h3>
        <table class="table table-bordered">
            <tr>
                <td>No</td>
                <td>Nama</td>
                <td> Email</td>
                <td> Role</td>
                <td> Aksi</td>
            </tr>
            @foreach ($users as $index =>$item )
                <tr>
                    <td>{{$index +1}}</td>
                    <td>{{$item ['name']}}</td>
                    <td>{{$item ['email']}}</td>
                    <td>
                        @if ($item ->role == 'admin')
                        <span class="badge badge-primary">Admin</span>
                        @elseif($item->role =='straff')
                        <span class="badge badge-success">staff</span>
                        @endif
                    </td>
                    <td class="d-flex align-items-center">
                        <form action="{{route('admin.users.restore',$item['id']) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success">Kembalikan</button>
                        </form>
                        <form action="{{route('admin.users.delete_permanent',$item['id'])}}" method="POST"
                        class="ms-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus Selamanya</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection
