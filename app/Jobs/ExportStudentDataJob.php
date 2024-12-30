<?php

namespace App\Jobs;

use App\Exports\StudentExport;
use App\Models\ExportHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ExportStudentDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $searchValue;

    public $fileName;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($searchValue, $fileName)
    {
        $this->searchValue = $searchValue;
        $this->fileName = $fileName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            $timestamp = now()->format('Y_m_d_His'); // Example: 2024_12_29_134500
            $fileName = "exports/" . $this->fileName . ".xlsx";
            Excel::store(new StudentExport($this->searchValue), $fileName);
            $fileGeneratedSuccessfully = 1;
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            $fileGeneratedSuccessfully = 0;
        }
        ExportHistory::create([
            'file_name' => $fileName,
            'status' => $fileGeneratedSuccessfully
        ]);
        DB::commit();
    }
}
