<?php

namespace App\Http\Controllers;

use App\DataTables\StudentDataTable;
use App\Imports\StudentsImport;
use App\Jobs\ImportStudentDataJob;
use App\Models\ExportHistory;
use App\Models\ImportHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class StudentController extends Controller
{

    public function index(StudentDataTable $dataTable)
    {
        $lastImportDetail = ImportHistory::latest()->first();
        $lastExportDetail = ExportHistory::latest()->first();

        return $dataTable->render('students.index', ['lastImportDetail' => $lastImportDetail, 'lastExportDetail' => $lastExportDetail]);
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'import_student_record' => 'required|mimes:xlsx,csv',
            ]);
            $file = $request->file('import_student_record');
            $timestamp = now()->format('Y_m_d_His');
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $fileName = "{$originalName}_{$timestamp}.{$extension}";
            Storage::put($fileName, $file->getContent());
            ImportStudentDataJob::dispatch($fileName);

            return response()->json(['status' => true, 'message' => 'Student data import is being processed.']);
        } catch (\Exception $exception) {
            return response()->json(['status' => false, 'message' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function downloadFile(Request $request)
    {
        try {
            $lastExportDetail = ExportHistory::latest()->first();

            return Storage::download($lastExportDetail->file_name);
        } catch (\Exception $exception) {

            return back()->with(['error' => $exception->getMessage()]);
        }
    }
}
