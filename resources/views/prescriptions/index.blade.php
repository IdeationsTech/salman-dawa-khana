@extends('layouts.app')

@section('title', 'Prescriptions')

@section('content')
<div class="page-header">
    <div>
        <span class="eyebrow">PATIENT CARE</span>
        <h1>Prescriptions</h1>
        <p>Manage patient prescriptions and saved nuskha records.</p>
    </div>

    <a href="{{ route('prescriptions.create') }}" class="btn btn-primary">
        + New Prescription
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="card prescriptions-card">
    <div class="card-header">
        <form method="GET" action="{{ route('prescriptions.index') }}" class="prescriptions-toolbar">
            <div class="search-box">
                <span>⌕</span>

                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search prescription or patient ID"
                >
            </div>

            <select name="status" class="form-select">
                <option value="all">All Statuses</option>

                <option
                    value="active"
                    {{ request('status') === 'active' ? 'selected' : '' }}
                >
                    Active
                </option>

                <option
                    value="completed"
                    {{ request('status') === 'completed' ? 'selected' : '' }}
                >
                    Completed
                </option>

                <option
                    value="cancelled"
                    {{ request('status') === 'cancelled' ? 'selected' : '' }}
                >
                    Cancelled
                </option>
            </select>

            <button type="submit" class="btn btn-outline-primary">
                Filter
            </button>

            <a href="{{ route('prescriptions.index') }}" class="btn btn-light">
                Clear
            </a>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table prescriptions-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Prescription ID</th>
                    <th>Patient ID</th>
                    <th>Template</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($prescriptions as $prescription)
                    <tr>
                        <td>
                            {{ \Carbon\Carbon::parse($prescription->prescription_date)->format('d M Y') }}
                        </td>

                        <td>
                            {{ $prescription->prescription_id }}
                        </td>

                        <td>
                            {{ $prescription->patient_id }}
                        </td>

                        <td>
                            {{ $prescription->nuskha_template_id ?? 'Custom Nuskha' }}
                        </td>

                        <td>
                            <span class="status-badge status-{{ strtolower($prescription->status) }}">
                                {{ ucfirst($prescription->status) }}
                            </span>
                        </td>

                        <td>
                            <a
                                href="{{ route('prescriptions.show', $prescription) }}"
                                class="btn btn-sm btn-outline-primary"
                            >
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            No prescriptions found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($prescriptions->hasPages())
        <div class="card-footer">
            {{ $prescriptions->links() }}
        </div>
    @endif
</div>
@endsection