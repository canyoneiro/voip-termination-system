@extends('layouts.app')

@section('title', 'Create Dialing Plan')
@section('page-title', 'Create Dialing Plan')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('dialing-plans.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">A descriptive name for this dialing plan (e.g., "National Only", "No Premium")</div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="2">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="default_action" class="form-label">Default Action <span class="text-danger">*</span></label>
                            <select class="form-select @error('default_action') is-invalid @enderror"
                                    id="default_action" name="default_action" required>
                                <option value="allow" {{ old('default_action', 'allow') === 'allow' ? 'selected' : '' }}>
                                    Allow (permit if no rule matches)
                                </option>
                                <option value="deny" {{ old('default_action') === 'deny' ? 'selected' : '' }}>
                                    Deny (block if no rule matches)
                                </option>
                            </select>
                            @error('default_action')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">What happens when no rule matches the dialed number</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block">&nbsp;</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="block_premium"
                                       name="block_premium" value="1" {{ old('block_premium', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="block_premium">
                                    Block Premium Destinations
                                </label>
                            </div>
                            <div class="form-text">Automatically block destinations marked as premium</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="active"
                                   name="active" value="1" {{ old('active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="active">Active</label>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Next Step:</strong> After creating the dialing plan, you'll be able to add allow/deny rules.
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('dialing-plans.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Create Dialing Plan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
