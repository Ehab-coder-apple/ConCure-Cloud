@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-3">Surgical Cases</h1>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="mb-0 text-muted">List of surgical cases for this clinic.</p>
        <a href="{{ route('surgery.create') }}" class="btn btn-primary">New Surgical Case</a>
    </div>

    @if ($cases->isEmpty())
        <div class="alert alert-info">No surgical cases yet. Click "New Surgical Case" to create the first one.</div>
    @else
        <div class="table-responsive">
            <table class="table table-sm table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Patient</th>
                        <th>Primary Surgeon</th>
                        <th>Status</th>
                        <th>Scheduled</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($cases as $case)
                    <tr>
                        <td>{{ $case->id }}</td>
                        <td>{{ optional($case->patient)->first_name }} {{ optional($case->patient)->last_name }}</td>
                        <td>{{ optional($case->primarySurgeon)->first_name }} {{ optional($case->primarySurgeon)->last_name }}</td>
                        <td><span class="badge bg-secondary text-uppercase">{{ $case->status }}</span></td>
                        <td>{{ optional($case->scheduled_at)->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            <a href="{{ route('surgery.show', $case) }}" class="btn btn-sm btn-outline-primary">View</a>
                            <form action="{{ route('surgery.destroy', $case) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this surgical case? This will also delete all related operations and visits. This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $cases->links() }}
        </div>
    @endif
</div>
@endsection
