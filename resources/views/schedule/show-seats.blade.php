@extends('templates.app')

@section('content')
    <div class="container card my-5 p-4" style="margin-bottom: 10% !important">
        <div class="card-body">
            <b>{{ $schedule['cinema']['name'] }}</b>
            <br>
            {{-- mengamil thl hari ini :carbon ::now()  --}}
            <b>{{ \Carbon\Carbon::now()->format('d M,Y') }} || {{ $hour }}</b>

            <div class="d-flex justify-content-center mt-3">
                <div class="row w-50">
                    <div class="col-4 d-flex">
                        <div style="width: 20px; height: 20px; background: #050b14;"></div>
                        <p class="ms-2">Kursi Kosong</p>
                    </div>
                    <div class=" col-4 d-flex">
                        <div style="width: 20px; height: 20px; background: #cdc0c0;"></div>
                        <p class="ms-2">Kursi Terjual</p>
                    </div>
                    <div class="col-4 d-flex">
                        <div style="width: 20px; height: 20px; background: #3e85ef;"></div>
                        <p class="ms-2"> Kursi Dipilih</p>
                    </div>
                </div>
            </div>
            @php
                $row = range('A', 'H');
                $col = range(1, 18);
            @endphp
            {{-- looping untuk membuat baris A-H --}}
            @foreach ($row as $baris)
                <div class="d-flex justify-content-center my-1">
                    {{-- looping untuk membuat kursi di satu baris  --}}
                    @foreach ($col as $kursi)
                        {{-- jika kursi nomor 7, tambahkan space kosong untuk jalan  --}}
                        @if ($kursi == 4)
                            <div style="width: 35px;"></div>
                        @endif
                        @if ($kursi == 17)
                            <div style="width: 35px;"></div>
                        @endif

                        @php
                        $seat = $baris . "-" . $kursi;
                        @endphp
                        {{-- in_array(item,array) : mencari item di array php --}}
                        @if (in_array($seat, $seatsFormat))
                        <div style="background: #cdc0c0; border-radius: 10px; width: 45px; height: 45px;  cursor: pointer;"
                            class="p-2 mx-1 text-dark">
                            <span style="font-size: 12px; ">{{ $baris }}-{{ $kursi }}</span>
                        </div>
                        @else
                          {{-- munculkan A-1 A-2 dan seterunya --}}
                         <div style="background: #050b14; border-radius: 10px; width: 45px; height: 45px; text-align: center; cursor: pointer;"
                            onclick="selectedSeat('{{ $schedule->price }}','{{ $baris }}','{{ $kursi }}',this)"
                            class="p-2 mx-1 text-white">
                            <span style="font-size: 12px; ">{{ $baris }}-{{ $kursi }}</span>
                        </div>
                        @endif
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
    <div class="w-100 p-2 bg-light text-center fixed-bottom" id="wrapBtn">
        <b class=" text-white p-3">LAYAR BIOSKOP</b>
        <div class="row" style="border: 1px solid #d1d1d1">
            <div class="col-6 text-center " style="border: 1px solid #d1d1d1">
                <p> Total Harga</p>
                <h5 id="totalPrice"> Rp. -</h5>
            </div>
            <div class="col-6 text-center" style="border: 1px solid #d1d1d1">
                <p>Kursi Pilihan</p>
                <h5 id="selectedSeats">-</h5>
            </div>
        </div>
        {{-- menyimpan value yang di perlukan untuk aksi ringkasaan PEMESANAN --}}
        {{-- hidden ketika data php mau di kirim ke javascript --}}
        <input type="hidden" name="user_id" value="{{ Auth::user()->id }}" id="user_id">
        <input type="hidden" name="schedule_id" value="{{ $schedule->id }}" id="schedule_id">
        <input type="hidden" name="hour" value="{{ $hour }}" id="hour">
        <div style="color: black; font-weight: bold; cursor: not-allowed;" class="w-100 text-center" id="btnOrder">
            RINGKASAN PEMESANAN</div>
    </div>
@endsection

@push('script')
    <script>
        // array data kursi yang sudah di Pilihan
        let seats = [];
        let totalPriceData = null;

        function selectedSeat(price, row, col, el) {
            // membuat  A-1 sesuai row dan col yang Dipilih
            let seatItem = row + "-" + col;
            // cek apakah kursi ini udah ada di arrayseats
            let indexSeat = seats.indexOf(seatItem);
            // jika ada akan muncul index nya jika gaada -1
            if (indexSeat == -1) {
                // kalau gaada simpen kursi yang dipilih ke aaray
                seats.push(seatItem);
                // kasi warna biru muda ke element  yang dipilih
                el.style.background = "#3e85ef";
            } else {
                // kalau ada di array artinya klik kali ini untuk membatalkan pilihan (klikan ke 2 pada kursi)
                seats.splice(indexSeat, 1); //hapus item dari aarray
                // kembalikan warna biru ke biru tua
                el.style.background = "#050b14";
            }
            // menghitung total harga sesuai kursi yang di pilihan
            let totalPrice = price * (seats.length); //seats.leght:jumlah item array
            totalPriceData = totalPrice;
            let totalPriceEl = document.querySelector("#totalPrice");
            totalPriceEl.innerText = "Rp" + totalPrice;
            // memunculkan daftar kursi yang dipilih
            let selectedSeatsEl = document.querySelector("#selectedSeats");
            // seats.join(",") mengubah array jd string, dipisahkan dengan tanda tertentu
            selectedSeatsEl.innerText = seats.join(", ");

            // jika seatsnya lebih dari / sama dengan 1 aktifkan order dan tambahkan fungsi onclik untuk proses data tiket
            let btnOrder = document.querySelector("#btnOrder");

            if (seats.length > 0) {
                btnOrder.style.background = '#112646';
                btnOrder.style.color = 'white';
                btnOrder.style.cursor = 'pointer';
                btnOrder.onclick = createTicketData;
            } else {
                btnOrder.style.background = '';
                btnOrder.style.color = '';
                btnOrder.style.curcor = '';
                btnOrder.onclick = null;
            }
        }

            function createTicketData(){
                // AJAX :asynchronus javascript and xml,jika mau akses diatas ke server melalkui js gunakan method ajax({}) .bisa digunakan haya melalui jquery ($)
                $.ajax({
                    // routing untuk akses data
                    url: "{{ route('tickets.store') }}",
                    // http method
                    method: "POST",
                    // data yang akan di kirim (diambil pake request $request)
                    data: {
                        // CSRF token
                        _token: "{{ csrf_token() }}",
                        user_id: $("#user_id").val(),
                        schedule_id: $("#schedule_id").val(),
                        rows_of_seats: seats,
                        quantity: seats.length,
                        total_price: totalPriceData,
                        hour:$("#hour").val()
                    },
                    success: function(response){
                        // console.log(response)
                        // jika berhasil menambahkan data di arahkan ke halaman ke ticket order(ringkasan order)
                        // response= data  & messangge
                        let ticketId = response.data.id;
                        window.location.href = `/tickets/${ticketId}/order`;
                    },
                    error: function(message){
                        console.log(message);
                        alert("Terjadi Kesalahan ketika membuat data tiket!");
                    },
                });
            }
    </script>
@endpush
