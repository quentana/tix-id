<?php

namespace App\Exports;

use App\Models\Schedule;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ScheduleExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    private $rowNumber = 0;

    public function collection()
    {
        return Schedule::with(['cinema','movie'])->get();
    }
    public function headings():array{
        return['No','nama bioskop','Judul bioskop','Harga','Jam Tayang'];
    }
    public function map($schedule):array{
        return [
            ++$this->rowNumber,
            $schedule->cinema->name ?? '-',
            $schedule->movie->title ?? '-',
            'Rp. ' . number_format($schedule->price, 0, ',', '.'),
            implode(", ", $schedule->hours), // gabungkan array jam tayang
        ];
    }

}
