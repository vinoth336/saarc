<?php

namespace App\Jobs;

use App\Imports\StudentsImport;
use App\Models\ImportHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportStudentDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function handle()
    {
        $fileName = basename($this->filePath);
        $totalRecords = 0;
        $successfulRecords = 0;
        $failedRecords = 0;
        $failedRecordDetails = [];
        $storagePath = Storage::path($this->filePath);
        DB::beginTransaction();

        try {
            $import = new StudentsImport();
            $import->import($storagePath);
            $totalRecords = $import->getRowCount();
            $successfulRecords = $import->getSuccessCount();
            foreach ($import->failures() as $failure) {
                $failedRecords++;
                $failedRecordDetails[] = [
                    'row' => $failure->row(),
                    'data' => $failure->values(),
                    'errors' => $failure->errors(),
                ];
            }
            ImportHistory::create([
                'file_name' => $fileName,
                'total_records' => $totalRecords,
                'successful_records' => $successfulRecords,
                'failed_records' => $failedRecords,
                'failed_record_details' => json_encode($failedRecordDetails),
            ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            ImportHistory::create([
                'file_name' => $fileName,
                'total_records' => $totalRecords,
                'successful_records' => 0,
                'failed_records' => $totalRecords,
                'failed_record_details' => [$e->getMessage()],
            ]);
        }
    }
}
