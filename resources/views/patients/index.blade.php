@extends('layouts.app')

@section('title', 'Patients')

@section('content')
<div class="page-header">
    <div>
        <span class="eyebrow">PATIENT MANAGEMENT</span>
        <h1>Patients</h1>
        <p>Manage your patient records and visit history.</p>
    </div>

    <a href="{{ route('patients.create') }}" class="btn btn-primary">
        + Add Patient
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        Please correct the highlighted information.
    </div>
@endif

<div class="card patients-card">
    <div class="card-header">
        <form method="GET" action="{{ route('patients.index') }}" class="patients-toolbar">
            <div class="search-box">
                <span>⌕</span>

                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by patient name, phone or ID"
                >
            </div>

            <select name="status" class="form-select">
                <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>
                    All Statuses
                </option>

                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                    Active
                </option>

                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                    Inactive
                </option>
            </select>

            <button type="submit" class="btn btn-outline-primary">
                Filter
            </button>

            <a href="{{ route('patients.index') }}" class="btn btn-light">
                Clear
            </a>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table patients-table">
            <thead>
                <tr>
                    <th>Patient ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Gender</th>
                    <th>Last Visit</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($patients as $patient)
                    <tr>
                        <td>
                            <strong>{{ $patient->patient_id }}</strong>
                        </td>

                        <td>
                            <div class="patient-name">
                                <span class="avatar-circle">
                                    {{ strtoupper(substr($patient->full_name, 0, 1)) }}
                                </span>

                                <span>{{ $patient->full_name }}</span>
                            </div>
                        </td>

                        <td>{{ $patient->phone ?? '—' }}</td>

                        <td>{{ $patient->gender ?? '—' }}</td>

                        <td>
                            {{ optional($patient->updated_at)->format('d M Y') ?? '—' }}
                        </td>

                        <td>
                            @if($patient->is_active)
                                <span class="status-badge status-active">Active</span>
                            @else
                                <span class="status-badge status-inactive">Inactive</span>
                            @endif
                        </td>

                        <td>
                            <a
                                href="{{ route('patients.show', $patient) }}"
                                class="btn btn-sm btn-outline-primary"
                            >
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">
                            No patients found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($patients->hasPages())
        <div class="card-footer">
            {{ $patients->links() }}
        </div>
    @endif
</div>
@endsection