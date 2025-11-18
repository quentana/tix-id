<?php

namespace App\Http\Controllers;

use App\Exports\ScheduleExport;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cinemas = Cinema::all();
        $movies = Movie::all();

        // with() : mengambil data detail dari relasi , tidak hanya idnyA
        //  isis di dalam with  diambil dri nama fungsi relasi di model
        $schedules = Schedule::with(['cinema','movie'])->get();
        return view('staff.schedule.index', compact('cinemas','movies','schedules'));
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
            'cinema_id'  => 'required',
            'movie_id' => 'required',
            'price' => 'required|numeric',
            // validasi item array (.) validasi index ke bebrapa pun (*)
            'hours.*' => 'required|date_format:H:i'
        ],[
            'cinema_id.required' => 'Biosko Haru di pilih',
            'movie_id.required' => 'Flim Harus di pilih',
            'price.required' => 'Harga Hraus di isi',
            'price.numeric' => 'Harga harus diisi dengan angka ',
            'hours.*.required' => 'Jam tayang harus diisi minimal satu data',
            'hours.*.date_format' => 'Jam tayang harus diisi dengan jam:menit',
        ]);
       //pengecekan data berdasarkan cinema_id dan movie_id lalu ambil hours nya
        //value('hours') : hanya mengambil hours, ga perlu data lain
        $hours = Schedule::Where('cinema_id', $request->cinema_id)->where('movie_id',
        $request->movie_id)->value('hours');
        //jika data belum ada $hours akan NULL, agar tetap array gunakan ternary
        //jika $hours ada isinya ambil, kalau NULL buat area kosong
        $hoursBefore = $hours ?? [];
        //gabungkan hours sebelumnya dengan yang baru ditambahkan
        $mergeHours = array_merge($hoursBefore, $request->hours);
        //hilangkan jam yang duplikat, gunakan array ini untuk database
        $newHours = array_unique($mergeHours);

        //updateOrCreate() : jika cinema_id dan movie_id udah ada di schedule (UPDATE) kalau gaada (CREATE)
        $createData = Schedule::updateOrCreate([
            // mencari data
            'cinema_id' => $request->cinema_id,
            'movie_id' => $request->movie_id,
        ], [
            // update ini
            'price' => $request->price,
            'hours' => $newHours,
        ]);
        if ($createData) {
            return redirect()->route('staff.schedules.index')->with('success', 'Berhasil menambahkan data');
        } else {
            return redirect()->back()->with('error', 'Gagagl, coba lagi');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Schedule $schedule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $schedule = Schedule::where('id',$id)->with(['cinema','movie'])->first();
        return view('staff.schedule.edit',compact('schedule'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'price' => 'required|numeric',
            'hours.*' => 'required|date_format:H:i'
        ],[
            'price.required' => 'Harga Harus diisi',
            'price.numeric'=> 'Harga Harus diisi dengan Angka',
            'hours.*.required' => ' Jam Tayang Harus diisi',
            'hour.*.date_format' => 'Jam tayang harus diisi dengan jam:menit',
        ]);

        $updateData = Schedule::where('id',$id)->update([
            'price' => $request->price,
            'hours' => array_unique($request->hours),
        ]);
        if ($updateData){
            return redirect()->route('staff.schedules.index')->with('success' ,'Berhasil  mengubah data!');
        }else{
            return redirect()->back()->with('error','Gagal! Coba lagi');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Schedule::where('id',$id)->delete();
        return redirect()->route('staff.schedules.index')->with('success','Berhasil menghapus data!');
    }

    public function trash()
    {
        // onlyTrashed() : mengambil data yang sudah di hapus, yg deleted_at di phpmyadmin nya ada isi tanggal, hanya filter tetap gunakan
        // get()/ first() untuk ambilnya
        $schedules = Schedule::onlyTrashed()->with(['cinema','movie'])->get();
        return view('staff.schedule.trash',compact('schedules'));
    }
    public function  restore($id)
    {
        $schedule = Schedule::onlyTrashed()->find($id);
        //restore() :MEngambil data ke blum di hapus
        $schedule -> restore();
        return redirect()->route('staff.schedules.index')->with('success','Berhasil menggembalikan data!');
    }
    public function deletePermanent($id)
    {
        $schedule = Schedule::onlyTrashed()->find($id);
        // for Delete(): hapus selamanya dari database
        $schedule ->forceDelete();
        return redirect()->back()->with('success','Berhasil menghapus Data selamanya!');
    }
    public function exportExcel()
    {
        $fileName = 'data-schedule.xlsx';
        return Excel::download( new ScheduleExport, $fileName);
    }
   public function dataForDatatables()
{
    $schedules = Schedule::with(['cinema', 'movie']); // ambil relasi cinema & movie

    return DataTables::of($schedules)
        ->addIndexColumn() // kolom nomor urut
        ->addColumn('cinema _name', function($data) {
            return $data->cinema->name ?? '-';
        })
        ->addColumn('movie_title', function($data) {
            return $data->movie->title ?? '-';
        })
        ->addColumn('price', function($data) {
            return 'Rp. ' . number_format($data->price, 0, ',', '.');
        })
        ->addColumn('hours', function($data) {
            // hours berupa array, ubah jadi list <ul><li>...</li></ul>
            $list = '<ul>';
            foreach ($data->hours as $hour) {
                $list .= '<li>' . ($hour) . '</li>';
            }
            $list .= '</ul>';
            return $list;
        })
        ->addColumn('buttons', function($data) {
            $btnEdit = '<a href="'.route('staff.schedules.edit', $data->id).'" class="btn btn-primary">Edit</a>';
            $btnDelete = '<form action="'.route('staff.schedules.delete', $data->id).'" method="POST" style="display:inline-block; margin-left:6px;">'.
                         csrf_field().
                         method_field('DELETE').
                        '<button type="submit" class="btn btn-danger">Hapus</button>
                         </form>';
            return $btnEdit . ' ' . $btnDelete;
        })
        ->rawColumns(['hours', 'buttons']) // biar HTML di kolom ini tidak di-escape
        ->make(true);
}

}
