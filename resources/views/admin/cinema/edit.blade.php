@extends('templates.app')

@section('content')
    <div class="w-75 d-block mx-auto mt-3 p-4">
        <h class="text-center mb-3"> Tambah Data Bioskop</h>
        <form method="POST" action="{{Route('admin.cinemas.update',$cinema->id)}}">
            @if (Session::get('error'))
            <div class="aler alert-danger">{{Session::get('error')}}</div>
            @endif
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="name" class="form-label"> Nama Bioskop</label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid  @enderror" value="{{old('$cinema')}}">
                @error('name')
                    <div class="text-danger">{{$message}}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">Lokasi</label>
                <textarea name="location" id="location" class="form-control @error('location') is-invalid @enderror">{{$cinema->location}}
                </textarea>
                @error('location')
                    <div class="text-danger">{{$message}}</div>
                @enderror
            </div>
            <button type="sumbit" class="btn btn-primary">Kirim Data</button>
        </form>
    </div>
@endsection
