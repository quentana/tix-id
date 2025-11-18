@extends('templates.app')

@section('content')
 <div class="w-75 d-block mx-auto mt-3 p-4">
    <form action="{{ route('staff.promos.update', $promo->id) }}" method="POST">
        @csrf
        @error('discount')
        <small class="text-danger">{{ $message }}</small>
       @enderror
        @method('PUT')

        <div class="form-group mb-3">
            <label>Kode Promo</label>
            <input type="text" name="promo_code" value="{{ old('promo_code', $promo->promo_code) }}" class="form-control" required>
        </div>

        <div class="form-group mb-3">
            <label>Tipe Promo</label>
            <select name="type" class="form-control" required>
                <option value="rupiah" {{ $promo->type == 'rupiah' ? 'selected' : '' }}>Rupiah</option>
                <option value="percent" {{ $promo->type == 'percent' ? 'selected' : '' }}>Persen (%)</option>
            </select>
        </div>

        <div class="form-group mb-3">
            <label>Jumlah Potongan</label>
            <input type="number" name="discount" value="{{ old('discount', $promo->discount) }}" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">kirim</button>
    </form>
</div>
@endsection
