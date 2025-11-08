@extends('layouts.app')

@section('title', __('Medical Image Bank'))

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
      <i class="fas fa-images text-primary me-2"></i>
      {{ __('Medical Image Bank') }}
    </h1>
  </div>

  <!-- Filters -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" action="{{ route('image-bank.index') }}" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label">{{ __('Patient') }}</label>
          <input type="text" name="patient" value="{{ $filters['patient'] ?? '' }}" class="form-control" list="patientsDatalist" placeholder="{{ __('Search by name or code') }}">
          <datalist id="patientsDatalist">
            @foreach($patients as $p)
              <option value="{{ $p->id }}">{{ $p->first_name }} {{ $p->last_name }} ({{ $p->patient_id }})</option>
            @endforeach
          </datalist>
          <small class="text-muted">{{ __('Type numeric ID for direct match, or name/code for search') }}</small>
        </div>
        <div class="col-md-3">
          <label class="form-label">{{ __('Patient Condition') }}</label>
          <select name="condition" class="form-select">
            <option value="">{{ __('All') }}</option>
            @foreach($conditions as $cond)
              <option value="{{ $cond }}" {{ ($filters['condition'] ?? '') === $cond ? 'selected' : '' }}>{{ $cond }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">{{ __('Image Tag (Condition)') }}</label>
          <select name="tag" class="form-select">
            <option value="">{{ __('All') }}</option>
            @foreach($tags as $t)
              <option value="{{ $t }}" {{ ($filters['tag'] ?? '') === $t ? 'selected' : '' }}>#{{ $t }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">{{ __('Search') }}</label>
          <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="{{ __('Caption or file name') }}">
        </div>
        <div class="col-12 d-flex gap-2">
          <button class="btn btn-primary" type="submit"><i class="fas fa-filter me-1"></i> {{ __('Filter') }}</button>
          <a href="{{ route('image-bank.index') }}" class="btn btn-outline-secondary">{{ __('Reset') }}</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Results -->
  <div class="card">
    <div class="card-body">
      @if($images->count() === 0)
        <div class="text-center py-5">
          <i class="fas fa-image fa-2x text-muted mb-2"></i>
          <p class="text-muted mb-0">{{ __('No images found for the selected filters.') }}</p>
        </div>
      @else
        <div class="row g-3">
          @foreach($images as $img)
            <div class="col-6 col-md-4 col-lg-3">
              <div class="border rounded p-2 h-100 d-flex flex-column">
                <a href="{{ $img->url }}" target="_blank" class="d-block mb-2" title="{{ $img->filename }}">
                  <img src="{{ $img->url }}" alt="" class="img-fluid rounded" style="object-fit:cover; width:100%; height:160px;">
                </a>
                <div class="small text-muted mb-1">{{ $img->patient->first_name ?? '' }} {{ $img->patient->last_name ?? '' }}</div>
                @if(is_array($img->condition_tags) && count($img->condition_tags))
                  <div class="mb-1">
                    @foreach($img->condition_tags as $t)
                      <span class="badge bg-light text-dark border me-1">#{{ $t }}</span>
                    @endforeach
                  </div>
                @endif
                @if($img->caption)
                  <div class="small mb-2">{{ \Illuminate\Support\Str::limit($img->caption, 60) }}</div>
                @endif
                <div class="d-flex justify-content-between align-items-center mt-auto">
                  <small class="text-muted">{{ $img->created_at?->format('Y-m-d') }}</small>
                  <div class="btn-group btn-group-sm" role="group">
                    <a href="{{ $img->url }}" target="_blank" class="btn btn-outline-info" title="{{ __('Open') }}"><i class="fas fa-external-link-alt"></i></a>
                    <button type="button" class="btn btn-outline-secondary" onclick="openAndPrint('{{ $img->url }}')" title="{{ __('Print') }}"><i class="fas fa-print"></i></button>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>

        <div class="mt-4">
          {{ $images->links() }}
        </div>
      @endif
    </div>
  </div>
</div>

<script>
function openAndPrint(url){
  const w = window.open(url, '_blank');
  if(!w){ return; }
  const tryPrint = () => { try { w.focus(); w.print(); } catch(e){} };
  w.onload = tryPrint;
  setTimeout(tryPrint, 1200);
}
</script>
@endsection

