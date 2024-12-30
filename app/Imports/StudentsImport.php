<?php

namespace App\Imports;

use App\Models\Student;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
class StudentsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    private $rowCount = 0;
    private $successCount = 0;



    public function model(array $row)
    {
        \Log::info('testing');
        $this->rowCount++;
        $student = Student::updateOrCreate([
            'roll_no' => $row['rollno'],
        ], [
            'roll_no' => $row['rollno'],
            'name' => $row['name'],
            'department' => $row['department'],
            'year' => $row['year'],
            'dob' => $this->parseExcelDate($row['dob']),
            'sex' => $row['sex'],
        ]);

        $this->successCount++;

        return $student;
    }

    public function rules(): array
    {
        return [
            '*.rollno' => ['required', 'string', 'max:20'],
            '*.name' => ['required', 'string', 'max:255'],
            '*.department' => ['required', 'string', 'max:255'],
            '*.year' => ['required', 'integer', 'between:1,4'],
            '*.dob' => ['required', function ($attribute, $value, $fail) {
                $value = $this->parseExcelDate($value);
                if (!$this->isValidDate($value)) {
                    $fail("The $attribute field must be a valid date (Y-m-d).");
                }
            }],
            '*.sex' => ['required', Rule::in(['Male', 'Female'])],
        ];
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    private function parseExcelDate($value)
    {
        // Check if value is a numeric date
        if (is_numeric($value)) {
            return Date::excelToDateTimeObject($value)->format('Y-m-d');
        }

        // If it's already a valid date format, return as is
        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null; // Invalid date
        }
    }

    private function isValidDate($value)
    {
        try {
            Carbon::createFromFormat('Y-m-d', $value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
