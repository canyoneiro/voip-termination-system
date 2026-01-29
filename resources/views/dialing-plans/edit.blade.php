@extends('layouts.app')

@section('title', 'Edit Dialing Plan')
@section('page-title', 'Edit Dialing Plan: ' . $dialingPlan->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('dialing-plans.update', $dialingPlan) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $dialingPlan->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="2">{{ old('description', $dialingPlan->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="default_action" class="form-label">Default Action <span class="text-danger">*</span></label>
                            <select class="form-select @error('default_action') is-invalid @enderror"
                                    id="default_action" name="default_action" required>
                                <option value="allow" {{ old('default_action', $dialingPlan->default_action) === 'allow' ? 'selected' : '' }}>
                                    Allow (permit if no rule matches)
                                </option>
                                <option value="deny" {{ old('default_action', $dialingPlan->default_action) === 'deny' ? 'selected' : '' }}>
                                    Deny (block if no rule matches)
                                </option>
                            </select>
                            @error('default_action')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block">&nbsp;</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="block_premium"
                                       name="block_premium" value="1" {{ old('block_premium', $dialingPlan->block_premium) ? 'checked' : '' }}>
                                <label class="form-check-label" for="block_premium">
                                    Block Premium Destinations
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="active"
                                   name="active" value="1" {{ old('active', $dialingPlan->active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="active">Active</label>
                        </div>
                    </div>

                    @if($dialingPlan->customers()->exists())
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            This dialing plan is assigned to <strong>{{ $dialingPlan->customers()->count() }}</strong> customer(s).
                            Changes will affect them immediately.
                        </div>
                    @endif

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('dialing-plans.show', $dialingPlan) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Cancel
                        </a>
                        <div>
                            @if(!$dialingPlan->customers()->exists())
                                <button type="button" class="btn btn-danger me-2" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="fas fa-trash me-1"></i> Delete
                                </button>
                            @endif
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Dialing Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong>{{ $dialingPlan->name }}</strong>?</p>
                <p class="text-muted">This will also delete all rules in this plan. This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('dialing-plans.destroy', $dialingPlan) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
