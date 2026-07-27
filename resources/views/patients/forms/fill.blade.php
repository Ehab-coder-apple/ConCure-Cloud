@extends('layouts.app')

@section('page-title', __('Fill Form') . ' - ' . $patient->full_name)

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">
                <i class="fas fa-pen text-primary"></i>
                {{ __('Fill Form') }} — {{ $assignment->template?->name ?? __('Form') }}
            </h1>
            <div class="d-flex gap-2">
                <a href="{{ route('patients.forms.show', [$patient, $assignment]) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> {{ __('Cancel') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('patients.forms.fill.submit', [$patient, $assignment]) }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="content_editable" class="form-label fw-bold">{{ __('Form Content / Notes') }}</label>
                            <div id="content_editable" class="form-control" contenteditable="true" style="min-height:420px; overflow:auto;">
                                {!! old('content', data_get($assignment->form_data, 'content', '')) !!}
                            </div>
                            <textarea name="content" id="content" class="d-none">{{ old('content', data_get($assignment->form_data, 'content', '')) }}</textarea>
                            <input type="hidden" name="content_html" id="content_html" value="{{ old('content', data_get($assignment->form_data, 'content', '')) }}">
                            @error('content')
                            <div class="text-danger small">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">{{ __('Tip: Paste from Word. Borders will be shown on review and in PDF.') }}</small>
                        </div>

                            <div class="mb-3">
                                <label for="attachment" class="form-label fw-bold">{{ __('Attach Completed Form (Optional)') }}</label>
                                <input type="file" class="form-control @error('attachment') is-invalid @enderror" id="attachment" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                <small class="text-muted">{{ __('Upload a scanned or filled document (PDF, Word, or Image). Max 10MB.') }}</small>
                                @error('attachment')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($assignment->hasAttachment())
                                    <div class="mt-2">
                                        <a href="{{ route('patients.forms.attachment', [$patient, $assignment]) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-paperclip me-1"></i> {{ __('Download current attachment') }}
                                        </a>
                                    </div>
                                @endif
                            </div>


                        <div class="d-flex gap-2">
                            <button type="submit" name="action" value="save" class="btn btn-info">
                                <i class="fas fa-save me-1"></i> {{ __('Save Progress') }}
                            </button>
                            <button type="submit" name="action" value="complete" class="btn btn-success">
                                <i class="fas fa-check me-1"></i> {{ __('Mark as Completed') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header fw-bold">
                    <i class="fas fa-info-circle me-1"></i> {{ __('Template & Tips') }}
                </div>
                <div class="card-body">
                    @if($assignment->template)
                        <p class="mb-2"><strong>{{ __('Template') }}:</strong> {{ $assignment->template->name }}</p>
                        <p class="mb-3">
                            <a href="{{ route('forms.templates.download', $assignment->template) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-download me-1"></i> {{ __('Download Original Template') }}
                            </a>
                        </p>
                    @endif
                    <ul class="mb-0 small text-muted">
                        <li>{{ __('Use Save Progress to keep working later.') }}</li>
                        <li>{{ __('Use Mark as Completed when the form is done and reviewed.') }}</li>
                        <li>{{ __('Completed forms become read-only.') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



@push('styles')
<style>
#content_editable table { border-collapse: collapse; width: 100%; }
#content_editable table, #content_editable th, #content_editable td { border: 1px solid #ced4da; }
#content_editable th, #content_editable td { padding: 6px 8px; }
#content_editable thead th { background: #f1f3f5; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.ckeditor.com/4.25.1/full-all/ckeditor.js"></script>
<script>
(function(){
    var editable = document.getElementById('content_editable');
    var hidden = document.getElementById('content');
    var hiddenHtml = document.getElementById('content_html');
    if (!editable || !hidden) return;

    function setHidden(html){
        hidden.value = html || '';
        if (hiddenHtml) hiddenHtml.value = html || '';
    }

    function getHtml(){
        if (window.CKEDITOR && CKEDITOR.instances && CKEDITOR.instances['content_editable']) {
            return CKEDITOR.instances['content_editable'].getData();
        }
        return editable.innerHTML;
    }

    function syncToHidden(){
        setHidden(getHtml());
    }

    // Keep hidden in sync while typing/pasting
    editable.addEventListener('input', syncToHidden);
    editable.addEventListener('paste', function(){ setTimeout(syncToHidden, 0); });

    if (window.CKEDITOR) {
        CKEDITOR.inline('content_editable', {
            removePlugins: 'resize',
            extraPlugins: 'table,tabletools,tableselection,pastefromword',
            toolbar: [
                { name: 'clipboard', items: ['Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'RemoveFormat'] },
                { name: 'paragraph', items: ['NumberedList','BulletedList','-','Outdent','Indent','-','JustifyLeft','JustifyCenter','JustifyRight'] },
                { name: 'insert', items: ['Table','HorizontalRule'] },
                { name: 'links', items: ['Link','Unlink'] },
                { name: 'styles', items: ['Format'] },
                { name: 'document', items: ['Source'] }
            ],
            allowedContent: true,
            pasteFromWordRemoveFontStyles: false,
            pasteFromWordRemoveStyles: false,
            on: {
                instanceReady: function(evt) {
                    evt.editor.addCss('table{border-collapse:collapse;width:100%} table,th,td{border:1px solid #ced4da;} th,td{padding:6px 8px;} thead th{background:#f1f3f5;}');
                    evt.editor.on('change', syncToHidden);
                }
            }
        });
    }

    // Initial sync
    syncToHidden();

    var formEl = document.querySelector('form');
    if (formEl) {
        formEl.addEventListener('submit', function(){
            syncToHidden();
        });
    }
})();
</script>
@endpush
