<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $clinicId = Auth::user()->clinic_id;

        $query = Expense::where('clinic_id', $clinicId);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('expense_id', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (
            $request->filled('category') &&
            $request->category !== 'all'
        ) {
            $query->where('category', $request->category);
        }

        $dateRange = strtolower(
            $request->get('date_range', 'all time')
        );

        if ($dateRange === 'today') {
            $query->whereDate('expense_date', Carbon::today());
        }

        if ($dateRange === 'this week') {
            $query->whereBetween('expense_date', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ]);
        }

        if ($dateRange === 'this month') {
            $query->whereBetween('expense_date', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ]);
        }

        $expenses = $query
            ->latest('expense_date')
            ->paginate(10)
            ->withQueryString();

        $categories = Expense::where(
            'clinic_id',
            $clinicId
        )
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $todayQuery = Expense::where(
            'clinic_id',
            $clinicId
        )->whereDate('expense_date', Carbon::today());

        $todayExpenses = $todayQuery->sum('amount');
        $todayExpenseCount = $todayQuery->count();

        $monthExpenses = Expense::where(
            'clinic_id',
            $clinicId
        )
            ->whereBetween('expense_date', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
            ->sum('amount');

        $highestCategory = Expense::where(
            'clinic_id',
            $clinicId
        )
            ->whereBetween('expense_date', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
            ->selectRaw('category, SUM(amount) as total_amount')
            ->groupBy('category')
            ->orderByDesc('total_amount')
            ->first();

        $highestExpenseCategory = $highestCategory?->category;
        $highestCategoryAmount = $highestCategory?->total_amount ?? 0;

        $monthIncome = Payment::where(
            'clinic_id',
            $clinicId
        )
            ->whereBetween('payment_date', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
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

    public function create()
    {
        return view('expenses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_date' => [
                'required',
                'date',
            ],

            'category' => [
                'required',
                'string',
                'max:80',
            ],

            'description' => [
                'nullable',
                'string',
                'max:255',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0',
            ],

            'payment_method' => [
                'required',
                'string',
                'max:20',
            ],
        ]);

        $validated['clinic_id'] = Auth::user()->clinic_id;
        $validated['recorded_by_user_id'] = Auth::id();

        Expense::create($validated);

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense created successfully.');
    }

    public function show(Expense $expense)
    {
        $this->ensureClinicAccess($expense);

        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $this->ensureClinicAccess($expense);

        return view('expenses.create', compact('expense'));
    }

    public function update(Request $request, Expense $expense)
    {
        $this->ensureClinicAccess($expense);

        $validated = $request->validate([
            'expense_date' => [
                'required',
                'date',
            ],

            'category' => [
                'required',
                'string',
                'max:80',
            ],

            'description' => [
                'nullable',
                'string',
                'max:255',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0',
            ],

            'payment_method' => [
                'required',
                'string',
                'max:20',
            ],
        ]);

        $expense->update($validated);

        return redirect()
            ->route('expenses.show', $expense)
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

    private function ensureClinicAccess(Expense $expense)
    {
        abort_if(
            $expense->clinic_id !== Auth::user()->clinic_id,
            403
        );
    }
}