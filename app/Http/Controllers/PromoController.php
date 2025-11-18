<?php

namespace App\Http\Controllers;

use App\Exports\PromoExport;
use App\Models\Promo;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class PromoController extends Controller
{
    public function index()
    {
        $promos = Promo::all();
        return view('staff.promo.index', compact('promos'));
    }

    public function create()
    {
        return view('staff.promo.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'promo_code' => 'required',
            'discount' => 'required',
            'type' => 'required',

        ], [
            'promo_code.required' => 'Kode promo harus diisi',
            'discount.required' => 'Potongan Harga harus diisi',
            'type.required' => 'Pilih Tipe  Harus diisi'
        ]);
        if ($request->type === 'percent' && $request->discount > 100) {
            return redirect()->back()->with('error', 'Discount persen tidak boleh lebih dari 100');
        }
        if ($request->type === 'rupiah' && $request->discount < 1000) {
            return redirect()->back()->with('error', 'Discount rupiah tidak boleh kurang dari 1000');
        }

        $createData = Promo::create([
            'promo_code' => $request->promo_code,
            'discount' => $request->discount,
            'type' => $request->type,
        ]);
        if ($createData) {
            return redirect()->route('staff.promos.index')->with('success', 'Berhasil Menambah data!');
        } else {
            return redirect()->back()->with('error', 'Gagal Silahkan coba lagi !');
        }


    }

    public function show(Promo $promo)
    {
        return redirect()->route('staff.promos.index');
    }

    public function edit(Promo $promo)
    {
        return view('staff.promo.edit', compact('promo'));
    }

    public function update(Request $request,$id)
    {
        $request->validate([
            'promo_code' => 'required',
            'discount' => 'required',
            'type' => 'required'
        ], [
            'promo_code.required' => 'Kode promo harus diisi',
            'discount.required' => 'Potongan Harga harus diisi',
            'type.required' => 'Pilih Tipe  Harus diisi'
        ]);
         if ($request->type == 'percent' && $request->discount > 100) {
            return redirect()->back()->with('error', 'Discount persen tidak boleh lebih dari 100');
        }
        if ($request->type == 'rupiah' && $request->discount < 1000) {
            return redirect()->back()->with('error', 'Discount rupiah tidak boleh kurang dari 1000');
        }

        // kirim data
        $updateData = Promo::where('id',$id)->update([
            'promo_code' => $request->promo_code,
            'discount' => $request->discount,
            'type' => $request->type,
            'actived' => 1
        ]);
        if($updateData){
            return redirect()->route('staff.promos.index')->with('success', 'Berhasil Mengubah data!');
        }else{
             return redirect()->back()->with('failed, Gagal! Silahkan coba lagi');
        }
    }

    public function destroy(Promo $promo)
    {
        $promo->delete();
        return redirect()->route('staff.promos.index')->with('success', 'Berhasil Menghapus data!');
    }
    public function exportExcel()
    {
        $fileName = 'data-promo.xlsx';
        return Excel::download(new PromoExport, $fileName);
    }
    public function trash()
    {
        $promos = Promo::onlyTrashed()->get();
        return view('staff.promo.trash',compact('promos'));
    }
    public function restore($id)
    {
        $promo = Promo::onlyTrashed()->find($id);
        $promo -> restore();
        return redirect()->route('staff.promos.index')->with('success','Berhasil mengembalikan data!');
    }
    public function deletePermanent($id)
    {
        $promo = Promo::onlyTrashed()->find($id);
        $promo -> forceDelete();
        return redirect()->back()->with('success','Berhasil Menghapus Data!');
    }
    public function dataForDatatables()
    {
        $promos = Promo::query();
        return DataTables::of($promos)
        ->addIndexColumn()
        ->addColumn('totaldiscount', function($data){
            if($data -> type == 'percent'){
                return $data->discount .'%';
            }else{
                return 'Rp.'. number_format($data->discount, 0,',','.');
            }

        })
        ->addColumn('buttons',function($data){
            $btnEdit = '<a href=" '.route('staff.promos.edit', $data['id']).'
            " class="btn btn-primary me-2">Edit</a>';
            $btnDelete = ' <form action="'. route('staff.promos.delete',
                        $data['id']) .' " method="POST">'.
                        csrf_field().
                        method_field('DELETE').
                        '<button type="submit" class="btn btn-danger me-2">Hapus</button>
                    </form>';
            return'<div class="d-flex jsutify-content-center">'.$btnEdit.$btnDelete.'</div>';
        })
        ->rawColumns(['totaldiscount','buttons'])
        ->make( true);
    }
}
