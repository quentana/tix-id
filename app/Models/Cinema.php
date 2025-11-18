<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cinema extends Model
{
    //mengaktifkan  softdeletes : menghapus tanpa benar benar hilang di db
    use SoftDeletes;

    //mendaftarkan colum-colum  selain yang bawaaannya, selain id dan timestampts softdeletes. agar dapat diisi datanya ke colum tsb
    protected $fillable = ['name','location'];
    // mendefinisikan relasi one to many (Cinema ke schedule)
    // many di schedule,jd nama  fungsi jamak (s)
    public function schedules()
    {
        // panggil jenis relasi
        return $this->hasMany(Schedule::class);
        
    }
}
