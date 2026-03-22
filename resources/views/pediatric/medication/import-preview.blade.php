@extends('layouts.app')

@section('title', __('Import Preview'))

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-1">
                <i class="fas fa-eye me-2 text-primary"></i>
                {{ __('Import Preview') }}
            </h1>
            <p class="text-muted mb-0">{{ __('Review data before importing') }}</p>
        </div>
    </div>

    @if(count($errors) > 0)
    <div class="alert alert-warning">
        <h6 class="fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>{{ __('Validation Issues') }}</h6>
        <ul class="mb-0 small">
            @foreach($errors as $e)
            <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0" style="font-size:0.8rem;">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Drug</th>
                            <th>Brand</th>
                            <th>Category</th>
                            <th>Form</th>
                            <th>Concentration</th>
                            <th>mg/kg min</th>
                            <th>mg/kg max</th>
                            <th>Max Daily</th>
                            <th>Freq</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $item)
                        <tr class="{{ !empty($item['_errors']) ? 'table-danger' : 'table-success' }}">
                            <td>{{ $item['_row'] }}</td>
                            <td><strong>{{ $item['generic_name'] ?? '' }}</strong></td>
                            <td>{{ $item['brand_name'] ?? '' }}</td>
                            <td>{{ $item['category'] ?? '' }}</td>
                            <td>{{ $item['form'] ?? '' }}</td>
                            <td>{{ $item['concentration'] ?? '' }}</td>
                            <td>{{ $item['mg_per_kg_min'] ?? '' }}</td>
                            <td>{{ $item['mg_per_kg_max'] ?? '' }}</td>
                            <td>{{ $item['max_daily_mg'] ?? '' }}</td>
                            <td>{{ $item['frequency_per_day'] ?? '' }}</td>
                            <td>
                                @if(!empty($item['_errors']))
                                <span class="badge bg-danger">Error</span>
                                @else
                                <span class="badge bg-success">Ready</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex gap-3">
        <form method="POST" action="{{ route('pediatric.medication.import.confirm') }}">
            @csrf
            <button type="submit" class="btn btn-success">
                <i class="fas fa-check me-2"></i>{{ __('Confirm Import') }}
                <span class="badge bg-white text-success ms-1">{{ collect($data)->filter(fn($i) => empty($i['_errors']))->count() }} rows</span>
            </button>
        </form>
        <a href="{{ route('pediatric.medication.import') }}" class="btn btn-outline-secondary">
            <i class="fas fa-times me-1"></i>{{ __('Cancel') }}
        </a>
    </div>
</div>
@endsection

