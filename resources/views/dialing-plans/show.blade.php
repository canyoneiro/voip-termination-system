@extends('layouts.app')

@section('title', 'Dialing Plan: ' . $dialingPlan->name)
@section('page-title', $dialingPlan->name)

@section('page-actions')
    <a href="{{ route('dialing-plans.edit', $dialingPlan) }}" class="btn btn-secondary">
        <i class="fas fa-edit me-1"></i> Edit Plan
    </a>
@endsection

@section('content')
<div class="row">
    <!-- Plan Details -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Plan Details</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5">Status</dt>
                    <dd class="col-7">
                        @if($dialingPlan->active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </dd>

                    <dt class="col-5">Default Action</dt>
                    <dd class="col-7">
                        <span class="badge bg-{{ $dialingPlan->default_action === 'allow' ? 'success' : 'danger' }}">
                            {{ strtoupper($dialingPlan->default_action) }}
                        </span>
                    </dd>

                    <dt class="col-5">Block Premium</dt>
                    <dd class="col-7">
                        @if($dialingPlan->block_premium)
                            <span class="badge bg-warning text-dark">Yes</span>
                        @else
                            <span class="badge bg-secondary">No</span>
                        @endif
                    </dd>

                    <dt class="col-5">Rules</dt>
                    <dd class="col-7">{{ $dialingPlan->rules->count() }}</dd>

                    <dt class="col-5">Customers</dt>
                    <dd class="col-7">{{ $dialingPlan->customers->count() }}</dd>
                </dl>

                @if($dialingPlan->description)
                    <hr>
                    <p class="text-muted mb-0 small">{{ $dialingPlan->description }}</p>
                @endif
            </div>
        </div>

        <!-- Test Number -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-vial me-2"></i>Test Number</h5>
            </div>
            <div class="card-body">
                <div class="input-group">
                    <input type="text" class="form-control" id="testNumber" placeholder="e.g., 34612345678">
                    <button class="btn btn-primary" type="button" id="btnTestNumber">
                        <i class="fas fa-check"></i> Test
                    </button>
                </div>
                <div id="testResult" class="mt-3" style="display: none;"></div>
            </div>
        </div>

        <!-- Assigned Customers -->
        @if($dialingPlan->customers->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Assigned Customers</h5>
            </div>
            <ul class="list-group list-group-flush">
                @foreach($dialingPlan->customers as $customer)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <a href="{{ route('customers.show', $customer) }}">{{ $customer->name }}</a>
                    @if($customer->active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    <!-- Rules -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Rules</h5>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="fas fa-file-import me-1"></i> Import
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addRuleModal">
                        <i class="fas fa-plus me-1"></i> Add Rule
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                @if($dialingPlan->rules->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-list-alt fa-3x text-muted mb-3"></i>
                        <h5>No Rules</h5>
                        <p class="text-muted">Add rules to control which destinations can be dialed.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRuleModal">
                            <i class="fas fa-plus me-1"></i> Add First Rule
                        </button>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th width="60">Priority</th>
                                    <th width="80">Type</th>
                                    <th>Pattern</th>
                                    <th>Description</th>
                                    <th width="80">Status</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dialingPlan->rules as $rule)
                                <tr class="{{ !$rule->active ? 'table-secondary' : '' }}">
                                    <td><span class="badge bg-secondary">{{ $rule->priority }}</span></td>
                                    <td>{!! $rule->type_badge !!}</td>
                                    <td><code>{{ $rule->pattern }}</code></td>
                                    <td class="text-muted">{{ $rule->description ?: '-' }}</td>
                                    <td>
                                        @if($rule->active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary"
                                                    onclick="editRule({{ $rule->id }}, '{{ $rule->type }}', '{{ $rule->pattern }}', '{{ $rule->description }}', {{ $rule->priority }}, {{ $rule->active ? 'true' : 'false' }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('dialing-plans.rules.destroy', [$dialingPlan, $rule]) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Delete this rule?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- How Rules Work -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>How Rules Work</h5>
            </div>
            <div class="card-body">
                <ol class="mb-0">
                    <li class="mb-2">
                        @if($dialingPlan->block_premium)
                            <strong>Premium check:</strong> Premium destinations are automatically blocked.
                        @else
                            <strong>Premium check:</strong> Premium destinations are allowed (not blocked).
                        @endif
                    </li>
                    <li class="mb-2">
                        <strong>Rules:</strong> Rules are evaluated in priority order (lowest first). First matching rule wins.
                    </li>
                    <li>
                        <strong>Default:</strong> If no rule matches, the default action is
                        <span class="badge bg-{{ $dialingPlan->default_action === 'allow' ? 'success' : 'danger' }}">
                            {{ strtoupper($dialingPlan->default_action) }}
                        </span>
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Add Rule Modal -->
<div class="modal fade" id="addRuleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('dialing-plans.rules.store', $dialingPlan) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Rule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="allow">Allow</option>
                            <option value="deny">Deny</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="pattern" class="form-label">Pattern</label>
                        <input type="text" class="form-control" id="pattern" name="pattern" required
                               placeholder="e.g., 34* or 1800*">
                        <div class="form-text">Use * as wildcard. Example: 346* matches all Spanish mobile.</div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description (optional)</label>
                        <input type="text" class="form-control" id="description" name="description"
                               placeholder="e.g., Spanish Mobile">
                    </div>
                    <div class="mb-3">
                        <label for="priority" class="form-label">Priority</label>
                        <input type="number" class="form-control" id="priority" name="priority"
                               value="100" min="1" max="9999">
                        <div class="form-text">Lower numbers are evaluated first.</div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="addActive" name="active" value="1" checked>
                        <label class="form-check-label" for="addActive">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Rule Modal -->
<div class="modal fade" id="editRuleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editRuleForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Rule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editType" class="form-label">Type</label>
                        <select class="form-select" id="editType" name="type" required>
                            <option value="allow">Allow</option>
                            <option value="deny">Deny</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editPattern" class="form-label">Pattern</label>
                        <input type="text" class="form-control" id="editPattern" name="pattern" required>
                    </div>
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Description</label>
                        <input type="text" class="form-control" id="editDescription" name="description">
                    </div>
                    <div class="mb-3">
                        <label for="editPriority" class="form-label">Priority</label>
                        <input type="number" class="form-control" id="editPriority" name="priority" min="1" max="9999">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="editActive" name="active" value="1">
                        <label class="form-check-label" for="editActive">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('dialing-plans.rules.import', $dialingPlan) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Rules</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="importType" class="form-label">Rule Type</label>
                        <select class="form-select" id="importType" name="type" required>
                            <option value="allow">Allow</option>
                            <option value="deny">Deny</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="patterns" class="form-label">Patterns (one per line)</label>
                        <textarea class="form-control" id="patterns" name="patterns" rows="10"
                                  placeholder="34*&#10;33*&#10;44*"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editRule(id, type, pattern, description, priority, active) {
    document.getElementById('editRuleForm').action = '{{ route("dialing-plans.rules.update", [$dialingPlan, ""]) }}/' + id;
    document.getElementById('editType').value = type;
    document.getElementById('editPattern').value = pattern;
    document.getElementById('editDescription').value = description;
    document.getElementById('editPriority').value = priority;
    document.getElementById('editActive').checked = active;
    new bootstrap.Modal(document.getElementById('editRuleModal')).show();
}

document.getElementById('btnTestNumber').addEventListener('click', function() {
    const number = document.getElementById('testNumber').value.trim();
    if (!number) return;

    fetch('{{ route("dialing-plans.test", $dialingPlan) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ number: number })
    })
    .then(response => response.json())
    .then(data => {
        const resultDiv = document.getElementById('testResult');
        resultDiv.style.display = 'block';

        let html = '';
        if (data.allowed) {
            html = '<div class="alert alert-success mb-0">';
            html += '<i class="fas fa-check-circle me-2"></i><strong>ALLOWED</strong><br>';
        } else {
            html = '<div class="alert alert-danger mb-0">';
            html += '<i class="fas fa-ban me-2"></i><strong>DENIED</strong><br>';
        }

        html += '<small>' + data.message + '</small>';

        if (data.prefix) {
            html += '<hr class="my-2">';
            html += '<small class="text-muted">Prefix: ' + data.prefix.prefix + ' (' + (data.prefix.country || 'Unknown') + ')';
            if (data.prefix.is_premium) html += ' <span class="badge bg-warning text-dark">Premium</span>';
            if (data.prefix.is_mobile) html += ' <span class="badge bg-info">Mobile</span>';
            html += '</small>';
        }

        html += '</div>';
        resultDiv.innerHTML = html;
    });
});
</script>
@endpush
