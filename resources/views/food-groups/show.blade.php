@extends('layouts.app')

@section('title', $foodGroup->translated_name)

@push('styles')
<style>
.table td, .table th {
    color: #333 !important;
}
.table .badge {
    color: #fff !important;
}
.card-body .row .col-sm-8,
.card-body .row .col-sm-4 {
    color: #333 !important;
}
</style>
@endpush

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-layer-group text-primary"></i>
                        {{ $foodGroup->translated_name }}
                    </h1>
                    <p class="text-muted">
                        {{ $foodGroup->translated_description ?? __('No description available') }}
                    </p>
                </div>
                <div>
                    <a href="{{ route('food-groups.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ __('Back to Food Groups') }}
                    </a>
                    @can('manage-food-composition')
                        <a href="{{ route('food-groups.edit', $foodGroup) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> {{ __('Edit Group') }}
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Group Information -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> {{ __('Group Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>{{ __('Name') }}:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $foodGroup->name }}
                        </div>
                    </div>

                    @if($foodGroup->name_translations)
                        <hr>
                        <h6 class="font-weight-bold text-primary">{{ __('Translations') }}</h6>
                        @foreach($foodGroup->name_translations as $locale => $translation)
                            <div class="row mb-2">
                                <div class="col-sm-4">
                                    <strong>
                                        @if($locale === 'en') {{ __('English') }}
                                        @elseif($locale === 'ar') {{ __('Arabic') }}
                                        @elseif($locale === 'ku') {{ __('Kurdish') }}
                                        @else {{ ucfirst($locale) }}
                                        @endif:
                                    </strong>
                                </div>
                                <div class="col-sm-8">
                                    {{ $translation }}
                                </div>
                            </div>
                        @endforeach
                    @endif

                    <hr>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>{{ __('Status') }}:</strong>
                        </div>
                        <div class="col-sm-8">
                            @if($foodGroup->is_active)
                                <span class="badge badge-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge badge-secondary">{{ __('Inactive') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>{{ __('Foods Count') }}:</strong>
                        </div>
                        <div class="col-sm-8">
                            <span class="badge badge-info">{{ $foodGroup->foods->count() }}</span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>{{ __('Display Order') }}:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $foodGroup->display_order ?? 0 }}
                        </div>
                    </div>

                    @if($foodGroup->description || $foodGroup->description_translations)
                        <hr>
                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <strong>{{ __('Description') }}:</strong>
                            </div>
                            <div class="col-sm-8">
                                {{ $foodGroup->description ?? __('No description') }}
                            </div>
                        </div>

                        @if($foodGroup->description_translations)
                            <div class="mt-3">
                                <h6 class="font-weight-bold text-primary">{{ __('Description Translations') }}</h6>
                                @foreach($foodGroup->description_translations as $locale => $translation)
                                    <div class="row mb-2">
                                        <div class="col-sm-4">
                                            <strong>
                                                @if($locale === 'en') {{ __('English') }}
                                                @elseif($locale === 'ar') {{ __('Arabic') }}
                                                @elseif($locale === 'ku') {{ __('Kurdish') }}
                                                @else {{ ucfirst($locale) }}
                                                @endif:
                                            </strong>
                                        </div>
                                        <div class="col-sm-8">
                                            {{ $translation }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif

                    <hr>
                    <small class="text-muted">
                        {{ __('Created') }}: {{ $foodGroup->created_at->format('M d, Y \a\t g:i A') }}
                        @if($foodGroup->updated_at != $foodGroup->created_at)
                            <br>{{ __('Last updated') }}: {{ $foodGroup->updated_at->format('M d, Y \a\t g:i A') }}
                        @endif
                    </small>
                </div>
            </div>
        </div>

        <!-- Foods in this Group -->
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-apple-alt"></i> {{ __('Foods in this Group') }}
                        <span class="badge badge-info ml-2">{{ $foodGroup->foods->count() }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    @if($foodGroup->foods->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ __('Food Name') }}</th>
                                        <th>{{ __('Calories') }}</th>
                                        <th>{{ __('Protein') }}</th>
                                        <th>{{ __('Carbs') }}</th>
                                        <th>{{ __('Fat') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th width="100">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($foodGroup->foods as $food)
                                        <tr>
                                            <td>
                                                <strong>{{ $food->translated_name }}</strong>
                                                @if($food->is_custom)
                                                    <span class="badge badge-success badge-sm ml-1">{{ __('Custom') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format($food->calories, 1) }}</td>
                                            <td>{{ number_format($food->protein, 1) }}g</td>
                                            <td>{{ number_format($food->carbohydrates, 1) }}g</td>
                                            <td>{{ number_format($food->fat, 1) }}g</td>
                                            <td>
                                                @if($food->is_active)
                                                    <span class="badge badge-success">{{ __('Active') }}</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ __('Inactive') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('foods.show', $food) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="{{ __('View Food') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @can('manage-food-composition')
                                                    @if($food->is_custom)
                                                        <a href="{{ route('foods.edit', $food) }}" 
                                                           class="btn btn-sm btn-outline-warning" 
                                                           title="{{ __('Edit Food') }}">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @can('manage-food-composition')
                            <div class="mt-3">
                                <a href="{{ route('foods.create', ['food_group_id' => $foodGroup->id]) }}" 
                                   class="btn btn-primary">
                                    <i class="fas fa-plus"></i> {{ __('Add Food to this Group') }}
                                </a>
                            </div>
                        @endcan
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-apple-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No Foods in this Group') }}</h5>
                            <p class="text-muted">{{ __('This food group doesn\'t contain any foods yet.') }}</p>
                            @can('manage-food-composition')
                                <a href="{{ route('foods.create', ['food_group_id' => $foodGroup->id]) }}" 
                                   class="btn btn-primary">
                                    <i class="fas fa-plus"></i> {{ __('Add First Food') }}
                                </a>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
