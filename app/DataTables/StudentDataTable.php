<?php

namespace App\DataTables;

use App\Exports\StudentExport;
use App\Jobs\ExportStudentDataJob;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class StudentDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     * @return \Yajra\DataTables\EloquentDataTable
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('dob', function ($student) {
                return date("d-m-Y", strtotime($student->dob));
            })
            ->editColumn('created_at', function ($student) {
                return date("d-m-Y", strtotime($student->created_at));
            })
            ->editColumn('updated_at', function ($student) {
                return date("d-m-Y", strtotime($student->updated_at));
            })
            ;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Student $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Student $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('student-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->selectStyleSingle()
                    ->dom('Bfrtip')
                    ->selectStyleSingle()
                    ->buttons(
                        Button::make(['extend' => 'reload', 'className' => 'btn btn-sm btn-success', 'text' => 'Reload']),
                        Button::make(['extend' => 'excel', 'className' => 'btn btn-sm btn-success', 'text' => 'Excel']),
                    );
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array
     */
    public function getColumns(): array
    {
        return [
            'DT_RowIndex' => ['title' => 'SNo', 'orderable' => false, 'searchable' => false],
            'roll_no' => ['title' => 'RollNo', 'orderable' => true, 'searchable' => true],
            'name' => ['title' => 'Name', 'orderable' => true, 'searchable' => true],
            'department' => ['title' => 'Department', 'orderable' => true, 'searchable' => true],
            'dob' => ['title' => 'DOB', 'orderable' => true, 'searchable' => true],
            'created_at' => ['title' => 'Created At', 'orderable' => true, 'searchable' => true],
            'updated_at' => ['title' => 'Updated At', 'orderable' => true, 'searchable' => true],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'Student_' . date('YmdHis');
    }

    public function excel()
    {
        $student = new Student();
        $query = $this->query($student);
        $filename = $this->filename();
        $searchValue = request()->input('search.value');
        dispatch(new ExportStudentDataJob($searchValue, $filename));

        return back()->with(['status' => true, 'message' => 'Export processing started.']);
        //return Excel::download(new StudentExport($searchValue), $filename . '.xlsx');
    }
}
