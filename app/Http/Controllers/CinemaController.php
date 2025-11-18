<?php

namespace App\Http\Controllers;

use App\Models\Cinema;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CinemaExport;
use Yajra\DataTables\Facades\DataTables;

class CinemaController extends Controller
{
    /**

    * Display a listing of the resource.
     */
    public function index()
    {
        //mengambil data dari model
        //all() -> mengambil semua data di model cinema/table cinemas
        $cinemas = Cinema::all();
        //mengirim data ke blade->compact('namavariable)
        return view('admin.cinema.index', compact('cinemas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.cinema.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'location' => 'required|min:10'
        ], [
            'name.required' => 'Nama bioskop harus diisi',
            'location.required' => 'Lokasi bioskop harus diisi',
            'location.min' => 'Lokasi Bioskop harus diisi minimal 10 karakter'
        ]);
        $createData = Cinema::create([
            'name' => $request->name,
            'location' => $request->location,
        ]);
        if($createData) {
            return redirect()->route('admin.cinemas.index')->with('success', 'Berhasil membuat data!');
        } else {
            return redirect()->back()->with('error', 'Gagal! Silahkan coba');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Cinema $cinema)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)

    {
        //ngambil data dari {id} route nya
        $cinema = Cinema::find($id);
       return view('admin.cinema.edit', compact('cinema'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cinema $cinema, $id)
    {
        $request->validate([
            'name' => 'required',
            'location' => 'required|min:10'
        ], [
            'name.required' => 'Nama Bioskop harus diisi',
            'name.min' => 'Nama Bioskop harus diisi minimal 3 karakter',
            'location.required' => 'Lokasi Bioskop harus diisi',
            'location.min' => 'Lokasi Bioskop harus diisi minimal 10 karakter',
        ]);

        //kirim data
        $updateData = Cinema::where('id', $id)->update ([
            'name' => $request->name,
            'location' => $request->location
        ]);

        //perpindahan halaman
        if($updateData) {
            return redirect()->route('admin.cinemas.index')->with('success', 'Berhasil mengubah data');
        } else {
            return redirect()->back()->with('failed, Gagal! Silahkan coba lagi');
        }
    }

    public function destroy($id){
        $deleteData = Cinema::where('id', $id)->delete();
        if($deleteData) {
            return redirect()->route('admin.cinemas.index')->with('success', 'Berhasil menghapus data');
        } else {
            return redirect()->back()->with('failed', 'Gagal! Silahkan coba lagi');
        }
    }
    // menambahkan file excel ke  cinema
    public function exportExcel()
    {
        $fileName = 'data-cinema.xlsx';
        return Excel::download(new CinemaExport, $fileName);
    }
    public function trash()
    {
        $cinemas = Cinema::onlyTrashed()->get();
        return view('admin.cinema.trash',compact('cinemas'));
    }
    public function restore($id)
    {
        $cinema = Cinema::onlyTrashed()->find($id);
        $cinema -> restore();
        return redirect()->route('admin.cinemas.index')->with('success', 'Berhasil Mengembalikan data!');
    }
    public function deletePermanent($id)
    {
        $cinema = Cinema::onlyTrashed()->find($id);
        $cinema ->forceDelete();
        return redirect()->back()->with('success','Berhasil Menghapus Data!');
    }
    public function dataForDatatables()
    {
        $cinemas = Cinema::query();
        return DataTables::of($cinemas)
        ->addIndexColumn()
        ->addColumn('buttons',function($data){
            $btnEdit = '  <a href="'.Route('admin.cinemas.edit', $data['id']) .'"
             class="btn btn-primary me-2">Edit</a>';
             $btnDelete = ' <form class="me-2" action="'.route('admin.cinemas.delete',
                            $data->id) .'" method="POST">'.
                            csrf_field().
                            method_field('DELETE').
                            '<button type="sumbit" class="btn btn-danger me-2">Hapus</button>
                        </form>';
            return '<div class="d-flex justify-content-center">'.$btnEdit.$btnDelete.'</div>';
        })
        ->rawColumns(['buttons'])
        ->make(true);
    }
    public function listCinema()
    {
        $cinemas = Cinema::all();
        return view('schedule.cinemas',compact('cinemas'));
    }
    public function cinemaSchedules($cinema_id)
    {
        // whereHas ('namarelasi', function($q){..} : argumen 1 (nama relasi ) wajib, argumen2 (func untuk filter pada relasi) optional )
        $schedules = Schedule::where('cinema_id', $cinema_id)->with('movie')->whereHas
        ('movie', function($q){
            $q->where('actived', 1);
        })->get();
        return view('schedule.cinema-schedule',compact('schedules'));
    }
}
