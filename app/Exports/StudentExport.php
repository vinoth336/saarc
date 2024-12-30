<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;


class StudentExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStrictNullComparison, WithTitle, WithChunkReading, WithMapping, WithColumnFormatting
{
    use Exportable;

    public $searchValue;



    public function __construct($searchValue=null)
    {
        $this->searchValue = $searchValue;
    }

    public function query()
    {
        $searchValue = $this->searchValue;
        $query = Student::where(function($query) use ($searchValue) {
          $query->when($searchValue, function ($query) use($searchValue) {
              $query->where('roll_no', 'like', "%$searchValue%")
                  ->orWhere('name', 'like', "%$searchValue%")
                  ->orWhere('department', 'like', "%$searchValue%")
                  ->orWhere('sex', 'like', "%$searchValue%");
          });
        })->select([
            'roll_no', 'name', 'department', 'year', 'sex', 'dob',
            'created_at', 'updated_at'
        ]);

        return $query;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function headings(): array
    {
        return [
            'RollNo',
            'Name',
            'Department',
            'Sex',
            'Dob',
            'Created At',
            'Updated At',
        ];
    }

    public function map($student): array
    {
        return [
            $student->roll_no,
            $student->name,
            $student->department,
            $student->sex,
            Date::PHPToExcel($student->dob),
            Date::dateTimeToExcel($student->created_at),
            Date::dateTimeToExcel($student->updated_at),
        ];
    }

    public function title(): string
    {
        return 'Students';
    }
    public function chunkSize(): int
    {
        return 1000;
    }

    public function columnFormats(): array
    {
        return [
            'E' => 'D-m-Y',
            'F' => 'd-m-Y',
            'G' => 'd-m-Y',
        ];
    }
}
