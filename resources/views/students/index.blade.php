@extends('layouts.app')
@section('page-styles')
    <style>
        .dt-buttons {
            width: 50%;
            float: left;
            text-align: left;
            margin-bottom: 10px;

        }

        .dt-search {
            width: 50%;
            float: right;
            text-align: right;
            margin-bottom: 10px;
        }

        .dt-info {
            width: 50%;
            float: left;
            text-align: left;
            margin-top: 10px;
        }

        .dt-paging {
            width: 50%;
            float: right;
            text-align: right;
            margin-top: 10px;
        }

        .hide {
            display: none
        }
    </style>
@endsection

@section('content')
    <div class="container">
        @if(session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session()->get('error') }}
                <button type="button" class="btn btn-sm close float-end" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if(session()->has('message') && session()->has('status') && session()->get('status') == 'true')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session()->get('message') }}
                <button type="button" class="btn btn-sm close float-end" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-12 justify-content-end text-end mb-3" id="show_import_section_container">
                @if ($lastExportDetail)
                    <a href="{{ route('students.download_file') }}" target="_blank">
                        Export Available
                    </a>
                @endif
                <button class="btn btn-sm btn-info" type="button" id="show_import_section">
                    Import Record
                </button>
            </div>
            <div class="col-md-12 hide" id="show_import_input_option_container">
                <form method="post" action="student/import" id="import-record">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-auto">
                            <input type="file" class="form-control" id="import_student_record"
                                   name="import_student_record"
                                   accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                                   placeholder="Import Student Record" required>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-danger" type="button" id="hide_import_input_option_container">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </form>
                <div id="accordion" class="mt-2 mb-2">
                    <div class="card">
                        <div class="card-header" id="headingOne">
                            <h5 class="mb-0">
                                <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne"
                                        aria-expanded="true" aria-controls="collapseOne">
                                    Last Import Status
                                </button>
                            </h5>
                        </div>
                        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne"
                             data-parent="#accordion">
                            <div class="card-body last_import_status">
                                <div class="d-inline">Total Record : <span
                                        id="total_record_in_file">{{ $lastImportDetail?->total_records }}</span></div>
                                <div class="d-inline m-lg-2">Total Record Successfully Import : <span
                                        id="total_record_import">{{ $lastImportDetail?->successful_records }}</span>
                                </div>
                                <div class="d-inline ml-2">Total Invalid Record <span
                                        id="total_invalid_record">{{ $lastImportDetail?->failed_records }}</span></div>
                                <table class="table table-bordered mt-2" id="import_status">
                                    <thead>
                                    <tr>
                                        <th>Record</th>
                                        <th>Error</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if(is_array($lastImportDetail?->failed_record_details))
                                        @foreach($lastImportDetail?->failed_record_details as $failedRecordDetail)
                                            <tr>
                                                <td>
                                                    {{ implode(" | ", $failedRecordDetail['data'] ?? []) }}
                                                </td>
                                                <td>
                                                    {{ implode(" , ", $failedRecordDetail['errors'] ?? []) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="2" class="text-center">
                                                No Error Occurred In the Last Import
                                            </td>
                                        </tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">{{ __('Students Record') }}</div>
                    <div class="card-body">
                        {!! $dataTable->table() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('page-scripts')
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/3.2.0/js/dataTables.buttons.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.dataTables.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.html5.min.js"></script>
    <script src="{{ asset('vendor/datatables/buttons.server-side.js') }}"></script>

    {!! $dataTable->scripts() !!}
    <script type="text/javascript">
        $('#import-record').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('{{ route('students.import') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), // Include CSRF token
                },
            })
                .then(response => response.json())
                .then(function(data) {
                    alert(data.message || 'Import processing started.')
                    $("#import_student_record").val('');
                    $("#hide_import_input_option_container").trigger('click');
                    $(".last_import_status").html('Please Reload Page To Get The Latest Status.');

                })
                .catch(err => alert('Error uploading file.'));
        });

        // Handle export
        $('#export-button').on('click', function () {
            fetch('/students/export')
                .then(response => response.json())
                .then(data => alert(data.message || 'Export processing started.'))
                .catch(err => alert('Error starting export.'));
        });

        $("#show_import_section").on("click", function () {
            $("#show_import_section").addClass('hide');
            $("#show_import_input_option_container").removeClass('hide');
        });

        $("#hide_import_input_option_container").on("click", function () {
            $("#show_import_input_option_container").addClass('hide');
            $("#show_import_section").removeClass('hide');
        });
    </script>
@endsection
