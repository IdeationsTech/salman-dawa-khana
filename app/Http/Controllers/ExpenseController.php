<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Expense;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | EXPENSES — Shared Category List
    |--------------------------------------------------------------------------
    */

    private const CATEGORIES = [
        'Utilities',
        'Rent',
        'Travel',
        'Staff',
        'Supplies',
        'Daily Expenses',
        'Tax',
        'Medicines',
        'Medicine Packaging',
        'Other',
    ];

    /*
    |--------------------------------------------------------------------------
    | EXPENSES — List and Filters
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $clinicId = (int) Auth::user()->clinic_id;

        if (
            is_string($request->input('category'))
            && $request->input('category') !== 'all'
        ) {
            $request->merge([
                'category' => $this->categoryLabel(
                    $request->input('category')
                ),
            ]);
        }

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:160'],
            'category' => [
                'nullable',
                Rule::in(['all', ...self::CATEGORIES]),
            ],
            'date_range' => [
                'nullable',
                Rule::in([
                    'all time',
                    'today',
                    'this week',
                    'this month',
                ]),
            ],
        ]);

        $query = Expense::query()->where('clinic_id', $clinicId);

        $search = trim($filters['search'] ?? '');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query
                    ->where('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");

                if (ctype_digit($search)) {
                    $query->orWhere('expense_id', (int) $search);
                }

                if (strtolower($search) === 'travel') {
                    $query->orWhereRaw(
                        'LOWER(TRIM(category)) = ?',
                        ['transport']
                    );
                }
            });
        }

        $category = $filters['category'] ?? 'all';

        if ($category !== 'all') {
            $values = match ($category) {
                'Travel' => ['travel', 'transport'],
                'Other' => ['other', 'others'],
                default => [strtolower($category)],
            };

            $placeholders = implode(',', array_fill(0, count($values), '?'));

            $query->whereRaw(
                "LOWER(TRIM(category)) IN ({$placeholders})",
                $values
            );
        }

        $today = Carbon::now('Asia/Dubai')->startOfDay();
        $monthStart = $today->copy()->startOfMonth();
        $nextMonth = $monthStart->copy()->addMonth();

        $dateRange = $filters['date_range'] ?? 'all time';

        if ($dateRange !== 'all time') {
            [$start, $end] = match ($dateRange) {
                'today' => [
                    $today->copy(),
                    $today->copy()->addDay(),
                ],
                'this week' => [
                    $today->copy()->startOfWeek(Carbon::MONDAY),
                    $today->copy()->startOfWeek(Carbon::MONDAY)->addWeek(),
                ],
                'this month' => [
                    $monthStart->copy(),
                    $nextMonth->copy(),
                ],
            };

            $query
                ->where('expense_date', '>=', $start->format('Y-m-d H:i:s'))
                ->where('expense_date', '<', $end->format('Y-m-d H:i:s'));
        }

        $expenses = $query
            ->orderByDesc('expense_date')
            ->orderByDesc('expense_id')
            ->paginate(100)
            ->withQueryString();

        // Display legacy Transport records as Travel.
        // This does not rewrite historical database rows.
        $expenses->getCollection()->each(function ($expense) {
            $expense->category = $this->categoryLabel($expense->category);
        });

        // All categories appear even before their first expense is entered.
        $categories = collect(self::CATEGORIES);

        $todayQuery = Expense::query()
            ->where('clinic_id', $clinicId)
            ->where('expense_date', '>=', $today->format('Y-m-d H:i:s'))
            ->where(
                'expense_date',
                '<',
                $today->copy()->addDay()->format('Y-m-d H:i:s')
            );

        $todayExpenses = (clone $todayQuery)->sum('amount');
        $todayExpenseCount = (clone $todayQuery)->count();

        $monthQuery = Expense::query()
            ->where('clinic_id', $clinicId)
            ->where('expense_date', '>=', $monthStart->format('Y-m-d H:i:s'))
            ->where('expense_date', '<', $nextMonth->format('Y-m-d H:i:s'));

        $monthExpenses = (clone $monthQuery)->sum('amount');

        $categoryTotals = [];

        foreach (
            (clone $monthQuery)
                ->selectRaw('category, SUM(amount) AS category_total')
                ->groupBy('category')
                ->get() as $row
        ) {
            $label = $this->categoryLabel($row->category);

            $categoryTotals[$label] =
                ($categoryTotals[$label] ?? 0)
                + (float) $row->category_total;
        }

        arsort($categoryTotals);

        $highestExpenseCategory = array_key_first($categoryTotals);
        $highestCategoryAmount = $highestExpenseCategory !== null
            ? $categoryTotals[$highestExpenseCategory]
            : 0;

        $monthIncome = Payment::query()
            ->where('clinic_id', $clinicId)
            ->where('payment_date', '>=', $monthStart->format('Y-m-d H:i:s'))
            ->where('payment_date', '<', $nextMonth->format('Y-m-d H:i:s'))
            ->sum('amount');

        $netIncome = $monthIncome - $monthExpenses;

        return view('expenses.index', compact(
            'expenses',
            'categories',
            'todayExpenses',
            'todayExpenseCount',
            'monthExpenses',
            'highestExpenseCategory',
            'highestCategoryAmount',
            'netIncome'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | EXPENSES — Create and Save
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('expenses.create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateExpense($request);

        Expense::create([
            ...$validated,
            'clinic_id' => Auth::user()->clinic_id,
            'recorded_by_user_id' => Auth::id(),
        ]);

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense recorded successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | EXPENSES — View and Edit
    |--------------------------------------------------------------------------
    */

    public function show(Expense $expense)
    {
        $this->ensureClinicAccess($expense);

        $expense->category = $this->categoryLabel($expense->category);

        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $this->ensureClinicAccess($expense);

        return view('expenses.create', [
            ...$this->formData(),
            'expense' => $expense,
        ]);
    }

    public function update(Request $request, Expense $expense)
    {
        $this->ensureClinicAccess($expense);

        $expense->update($this->validateExpense($request));

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $this->ensureClinicAccess($expense);

        $expense->delete();

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | EXPENSES — Required Fields
    |--------------------------------------------------------------------------
    */

    private function validateExpense(Request $request): array
    {
        return $request->validate([
            'description' => [
                'bail',
                'required',
                'string',
                'max:255',
            ],

            'category' => [
                'required',
                Rule::in(self::CATEGORIES),
            ],

            'expense_date' => [
                'bail',
                'required',
                'date_format:Y-m-d',
                'after_or_equal:1000-01-01',
                'before_or_equal:9999-12-31',
            ],

            'amount' => [
                'bail',
                'required',
                'numeric',
                'min:0.01',
                'max:9999999999.99',
                'regex:/^\d{1,10}(?:\.\d{1,2})?$/',
            ],

            'payment_method' => [
                'required',
                Rule::in(['cash', 'bank', 'card']),
            ],
        ], [
            'description.required' => 'Please enter the expense description.',
            'category.required' => 'Please select an expense category.',
            'category.in' => 'Please select a category from the list.',
            'expense_date.required' => 'Please enter the expense date.',
            'expense_date.date_format' => 'Please enter a valid expense date.',
            'amount.required' => 'Please enter the expense amount.',
            'amount.min' => 'The expense amount must be greater than zero.',
            'amount.regex' => 'Use no more than two decimal places.',
            'payment_method.required' => 'Please select the payment method.',
            'payment_method.in' => 'Please select Cash, Bank transfer or Card.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | EXPENSES — Shared Form Data and Helpers
    |--------------------------------------------------------------------------
    */

    private function formData(): array
    {
        $clinic = Clinic::findOrFail(Auth::user()->clinic_id);

        return [
            'categories' => self::CATEGORIES,
            'clinic' => $clinic,
            'currency' => strtoupper($clinic->currency ?: 'AED'),
        ];
    }

    private function categoryLabel(?string $category): string
    {
        $value = strtolower(trim($category ?? ''));

        if ($value === 'transport') {
            return 'Travel';
        }

        if ($value === 'others') {
            return 'Other';
        }

        foreach (self::CATEGORIES as $label) {
            if (strtolower($label) === $value) {
                return $label;
            }
        }

        return trim($category ?? '');
    }

    private function ensureClinicAccess(Expense $expense): void
    {
        abort_unless(
            (int) $expense->clinic_id === (int) Auth::user()->clinic_id,
            403
        );
    }
}