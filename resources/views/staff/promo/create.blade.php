@extends('templates.app')

@section('content')
    <div class="w-75 mx-auto my-5">
        <form method="POST" action="{{ route('staff.promos.store') }}">
            @if (Session::get('error'))
                <div class="aler alert-danger">{{ Session::get('error') }}</div>
            @endif
            @csrf

            <div class=" mb-3">
                <label for="promo_code"class="form-label">Kode promo</label>
                <input type="text"name="promo_code" id="promo_code"
                    class="form-control @error('promo_code') is-invalid
                @enderror"
                    value="{{ old('$promo') }}">
                @error('promo_code')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
                </input>
            </div>
            <div class="mb-3">
                <label for="type"class="form-label">Tipe Promo</label>
                <select name="type" id="type"
                    class="form-control @error('type') is-invalid
                @enderror" value="{{ old('$promo') }}">
                    <option selected>-- Pilih Tipe --</option>
                    <option value="percent">%</option>
                    <option value="rupiah">Rupiah</option>
                </select>
                @error('type')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            <div class="mb-3">
                <label for="discount"class="form-label">Jumlah Potongan</label>
                <input type="number"name="discount" id="discount"
                    class="form-control @error('discount ') is-invalid @enderror" value="{{ old('$promo') }}">
                @error('discount')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
                </input>
            </div>
            <button type="submit" class="btn btn-primary">kirim</button>
        </form>
    </div>
@endsection
