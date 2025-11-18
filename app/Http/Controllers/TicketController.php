<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Ticket;
use App\Models\Promo;
use App\models\TicketPayment;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function showSeats($scheduleId,$hourId)
    {
        $schedule = Schedule:: where('id',$scheduleId)->with('cinema')->first();
        // jika tidak ditemukan buat default kosong
        $hour = $schedule['hours'][$hourId]??'';
        $seats = Ticket::whereHas('ticketPayment', function($q) {
            // whereDate :mancari data tanggal
            $q->whereDate('paid_date', now()->format('Y-m-d'));
        })->whereTime('hour', $hour)->pluck('rows_of_seats');
        // pluck(): mengambil satu field aja, disimpan di array
        //  ...$seats : spread operator : mengeluarkan item array
        $seatsFormat = array_merge( ...$seats);
        // dd($seatsFormat);
        return view('schedule.show-seats',compact('schedule','hour', 'seatsFormat'));

    }

    public function index()
    {
        $ticketActive = Ticket::whereHas('ticketPayment', function($q) {
            $q->whereDate('booked_date', now()->format('Y-m-d'))->where('paid_date', '<>', NULL);
        })->get();
        // <> : tidak sama dengan
        $ticketNonActive = Ticket::whereHas('ticketPayment', function($q) {
            $q->whereDate('booked_date', '<', now()->format('Y-m-d'))->where('paid_date', '<>', NULL);
        })->get();
        return view('ticket.index', compact('ticketActive', 'ticketNonActive'));
    }

    public function chartData()
    {
        // ambil bulan sekarang
        $month = now()->format('m');
        $tickets = Ticket::whereHas('ticketPayment', function($q) use($month) {
            // whereMoth :mencari berdasarkan bulan
            $q->whereMonth('booked_date', $month)->where('paid_date', '<>', NULL);
        })->get()->groupBy(function ($ticket) {
            // hasil data berdasarkan bulan dan sudah dibayar, dikelompokan(gorupBy) berdasrkan taggal peembelian untuk menhitung di hari itu berapa yang beli tiket
            return \Carbon\Carbon::parse($ticket['ticketPayment']['booked_date'])->format('Y-m-d');
        })->toArray();
        // ambil data key/ index (tanggal) untuk data label charjs
        $LabeLs = array_keys($tickets);
        // membuat array untuk menyimpan data jumlah pembelian tiap tanggal
        $data = [];
        foreach ($tickets as $ticket) {
            // simpan hasil perhitungan count() dari $ticket. $ticket berbentuk array data data yang dibeli di tanggal tertentu
            array_push($data, count($ticket));
        }
        return response()->json([
            'labels' => $LabeLs,
            'data' => $data
        ]);
        // dd($tickets);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' =>'required',
            'schedule_id' => 'required',
            'rows_of_seats' => 'required',
            'quantity' => 'required',
            'total_price' => 'required',
            'hour' => 'required',
        ]);

        $createData = Ticket::create([
            'user_id' => $request->user_id,
            'schedule_id' => $request->schedule_id,
            'rows_of_seats' => $request->rows_of_seats,
            'quantity' => $request->quantity,
            'total_price' => $request->total_price,
            'actived' => 1,
            'service_fee' => 4000 * $request->quantity,
            'hour' => $request->hour,
        ]);
        // hasilnya dikirimkan dalam bentuk response json karena
        return response()->json([
            'message' => 'Berhasil membuat data tiket',
            'data' => $createData
        ]);
    }

    public function ticketOrder ($ticketId)
    {
        $ticket = Ticket::where('id', $ticketId)->with(['schedule.cinema','schedule.movie'])->first();
        $promos = Promo::where('actived', 1)->get();
        return view('schedule.order',compact('ticket','promos'));
        // dd($ticket);
    }

    public function createBarcode(Request $request, $ticketId)
    {
        $barcodeKode = 'TICKET' . $ticketId .rand(1,10);
        // format ():ektensi file, size() :ukuran gambar, margin():margin luar gambar
        $qrImage = QrCode::format('svg')->size(300)->margin(2)->generate($barcodeKode);
        $fileName = $barcodeKode . '.svg';
        $path = 'barcode/'. $fileName;
        // karna file bukan dari luar (generate) memindahkan nya tidak bisa dengan storeAS gunakan Strogae:link
        // pindahkan gambar ke storage public
        Storage::disk('public')->put($path, $qrImage);

        $createData = TicketPayment::create([
            'ticket_id' => $ticketId,
            'barcode' => $path,
            'status' => 'process',
            'booked_date' =>now()
        ]);

        // Update total_price pada ticket jika menggunakan promo
        if( $request->promo_id != Null){
            $ticket =Ticket::find($ticketId);
            $promo = Promo:: find($request->promo_id);
            if($promo &&  $promo ['type'] == 'percent'){
                $totalPrice = $ticket['total_price'] - ($ticket ['total_price'] * $promo['discount'] / 100);;
            }else{
                $totalPrice = $ticket ['total_price'] - $promo ['discount'];
            }
            $ticket->update(['promo_id' => $request ->promo_id,'total_price' => $totalPrice]);
        }
        return response()->json(['messange' => 'Berhasil membuat Barcode','data' => $createData]);


    }
    public function paymentPage($ticketId)
    {
        $ticket = Ticket::where('id', $ticketId)->with(['promo','ticketPayment'])->first();
        return view('schedule.payment', compact('ticket'));
    }
    public function proofPayment($ticketId)
    {
        $updateData = TicketPayment::where ('ticket_id', $ticketId)->update([
            'paid_date' => now()
        ]);
        // arahkan ke halaman tiket struk
        return redirect()->route('tickets.show', $ticketId);
    }

    /**
     * Display the specified resource.
     */
    public function show($ticketId)
    {
        $ticket = Ticket::where('id', $ticketId)->with(['schedule', 'schedule.cinema', 'schedule.movie','ticketPayment'])->first();
        return view('schedule.ticket-receipt',compact('ticket'));
    }
    public function exportPdf($ticketId)
    {
        // menentukan data yang akan dikirim ke blade pdf
        // bentuk data harus array tidak collection -> to array()
        $ticket = Ticket::where('id', $ticketId)->with(['schedule', 'schedule.cinema', 'schedule.movie', 'ticketPayment'])->first()->toArray();
        // menentukan nama aliass variabel yang akan digunakan di blade pdf
        view()->share('ticket',$ticket);
        // menentukan blade yang akan di cetak menjai pdf dan compact data yang digunakan
        $pdf = Pdf::loadView('schedule.pdf',$ticket);
        // unduh pdf dengan nama file tertentu
        $fileName = 'TICKET' . $ticket['id'] . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        //
    }
}
