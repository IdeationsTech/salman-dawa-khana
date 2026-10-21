@extends('layouts.app')

@section('title', __('Payments'))

@section('content')
<div class="app-shell">
    @include('layouts._sidebar')

    <main class="dashboard-main">
        <header class="page-header">
            <div>
                <p class="dashboard-date">{{ __('Clinic finances') }}</p>
                <h1>{{ __('Payments') }}</h1>
                <p class="page-subtitle">
                    {{ __('Patient receipts, nuskha order collections and refunds.') }}
                </p>
            </div>

            @clinicCan('payments.create')<a href="{{ route('payments.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle" aria-hidden="true"></i>
                {{ __('Record payment') }}
            </a>@endclinicCan
        </header>

        @if (session('success'))
            <div class="alert alert-success" role="status">
                {{ __(session('success')) }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ __($error) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($unconfirmedCurrencyCount > 0)
            <div class="alert alert-warning" role="status">
                {{ $unconfirmedCurrencyCount }} {{ __('patient payments have no confirmed currency and are excluded from summary totals.') }}
            </div>
        @endif

        <section class="payment-summary-grid">
            <article class="payment-summary-card">
                <span>{{ __('Today\'s patient receipts') }}</span>
                <strong>
                    {{ $currency }}
                    {{ number_format((float) $todaySummary['patient'], $moneyDecimals) }}
                </strong>
                <small>{{ __('Patient payments received today') }}</small>
            </article>

            <article class="payment-summary-card">
                <span>{{ __('Today\'s order receipts') }}</span>
                <strong>
                    {{ $currency }}
                    {{ number_format((float) $todaySummary['orders'], $moneyDecimals) }}
                </strong>
                <small>{{ __('Nuskha collections before refunds') }}</small>
            </article>

            <article class="payment-summary-card">
                <span>{{ __('Today\'s refunds') }}</span>
                <strong>
                    {{ $currency }}
                    {{ number_format((float) $todaySummary['refunds'], $moneyDecimals) }}
                </strong>
                <small class="stat-warning">{{ __('Deducted from received income') }}</small>
            </article>

            <article class="payment-summary-card">
                <span>{{ __('Today\'s net receipts') }}</span>
                <strong>
                    {{ $currency }}
                    {{ number_format((float) $todaySummary['net'], $moneyDecimals) }}
                </strong>
                <small class="stat-success">
                    Patient + order receipts − refunds
                </small>
            </article>
        </section>

        <section class="payment-summary-grid">
            <article class="payment-summary-card">
                <span>{{ __('This month\'s net receipts') }}</span>
                <strong>
                    {{ $currency }}
                    {{ number_format((float) $monthSummary['net'], $moneyDecimals) }}
                </strong>
                <small>{{ $monthLabel }} · After refunds</small>
            </article>

            <article class="payment-summary-card">
                <span>{{ __('All-time net receipts') }}</span>
                <strong>
                    {{ $currency }}
                    {{ number_format((float) $allTimeSummary['net'], $moneyDecimals) }}
                </strong>
                <small>{{ __('Patient and order receipts after refunds') }}</small>
            </article>

            <article class="payment-summary-card">
                <span>{{ __('Outstanding patient bills') }}</span>
                <strong>
                    {{ $currency }}
                    {{ number_format((float) $outstandingBalance, $moneyDecimals) }}
                </strong>
                <small class="stat-warning">
                    {{ __('Remaining patient bill balances') }}
                </small>
            </article>

            <article class="payment-summary-card">
                <span>{{ __('Unallocated patient receipts') }}</span>
                <strong>
                    {{ $currency }}
                    {{ number_format((float) $unallocatedReceived, $moneyDecimals) }}
                </strong>
                <small>{{ __('Included in receipts; not assigned to bills') }}</small>
            </article>
        </section>

        <p class="text-muted small">
            {{ __('Summary cards show this clinic\'s') }} {{ $currency }} {{ __('records. These are received amounts after refunds, before expenses. Filters below apply to the patient payment table.') }}
            <a href="{{ route('orders.index') }}">{{ __('View nuskha orders') }}</a>.
        </p>

        <section class="payments-panel">
            <div class="payments-toolbar">
                <div>
                    <h2>{{ __('Patient payment records') }}</h2>
                    <p>{{ __('Records retain their original currencies.') }}</p>
                </div>

                <div class="payments-toolbar-actions">
                    <select
                        name="method"
                        class="form-select"
                        form="payment-filter-form"
                        aria-label="{{ __('Payment method') }}"
                    >
                        @foreach ([
                            'all' => 'All methods',
                            'cash' => 'Cash',
                            'bank' => 'Bank transfer',
                            'card' => 'Card',
                        ] as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected($method === $value)
                            >
                                {{ __($label) }}
                            </option>
                        @endforeach
                    </select>

                    <select
                        name="date_range"
                        class="form-select"
                        form="payment-filter-form"
                        aria-label="{{ __('Payment date range') }}"
                    >
                        @foreach ([
                            'all time' => 'All time',
                            'today' => 'Today',
                            'this week' => 'This week',
                            'this month' => 'This month',
                        ] as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected($dateRange === $value)
                            >
                                {{ __($label) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <form
                method="GET"
                action="{{ route('payments.index') }}"
                id="payment-filter-form"
                data-server-payment-filters
            >
                <div class="payment-search">
                    <span>
                        <i class="bi bi-search" aria-hidden="true"></i>
                    </span>

                    <input
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        maxlength="160"
                        aria-label="{{ __('Search payments') }}"
                        placeholder="{{ __('Patient name, phone, patient code, payment ID or reference') }}"
                    >

                    <button type="submit" class="btn btn-primary btn-sm">
                        {{ __('Search') }}
                    </button>

                    <a
                        href="{{ route('payments.index') }}"
                        class="btn btn-light btn-sm"
                    >
                        {{ __('Clear') }}
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table payments-table align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('Payment ID') }}</th>
                            <th>{{ __('Patient') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Method') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Reference') }}</th>
                            <th class="text-end">{{ __('Action') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($payments as $payment)
                            @php
                                $rowMethod = strtolower(trim($payment->method));

                                $isBank = in_array(
                                    $rowMethod,
                                    ['bank', 'bank transfer', 'bank_transfer'],
                                    true
                                );

                                $methodLabel = $isBank
                                    ? 'Bank transfer'
                                    : ucfirst($rowMethod);

                                $methodClass = $isBank
                                    ? 'bank-method'
                                    : ($rowMethod === 'cash'
                                        ? 'cash-method'
                                        : 'card-method');
                            @endphp

                            <tr>
                                <td>PAY-{{ $payment->payment_id }}</td>

                                <td>
                                    <strong>
                                        {{ $payment->patient?->full_name ?? 'Patient unavailable' }}
                                    </strong>
                                    <small>
                                        {{ $payment->patient?->patient_code ?? '—' }}
                                    </small>
                                    <small>
                                        {{ $payment->patient?->phone ?? '—' }}
                                    </small>
                                </td>

                                <td>
                                    {{ $payment->payment_date->format('d M Y') }}
                                    <small>
                                        {{ $payment->payment_date->format('h:i A') }}
                                    </small>
                                </td>

                                <td>
                                    <span class="payment-method {{ $methodClass }}">
                                        {{ __($methodLabel) }}
                                    </span>

                                    @if (!$payment->bill_id)
                                        <small>{{ __('Unallocated') }}</small>
                                    @endif
                                </td>

                                <td class="payment-amount">
                                    @if ($payment->currency)
                                        {{ strtoupper($payment->currency) }}
                                        {{ number_format((float) $payment->amount, $moneyDecimals) }}
                                    @else
                                        {{ number_format((float) $payment->amount, $moneyDecimals) }}
                                        <small class="d-block text-muted">
                                            {{ __('Currency not recorded') }}
                                        </small>
                                    @endif
                                </td>

                                <td>{{ $payment->reference_no ?: '—' }}</td>

                                <td class="text-end">
                                    <a
                                        href="{{ route('payments.show', $payment) }}"
                                        class="table-action"
                                    >
                                        {{ __('View') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    {{ __('No patient payments match your filters.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="patients-pagination">
                <span>
                    {{ __('Showing') }} {{ $payments->firstItem() ?? 0 }}
                    {{ __('to') }} {{ $payments->lastItem() ?? 0 }}
                    {{ __('of') }} {{ $payments->total() }} {{ __('patient payments') }}
                </span>

                <div>{{ $payments->links() }}</div>
            </div>
        </section>
    </main>
</div>
@endsection