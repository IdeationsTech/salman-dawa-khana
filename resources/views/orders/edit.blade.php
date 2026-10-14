@extends('layouts.app')

@section('title', 'Edit Order — ' . $order->order_no)

@section('content')
<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        <div class="form-page-header">
            <a href="{{ route('orders.show', $order) }}" class="back-link">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                Back to order
            </a>

            <h1>Edit {{ $order->order_no }}</h1>
            <p>
                Net received:
                {{ $order->currency }} {{ $paidAmount }}
            </p>
        </div>

        @include('orders._form')
    </main>
</div>
@endsection