@extends('layouts.app')

@section('title', 'Nuskha Templates — Salman Dawa Khana')

@section('content')
<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        {{-- NUSKHA TEMPLATES — Heading --}}
        <header class="page-header">
            <div>
                <p class="dashboard-date">Prescription library</p>
                <h1>Nuskha Templates</h1>
                <p class="page-subtitle">
                    Save and reuse commonly prescribed nuskhas.
                </p>
            </div>

            <a
                href="{{ route('prescriptions.templates.create') }}"
                class="btn btn-primary"
            >
                + Create Template
            </a>
        </header>

        @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        {{-- NUSKHA TEMPLATES — Server Filters --}}
        <form
            action="{{ route('prescriptions.templates.index') }}"
            method="GET"
            class="templates-toolbar"
            data-server-filters
        >
            <div class="template-search">
                <span aria-hidden="true">⌕</span>
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    maxlength="160"
                    placeholder="Search by name or ingredient"
                    aria-label="Search nuskha templates"
                >
            </div>

            <select
                name="status"
                class="form-select template-category-select"
                aria-label="Template status"
            >
                @foreach ([
                    'all' => 'All statuses',
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                ] as $value => $label)
                    <option
                        value="{{ $value }}"
                        @selected(request('status', 'all') === $value)
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary">
                Apply
            </button>

            <a
                href="{{ route('prescriptions.templates.index') }}"
                class="btn btn-light"
            >
                Reset
            </a>
        </form>

        {{-- NUSKHA TEMPLATES — Saved Records --}}
        <section class="template-card-grid">
            @forelse ($templates as $template)
                <article class="nuskha-template-card">
                    <div class="template-card-top">
                        <div class="template-icon" aria-hidden="true">✦</div>

                        <span class="badge {{ $template->is_active
                            ? 'bg-success-subtle text-success'
                            : 'bg-secondary-subtle text-secondary' }}">
                            {{ $template->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <h2>{{ $template->name }}</h2>

                    <p class="template-description">
                        {{ \Illuminate\Support\Str::limit(
                            $template->instructions ?: 'No general instructions added.',
                            140
                        ) }}
                    </p>

                    <div class="template-meta">
                        <span>
                            {{ $template->items_count }}
                            {{ $template->items_count === 1 ? 'ingredient' : 'ingredients' }}
                        </span>
                        <span>Template #{{ $template->nuskha_template_id }}</span>
                    </div>

                    <div class="template-card-actions">
                        <a
                            href="{{ route('prescriptions.templates.edit', $template) }}"
                            class="template-action"
                        >
                            View / edit
                        </a>

                        @if ($template->is_active && $template->items_count > 0)
                            <a
                                href="{{ route('prescriptions.create', [
                                    'template_id' => $template->nuskha_template_id,
                                ]) }}"
                                class="btn btn-primary btn-sm"
                            >
                                Use template
                            </a>
                        @else
                            <span class="small text-muted">
                                Not available for use
                            </span>
                        @endif
                    </div>
                </article>
            @empty
                <div class="surface-card p-4 template-empty-state">
                    <h2 class="h5">No templates found</h2>
                    <p class="text-muted mb-0">
                        Create a nuskha template or change your search filters.
                    </p>
                </div>
            @endforelse
        </section>

        @if ($templates->hasPages())
            <div class="mt-4">
                {{ $templates->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </main>
</div>
@endsection