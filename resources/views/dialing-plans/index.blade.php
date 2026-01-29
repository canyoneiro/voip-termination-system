@extends('layouts.app')

@section('title', 'Dialing Plans')
@section('page-title', 'Dialing Plans')

@section('page-actions')
    <a href="{{ route('dialing-plans.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> New Dialing Plan
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        @if($dialingPlans->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-phone-slash fa-3x text-muted mb-3"></i>
                <h5>No Dialing Plans</h5>
                <p class="text-muted">Create your first dialing plan to restrict which destinations customers can dial.</p>
                <a href="{{ route('dialing-plans.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Create Dialing Plan
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Default Action</th>
                            <th>Block Premium</th>
                            <th>Rules</th>
                            <th>Customers</th>
                            <th>Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dialingPlans as $plan)
                        <tr>
                            <td>
                                <a href="{{ route('dialing-plans.show', $plan) }}" class="fw-bold text-decoration-none">
                                    {{ $plan->name }}
                                </a>
                                @if($plan->description)
                                    <br><small class="text-muted">{{ Str::limit($plan->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $plan->default_action === 'allow' ? 'success' : 'danger' }}">
                                    {{ strtoupper($plan->default_action) }}
                                </span>
                            </td>
                            <td>
                                @if($plan->block_premium)
                                    <span class="badge bg-warning text-dark">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                            <td>{{ $plan->rules_count }}</td>
                            <td>{{ $plan->customers_count }}</td>
                            <td>
                                @if($plan->active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('dialing-plans.show', $plan) }}" class="btn btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('dialing-plans.edit', $plan) }}" class="btn btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('dialing-plans.clone', $plan) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-info" title="Clone">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $dialingPlans->links() }}
            </div>
        @endif
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">About Dialing Plans</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <h6><i class="fas fa-check-circle text-success me-2"></i>Allow Rules</h6>
                <p class="text-muted small">Explicitly permit dialing to specific prefixes or patterns.</p>
            </div>
            <div class="col-md-4">
                <h6><i class="fas fa-ban text-danger me-2"></i>Deny Rules</h6>
                <p class="text-muted small">Block dialing to specific prefixes or patterns.</p>
            </div>
            <div class="col-md-4">
                <h6><i class="fas fa-sort-numeric-down text-info me-2"></i>Priority</h6>
                <p class="text-muted small">Lower priority numbers are evaluated first. First matching rule wins.</p>
            </div>
        </div>
        <hr>
        <h6>Pattern Examples:</h6>
        <ul class="small text-muted mb-0">
            <li><code>34</code> - Matches all numbers starting with 34 (Spain)</li>
            <li><code>346*</code> - Matches Spanish mobile (346xxxxxxx)</li>
            <li><code>1*</code> - Matches all USA/Canada numbers</li>
            <li><code>44</code> - Matches all UK numbers</li>
        </ul>
    </div>
</div>
@endsection
