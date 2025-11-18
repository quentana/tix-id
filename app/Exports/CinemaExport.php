<?php

namespace App\Exports;

use App\Models\Cinema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;




class CinemaExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    private $rowNumber = 0;
    public function collection()
    {
        return Cinema::all();
    }
    public function headings():array{
        return['No','Nama Bioskop','Lokasi'];
    }
    public function map($cinema):array{
        return[
            ++$this->rowNumber,
            $cinema->name,
            $cinema->location,
        ];
    }
}
