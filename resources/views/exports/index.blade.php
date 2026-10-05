@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Export History</h2>
            <button class="btn btn-primary" id="startExportBtn">Export Products</button>
        </div>
        
        <!-- Live Progress Section (Hidden initially) -->
        <div class="card mb-4 d-none" id="exportProgressCard">
            <div class="card-body">
                <h5 class="card-title">Exporting Products...</h5>
                <p id="exportStatusText">Status: Pending</p>
                <div class="progress mb-3" style="height: 25px;">
                    <div id="exportProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>
                <div id="exportActionContainer"></div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Requested At</th>
                                <th>Requested By</th>
                                <th>File Name</th>
                                <th>Status</th>
                                <th>Rows</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($exports as $export)
                            <tr>
                                <td>{{ $export->created_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $export->user->name ?? 'Unknown' }}</td>
                                <td>{{ $export->file_name }}</td>
                                <td>
                                    @if($export->status === 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($export->status === 'failed')
                                        <span class="badge bg-danger">Failed</span>
                                    @else
                                        <span class="badge bg-warning text-dark">{{ ucfirst($export->status) }}</span>
                                    @endif
                                </td>
                                <td>{{ $export->total_rows }}</td>
                                <td>
                                    @if($export->status === 'completed')
                                        <a href="{{ route('exports.download', $export->id) }}" class="btn btn-sm btn-success">Download</a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No export history found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startBtn = document.getElementById('startExportBtn');
    const card = document.getElementById('exportProgressCard');
    const bar = document.getElementById('exportProgressBar');
    const statusText = document.getElementById('exportStatusText');
    const actionContainer = document.getElementById('exportActionContainer');
    let pollInterval;

    startBtn.addEventListener('click', function() {
        if(!confirm('Start a new export?')) return;
        
        startBtn.disabled = true;
        card.classList.remove('d-none');
        bar.style.width = '0%';
        bar.innerText = '0%';
        statusText.innerText = 'Status: Starting job...';
        actionContainer.innerHTML = '';
        
        fetch('{{ route('exports.start') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.export_id) {
                pollInterval = setInterval(() => checkStatus(data.export_id), 2000);
            } else {
                alert('Error starting export');
                startBtn.disabled = false;
            }
        })
        .catch(err => {
            alert('Error starting export');
            startBtn.disabled = false;
        });
    });

    function checkStatus(id) {
        fetch(`/exports/status/${id}`)
        .then(response => response.json())
        .then(data => {
            bar.style.width = data.progress + '%';
            bar.innerText = data.progress + '%';
            statusText.innerText = `Status: ${data.status.toUpperCase()} (${data.processed_rows} / ${data.total_rows} rows)`;
            
            if(data.status === 'completed') {
                clearInterval(pollInterval);
                bar.classList.remove('progress-bar-animated', 'progress-bar-striped');
                actionContainer.innerHTML = `<a href="${data.download_url}" class="btn btn-success">Download File</a> <a href="" class="btn btn-outline-secondary ms-2">Refresh Page</a>`;
                startBtn.disabled = false;
            } else if(data.status === 'failed') {
                clearInterval(pollInterval);
                bar.classList.remove('progress-bar-animated', 'bg-success');
                bar.classList.add('bg-danger');
                actionContainer.innerHTML = `<div class="alert alert-danger">Error: ${data.error_message}</div> <a href="" class="btn btn-outline-secondary">Refresh Page</a>`;
                startBtn.disabled = false;
            }
        });
    }
});
</script>
@endsection
