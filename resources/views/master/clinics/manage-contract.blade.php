@extends('master.layouts.app')

@section('title', 'Manage Contracts - ' . $clinic->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-file-contract me-2"></i>Manage Contracts - {{ $clinic->name }}
        </h1>
        <a href="{{ route('master.clinics.show', $clinic) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Clinic
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Existing Contracts -->
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Contract History</h5>
                </div>
                <div class="card-body">
                    @if($contracts->count() > 0)
                        <div class="list-group">
                            @foreach($contracts as $contract)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $contract->contract_title }}</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ $contract->start_date?->format('Y-m-d') }} - {{ $contract->end_date?->format('Y-m-d') }}
                                        </small>
                                        @if($contract->annual_fee)
                                        <div class="mt-1">
                                            <span class="badge bg-success">{{ number_format($contract->annual_fee, 0) }} IQD/year</span>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="ms-3">
                                        @if($contract->status === 'draft')
                                            <span class="badge bg-info">Draft</span>
                                        @elseif($contract->status === 'pending')
                                            <span class="badge bg-warning">Pending Acceptance</span>
                                        @elseif($contract->status === 'accepted')
                                            <span class="badge bg-success">Accepted</span>
                                        @elseif($contract->status === 'expired')
                                            <span class="badge bg-secondary">Expired</span>
                                        @else
                                            <span class="badge bg-dark">{{ ucfirst($contract->status) }}</span>
                                        @endif
                                    </div>
                                </div>

                                @if($contract->status === 'accepted' && $contract->accepted_at)
                                <div class="mt-2 pt-2 border-top">
                                    <small class="text-success">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Accepted by {{ $contract->acceptedBy->full_name ?? 'Unknown' }} on {{ $contract->accepted_at->format('Y-m-d H:i') }}
                                    </small>
                                </div>
                                @endif

                                <!-- Action Buttons Based on Status -->
                                <div class="mt-2">
                                    @if($contract->status === 'draft')
                                    <!-- Send Contract Button -->
                                    <form action="{{ route('master.clinics.send-contract', [$clinic, $contract]) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Send this contract to the clinic? The clinic admin will need to accept it to access the system.');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-paper-plane me-1"></i>Send Contract
                                        </button>
                                    </form>

                                    <!-- Delete Draft Button -->
                                    <form action="{{ route('master.clinics.delete-contract', [$clinic, $contract]) }}" method="POST" class="d-inline ms-2"
                                          onsubmit="return confirm('Delete this draft contract?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash me-1"></i>Delete Draft
                                        </button>
                                    </form>
                                    @endif

                                    @if($contract->status === 'accepted' || $contract->status === 'expired')
                                    <!-- Renew Contract Button -->
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#renewModal{{ $contract->id }}">
                                        <i class="fas fa-redo me-1"></i>Renew Contract
                                    </button>
                                    @endif
                                </div>

                                <!-- Renew Modal -->
                                <div class="modal fade" id="renewModal{{ $contract->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('master.clinics.renew-contract', [$clinic, $contract]) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Renew Contract</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="annual_fee_{{ $contract->id }}" class="form-label">Annual Fee (IQD)</label>
                                                        <input type="number" class="form-control" id="annual_fee_{{ $contract->id }}" name="annual_fee" 
                                                               value="{{ $contract->annual_fee }}" min="0" step="0.01">
                                                        <small class="text-muted">Leave blank to keep current fee</small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="contract_duration_months_{{ $contract->id }}" class="form-label">Duration (Months)</label>
                                                        <input type="number" class="form-control" id="contract_duration_months_{{ $contract->id }}" name="contract_duration_months" 
                                                               value="{{ $contract->contract_duration_months ?? 12 }}" min="1" max="120">
                                                    </div>
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        This will create a new contract with the same terms and mark the current one as expired.
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-redo me-1"></i>Renew Contract
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-file-contract fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">No contracts found for this clinic.</p>
                            <small>Create a new contract using the form on the right.</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Create New Contract -->
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Create New Contract</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('master.clinics.store-contract', $clinic) }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contract_title" class="form-label">Contract Title</label>
                                <input type="text" class="form-control @error('contract_title') is-invalid @enderror"
                                       id="contract_title" name="contract_title"
                                       value="{{ old('contract_title', 'ConCure Cloud Service Agreement') }}">
                                @error('contract_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="annual_fee" class="form-label">Annual Fee (IQD)</label>
                                <input type="number" class="form-control @error('annual_fee') is-invalid @enderror"
                                       id="annual_fee" name="annual_fee"
                                       value="{{ old('annual_fee') }}" min="0" step="0.01" placeholder="e.g., 1000000">
                                @error('annual_fee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="contract_duration_months" class="form-label">Duration (Months)</label>
                                <input type="number" class="form-control @error('contract_duration_months') is-invalid @enderror"
                                       id="contract_duration_months" name="contract_duration_months"
                                       value="{{ old('contract_duration_months', 12) }}" min="1" max="120">
                                @error('contract_duration_months')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                   id="start_date" name="start_date"
                                   value="{{ old('start_date', date('Y-m-d')) }}">
                            <small class="text-muted">Leave as today if starting immediately</small>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="contract_content" class="form-label fw-bold">Contract Terms and Conditions</label>
                            <textarea class="form-control font-monospace @error('contract_content') is-invalid @enderror"
                                      id="contract_content" name="contract_content" rows="15"
                                      style="font-size: 0.875rem;">{{ old('contract_content', $defaultContractTemplate) }}</textarea>
                            @error('contract_content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                This contract will be displayed to the clinic admin for review and acceptance.
                            </small>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Note:</strong> Once created, the contract status will be "Pending".
                            The clinic admin must accept it on their next login to continue using the system.
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save me-2"></i>Create Contract
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
