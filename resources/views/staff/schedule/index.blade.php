@extends('templates.app')

@section('content')
    <div class="container my-5">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('staff.schedules.trash') }}" class="btn btn-secondary me-2">Data Sampah</a>
            <a href="{{ route('staff.schedules.export') }}" class="btn btn-secondary me-2">Export(.xlsx)</a>
            {{-- karna modal ini tidak akan berubah , munculkan dengan bosstrap target --}}
            <button class="btn btn-success"data-bs-toggle="modal" data-bs-target="#modalAdd">Tambah Data</button>
        </div>
        @if (Session::get('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
        @endif
        <h3 class="my-3">Data Jadwal Tayangan</h3>
        <table class="table table-bordered" id="scheduleTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Bioskop</th>
                    <th>Judul Film</th>
                    <th>Harga</th>
                    <th>Jam Tayang</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>

        {{-- modal --}}
        <div class="modal fade" id="modalAdd" tabindex="-1" aria-labelledby="modalAddLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Tambah Data</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('staff.schedules.store') }}">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label">Bioskop:</label>
                                <select name="cinema_id" id="cinema_id"
                                    class="form-select @error('cinema_id') is-invalid @enderror">
                                    <option disabled hidden selected>Pilih Bioskop</option>
                                    @foreach ($cinemas as $cinema)
                                        {{-- jumlah opsi slect sesuai dengan data cinemas --}}
                                        {{-- Fk cinema_id menyimpan id jdi value['id'] tapi munculkan ['name']nya --}}
                                        <option value="{{ $cinema['id'] }}">{{ $cinema['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('cinema_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label">Film:</label>
                                <select name="movie_id" id="movie_id"
                                    class="form-select @error('movie_id') is-invalid
                                @enderror">
                                    <option disabled hidden selected>Pilih flim</option>
                                    @foreach ($movies as $movie)
                                        <option value="{{ $movie['id'] }}">{{ $movie['title'] }}</option>
                                    @endforeach
                                </select>
                                @error('movie_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="message-text" class="col-form-label">Harga:</label>
                                <input type="number" name="price" id="price"
                                    class="form-control @error('price') is-invalid
                                @enderror">
                                @error('price')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="hours" class="form-label"> Jam tayang:</label>
                                {{-- kalo ada err yang berhubungan dengan item array hours --}}
                                @if ($errors->has('hours.*'))
                                    {{-- ambil ket err  pada item pertama --}}
                                    <small class="text-danger">{{ $errors->first('hours.*') }}</small>
                                @endif
                                <input type="time" name="hours[]" id="hours"
                                    class="form-control @if ($errors->has('hours.*')) is-invalid @endif">
                                {{-- akan diisi inputan tambahan dari js --}}
                                <div id="additionalInput"></div>
                                <span class="text-primary my-3" style="cursor:pointer" onclick="addInput()"> +Tambah input
                                    Jam</span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Kirim</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(function() {
            $('#scheduleTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('staff.schedules.datatables') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'cinema_name',
                        name: 'cinema.name',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'movie_title',
                        name: 'movie.title',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'price',
                        name: 'price',
                        orderable: true,
                        searchable: false
                    },
                    {
                        data: 'hours',
                        name: 'hours',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'buttons',
                        name: 'buttons',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
        });
    </script>

    <script>
        function addInput() {
            let content = `<input type ="time" nama="hours[]" id="hours" class="form-control my-2">`;
            // ambil wadah
            let wrap = document.querySelector("#additionalInput");
            // simpan kontan, tapi gunakan += agar konten terus  bertambah bukan mengubah
            wrap.innerHTML += content;
        }
    </script>
    {{-- pengkondisian php cek error, jika terjadi err apapun :  $errors->any() --}}
    @if ($errors->any())
        <script>
            // panngil  modal
            let modalAdd = document.querySelector("#modalAdd");
            // munculkan modal lgi, lewat js
            new bootstrap.Modal(modalAdd).show();
        </script>
    @endif
@endpush
