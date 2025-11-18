<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;
use Mockery;
use Route;
use Illuminate\Support\str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MovieExport;
use Yajra\DataTables\Facades\DataTables;


class MovieController extends Controller
{
    public function exportExcel()
    {
        $fileName = 'data-flim.xlsx';
        return Excel::download(new MovieExport, $fileName);
    }
    public function home()
    {
        // format pencarian data : where('colum,'operator','value')
        // jika Operatto ==/= operator Bisa TIDAK DITULIS
        // operator yang digunakan  : <kurang dari | > lebih dari | <> tidak sama dengan
        // format mengurutkan data : orderBY(''colum','DESC/ASC')-> DESC z-a/9-0, ASC a-z/0-9
        // get() : mengambil seluruh data  HASIL FILTER
        $movies = Movie::where('actived', 1)->orderBy('created_at', 'DESC')->limit(4)->get();
        return view('home', compact('movies'));
    }
    public function homeAllMovie(Request $request)
    {
        // ambil data dari input name="search_movie
        $title = $request->search_movie;
        // kalau seacrh_movie ga kosong, cari data
        if ($title != "") {
            // operator LIKE :Mencari data yang mirip / megandung kata tertentu
            // % di gunakan untuk mengaktifkan LIKE
            // % kata : Menacri kata belakang
            // kata % :Mencari kata depan
            // %kata% :menacri kata depan, tengah ,belakang
            $movies = Movie::where('title', 'LIKE', '%' . $title . '%')->where(
                'actived',
                1
            )->orderBy('created_at', 'DESC')->get();
        } else {
            $movies = Movie::where('actived', 1)->orderBy('created_at', 'DESC')->get();
        }

        return view('movies', compact('movies'));
    }

    public function movieSchedules($movie_id, Request $request)
    {
        // Request $request :mengambil data dari from atau href="?"
        $sortPrice = $request['sort-price'];
        if ($sortPrice) {
            // karena mau mengurutkan berdasarkan price yang aada di schedules, make sorting (orderby) di simpan  di relsi with schedules
            $movie = Movie::where('id', $movie_id)->with([
                'schedules' => function ($q) use ($sortPrice) {
                    // $q :Mewakili model schedule
                    // 'schedules' => function($q) {...} :melakukan filter/menjalankan elquent didalam relasi
                    $q->orderBy('price', $sortPrice);
                }, 'schedules.cinema'])->first();
        } else {
            // mengambil relasi didalam relasi
            // relasi cinema ada di schedule ->schedules.cinema(.)
            $movie = Movie::where('id', $movie_id)->with(['schedules', 'schedules.cinema'])->first();
            // first() :karna 1 data film, diambilnya satu
        }

        $sortAlfabet = $request['sort-alfabet'];
        if($sortAlfabet == 'ASC'){
            // Ambil colection, collection :hasill dari get,fisrt,all
            // $movie->schedules mengacu ke data relasi schedules
            // shorBy : Mengurutkan collection (ASC), orderBy :mengunrutkan query eloquent
            $movie ->schedules = $movie->schedules->sortBy(function($schedule){
               return $schedule->cinema->name;
                // mengurutkan berdasarkan name dari relasi cinema
            })->values();
        }elseif($sortAlfabet == 'DESC'){
            // kalau sortAlfabet bukan ASC, berarti DESC, gunakan sortByDESC (untuk mengurutkan secara DESC)
            $movie->schedules = $movie->schedules->sortByDesc(function($schedule){
                return $schedule->cinema->name;
            })->values();
            // value() :ambil ulang data dari collection
        }

         $searchCinema = $request['search-cinema'];
            if ($searchCinema){
            //   filter collection
            $movie->schedules = $movie->schedules->where('cinema_id', $searchCinema)->values();
            }

            // list untuk dropdwon bioskop, data murni yang tidak terfilter/sort apapun
            $listCinema = Movie::where('id', $movie_id)->with(['schedules','schedules.cinema'])->first();

        return view('schedule.detail-film', compact('movie','listCinema'));
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $movies = Movie::all();
        return view('admin.movie.index', compact('movies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.movie.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //cek seluruh request (data dr input)
        // dd($request->all());
        $request->validate([
            'title' => 'required',
            'duration' => 'required',
            'genre' => 'required',
            'director' => 'required',
            'age_rating' => 'required|numeric',
            //mimes => jenis file yg boleh diupload
            'poster' => 'required|mimes:jpg,jpeg,png,svg,webp',
            'description' => 'required|min:10'
        ], [
            'title.required' => 'Judul film harus diisi',
            'duration.required' => 'Durasi film harus diisi',
            'genre.required' => 'Genre film harus diisi',
            'director.required' => 'Sutradara harus diisi',
            'age_rating.required' => 'Usia minimal harus diisi',
            'age_rating.numberic' => 'Usia minimal harus diisi dengan angka',
            'poster.required' => 'Poster file harus diisi',
            'poster.mimes' => 'Poster file harus berupa JPG/JPEG/SVG/PNG/WEBP',
            'description' => 'Sinopsis harus diisi'
        ]);
        //$request -> file ('name_input) : ambil file yg diupload
        $gambar = $request->file('poster');
        //buat nama baru, nama acak untuk membedakan tiap file, akan menjadi: abcde poster.jpg
        //getClientOriginalExtention() : ambil extensi file
        $namaGambar = Str::random(5) . "-poster." . $gambar->getClientOriginalExtension();
        //storeAs -> menyimpan file, format storeAs(namafolder, namafile, visability)
        //hasil storeAs() berupa alamat file, visability, public/private
        $path = $gambar->storeAs("poster", $namaGambar, "public");


        $createData = Movie::create([
            'title' => $request->title,
            'duration' => $request->duration,
            'genre' => $request->genre,
            'director' => $request->director,
            'age_rating' => $request->age_rating,
            //yg disimpan di db lokasi fileny dari storeAs() -> $path
            'poster' => $path,
            'description' => $request->description,
            'actived' => 1
        ]);
        if ($createData) {
            return redirect()->route('admin.movies.index')->with('success', 'Berhasil tambah data!');
        } else {
            return redirect()->back()->with('error', 'Gagal! Silahkan coba lagi');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Movie $movie)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $movie = Movie::find($id);
        return view('admin.movie.edit', compact('movie'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Movie $movies, $id)
    {
        //cek seluruh request (data dr input)
        // dd($request->all());
        $request->validate([
            'title' => 'required',
            'duration' => 'required',
            'genre' => 'required',
            'director' => 'required',
            'age_rating' => 'required|numeric',
            //mimes => jenis file yg boleh diupload
            'poster' => 'mimes:jpg,jpeg,png,svg,webp',
            'description' => 'required|min:10'
        ], [
            'title.required' => 'Judul film harus diisi',
            'duration.required' => 'Durasi film harus diisi',
            'genre.required' => 'Genre film harus diisi',
            'director.required' => 'Sutradara harus diisi',
            'age_rating.required' => 'Usia minimal harus diisi',
            'age_rating.numberic' => 'Usia minimal harus diisi dengan angka',
            'poster.mimes' => 'Poster file harus berupa JPG/JPEG/SVG/PNG/WEBP',
            'description' => 'Sinopsis harus diisi'
        ]);
        //data sebelumnya
        $movie = Movie::find($id);
        //jika ada file poster baru
        if ($request->file('poster')) {
            $fileSebelumnya = storage_path("app/public/" . $movie['poster']);
            //file_exists() : cek apakah file ada di storage/app/public/poster/nama.jpg
            if (file_exists($fileSebelumnya)) {
                //unlink() : hapus
                unlink($fileSebelumnya);
            }
            //$request -> file ('name_input) : ambil file yg diupload
            $gambar = $request->file('poster');
            //buat nama baru, nama acak untuk membedakan tiap file, akan menjadi: abcde poster.jpg
            //getClientOriginalExtention() : ambil extensi file
            $namaGambar = Str::random(5) . "-poster." . $gambar->getClientOriginalExtension();
            //storeAs -> menyimpan file, format storeAs(namafolder, namafile, visability)
            //hasil storeAs() berupa alamat file, visability, public/private
            $path = $gambar->storeAs("poster", $namaGambar, "public");
        }

        $updateData = Movie::where('id', $id)->update([
            'title' => $request->title,
            'duration' => $request->duration,
            'genre' => $request->genre,
            'director' => $request->director,
            'age_rating' => $request->age_rating,
            //
            'poster' => $path ?? $movie['poster'],
            'description' => $request->description,
            'actived' => 1
        ]);
        if ($updateData) {
            return redirect()->route('admin.movies.index')->with('success', 'Berhasil tambah data!');
        } else {
            return redirect()->back()->with('error', 'Gagal! Silahkan coba lagi');
        }
    }
    public function dataChart()
    {
        $movieActive = Movie::where('actived', 1)->count();
        $movieNonActive = Movie::where('actived', 0)->count();

        $labels = ['Film Aktif', 'Film Non-Aktif'];
        $data = [$movieActive, $movieNonActive];

        return response()->json([
            'labels' => $labels,
            'data' => $data
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $deleteData = Movie::where('id', $id)->delete();
        if ($deleteData) {
            return redirect()->route('admin.movies.index')->with('success', 'Berhasil Menghapus Data');
        } else {
            return redirect()->back()->with('failed', 'Gagal! Silahkan coba Lagi');
        }
    }
    public function nonAktif($id)
    {
        $movie = Movie::findOrFail($id);
        $movie->actived = 0; //ubah status jadi non-aktif
        $movie->save();

        return redirect()->route('admin.movies.index')->with('success', 'Film berhasil di nonaktifkan');
    }
    public function trash()
    {
        $movies = Movie::onlyTrashed()->get();
        return view('admin.movie.trash', compact('movies'));
    }
    public function restore($id)
    {
        $movie = Movie::onlyTrashed()->find($id);
        $movie->restore();
        return redirect()->route('admin.movies.index')->with('succcess', 'Berhasil mengembalikan data!');
    }
    public function deletePermanent($id)
    {
        $movie = Movie::onlyTrashed()->find($id);
        $movie->forceDelete();
        return redirect()->back()->with('success', 'Berhasil menghapus data!');
    }

    public function dataForDatatables()
    {
        // siapkan query eloquent dari model movie
        $movies = Movie::query();
        // Datatables::of($movie) :menyiapkan data untuk datatables, data diambil dari $movies
        return DataTables::of($movies)
        ->addIndexColumn()
        // memberikan nomor1,2,dst di columtable
        // addColum() : menambahkan data selain dari table movies, digunakan untuk button aksi dan data yang perlu di maipulasi
        ->addColumn('imgPoster', function($data){
            $urlImage = asset('storage'). "/" .$data['poster'];
            // menambahkan data baru bernama imgPOster dengan hasil tag img yang link nya uda nyambung ke storage "' untuk kontan ke variable
            return '<img src="'. $urlImage .'" width="200px">';
        })
        ->addColumn('activedBadge', function($data){
            // membuat activedBadge yang akan mengembalikan badge waarna sesuai status
            if($data->actived ==1 ){
                return '<span class="badge badge-success">Aktif</span>';
            }else{
                return '<span class="badge badge-secondary">Non-Aktif</span>';
            }
        })
        ->addColumn('buttons',function($data){
            $btnDetail = ' <button class="btn btn-secondary me-2" onclick=\'showModal
            ('.json_encode ($data).')\'>Detail</button>';
            $btnEdit = ' <a href="'.route('admin.movies.edit', $data['id']) .'"
            class="btn btn-primary me-2">Edit</a>';
            $btnDelete = ' <form class="me-2" action="'.route('admin.movies.delete',
            $data['id']).'" method="POST">'.
                        csrf_field() .
                        method_field('DELETE') .
                        '<button type="submit" class="btn btn-danger me-2">Hapus</button>
                    </form>';
            $btnNonaktif = '';
            if ($data->actived == 1){
                $btnNonaktif = '<form class="me-2" action="'.route('admin.movies.nonaktif', $data['id']) .'" method="POST">'.
                    csrf_field() .
                    method_field ('PUT') .
                    '<button type="sumbit" class="btn btn-warning me-2">Non-Aktifkan Film</a>
                    </form>';
            }
            return '<div class="d-flex justify-content-center">'.$btnDetail.$btnEdit.$btnDelete.$btnNonaktif.'</div>';
        })
        // rawCollums([]) :mendaftarkan colum  yang dibuat di addcolumn
        ->rawColumns(['imgPoster','activedBadge','buttons'])
        ->make(true);//mengubah query menjadi JSON (format yang bisa dibaca datatable)
    }
}
