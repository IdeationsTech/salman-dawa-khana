@extends('layouts.app')

@section('title', 'New Nuskha Order')

@section('content')
<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        <div class="form-page-header">
            <a href="{{ route('orders.index') }}" class="back-link">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                Back to orders
            </a>

            <h1>New nuskha order</h1>
            <p>Record a customer order without creating a patient or visit.</p>
        </div>

        @include('orders._form')
    </main>
</div>
@endsection