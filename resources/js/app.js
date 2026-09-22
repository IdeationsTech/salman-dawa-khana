import 'bootstrap';

/*
|--------------------------------------------------------------------------
| GLOBAL FRONTEND INITIALIZATION
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', () => {
    initializePasswordToggles();
    initializeDismissibleAlerts();
    initializePrintButtons();
    initializeNuskhaItemRows();
    initializePrescriptionItemRows();
});

/*
|--------------------------------------------------------------------------
| AUTH — Password Visibility
|--------------------------------------------------------------------------
*/

function initializePasswordToggles() {
    const passwordInputs = document.querySelectorAll(
        'input[type="password"][data-password-toggle]'
    );

    passwordInputs.forEach((input) => {
        const toggleButton = document.querySelector(
            `[data-toggle-target="${input.id}"]`
        );

        if (!toggleButton) {
            return;
        }

        toggleButton.addEventListener('click', () => {
            const isPassword = input.type === 'password';

            input.type = isPassword ? 'text' : 'password';
            toggleButton.textContent = isPassword ? 'Hide' : 'Show';
        });
    });
}

/*
|--------------------------------------------------------------------------
| GLOBAL — Dismissible Alerts
|--------------------------------------------------------------------------
*/

function initializeDismissibleAlerts() {
    const alertButtons = document.querySelectorAll(
        '[data-dismiss-alert]'
    );

    alertButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const alert = button.closest('.alert');

            if (alert) {
                alert.remove();
            }
        });
    });
}

/*
|--------------------------------------------------------------------------
| GLOBAL — Print Buttons
|--------------------------------------------------------------------------
*/

function initializePrintButtons() {
    const printButtons = document.querySelectorAll(
        '[data-print-page]'
    );

    printButtons.forEach((button) => {
        button.addEventListener('click', () => {
            window.print();
        });
    });
}

/*
|--------------------------------------------------------------------------
| NUSKHA TEMPLATE — Add and Remove Items
|--------------------------------------------------------------------------
*/

function initializeNuskhaItemRows() {
    const itemList = document.querySelector('[data-item-list]');
    const addItemButton = document.querySelector('[data-add-item]');

    if (!itemList || !addItemButton) {
        return;
    }

    updateItemNumbers(itemList);

    addItemButton.addEventListener('click', () => {
        const firstRow = itemList.querySelector('.nuskha-item-row');

        if (!firstRow) {
            return;
        }

        const newRow = firstRow.cloneNode(true);

        newRow.querySelectorAll('input').forEach((input) => {
            input.value = '';
        });

        itemList.appendChild(newRow);
        updateItemNumbers(itemList);
    });

    itemList.addEventListener('click', (event) => {
        const removeButton = event.target.closest(
            '.remove-item-button'
        );

        if (!removeButton) {
            return;
        }

        const rows = itemList.querySelectorAll('.nuskha-item-row');

        if (rows.length === 1) {
            rows[0].querySelectorAll('input').forEach((input) => {
                input.value = '';
            });

            return;
        }

        removeButton.closest('.nuskha-item-row').remove();
        updateItemNumbers(itemList);
    });
}

/*
|--------------------------------------------------------------------------
| PRESCRIPTION — Add and Remove Items
|--------------------------------------------------------------------------
*/

function initializePrescriptionItemRows() {
    const itemList = document.querySelector(
        '[data-prescription-item-list]'
    );

    const addItemButton = document.querySelector(
        '[data-add-prescription-item]'
    );

    if (!itemList || !addItemButton) {
        return;
    }

    updateItemNumbers(itemList);

    addItemButton.addEventListener('click', () => {
        const firstRow = itemList.querySelector(
            '.prescription-item-row'
        );

        if (!firstRow) {
            return;
        }

        const newRow = firstRow.cloneNode(true);

        newRow.querySelectorAll('input').forEach((input) => {
            input.value = '';
        });

        itemList.appendChild(newRow);
        updateItemNumbers(itemList);
    });

    itemList.addEventListener('click', (event) => {
        const removeButton = event.target.closest(
            '[data-remove-prescription-item]'
        );

        if (!removeButton) {
            return;
        }

        const rows = itemList.querySelectorAll(
            '.prescription-item-row'
        );

        if (rows.length === 1) {
            rows[0].querySelectorAll('input').forEach((input) => {
                input.value = '';
            });

            return;
        }

        removeButton.closest('.prescription-item-row').remove();
        updateItemNumbers(itemList);
    });
}

/*
|--------------------------------------------------------------------------
| SHARED — Item Numbering
|--------------------------------------------------------------------------
*/

function updateItemNumbers(itemList) {
    const rows = itemList.querySelectorAll(
        '.nuskha-item-row, .prescription-item-row'
    );

    rows.forEach((row, index) => {
        const number = row.querySelector('.item-number');

        if (number) {
            number.textContent = index + 1;
        }
    });
}


/*
|--------------------------------------------------------------------------
| PAYMENTS — Remaining Balance Calculation
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', () => {
    initializePaymentCalculation();
});

function initializePaymentCalculation() {
    const totalElement = document.querySelector(
        '[data-total-payable]'
    );

    const amountInput = document.querySelector(
        '[data-payment-amount]'
    );

    const remainingElement = document.querySelector(
        '[data-remaining-balance]'
    );

    if (!totalElement || !amountInput || !remainingElement) {
        return;
    }

    const totalAmount = parseAmount(totalElement.textContent);

    amountInput.addEventListener('input', () => {
        const receivedAmount = Number(amountInput.value) || 0;
        const remainingAmount = Math.max(
            totalAmount - receivedAmount,
            0
        );

        remainingElement.textContent =
            `PKR ${formatAmount(remainingAmount)}`;
    });
}

function parseAmount(value) {
    return Number(
        value.replace(/[^0-9.-]+/g, '')
    ) || 0;
}

function formatAmount(value) {
    return value.toLocaleString('en-PK', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });
}

/*
|--------------------------------------------------------------------------
| PATIENTS — Search, Status Filter and Clear
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', () => {
    initializePatientSearch();
});

function initializePatientSearch() {
    const searchInput = document.querySelector(
        '.patient-search input'
    );

    const statusSelect = document.querySelector(
        '.patient-filters select'
    );

    const clearButton = document.querySelector(
        '.filter-clear'
    );

    const tableRows = document.querySelectorAll(
        '.patients-table tbody tr'
    );

    if (!searchInput || !statusSelect || tableRows.length === 0) {
        return;
    }

    searchInput.addEventListener(
        'input',
        applyPatientFilters
    );

    statusSelect.addEventListener(
        'change',
        applyPatientFilters
    );

    if (clearButton) {
        clearButton.addEventListener('click', () => {
            searchInput.value = '';
            statusSelect.selectedIndex = 0;

            applyPatientFilters();
        });
    }

    function applyPatientFilters() {
        const searchTerm = searchInput.value
            .trim()
            .toLowerCase();

        const selectedStatus = statusSelect.value
            .trim()
            .toLowerCase();

        tableRows.forEach((row) => {
            const rowText = row.textContent
                .trim()
                .toLowerCase();

            const statusElement = row.querySelector(
                '.status-badge'
            );

            const rowStatus = statusElement
                ? statusElement.textContent
                    .trim()
                    .toLowerCase()
                : '';

            const searchMatches =
                searchTerm === '' ||
                rowText.includes(searchTerm);

            const statusMatches =
                selectedStatus === 'all statuses' ||
                rowStatus === selectedStatus;

            row.style.display =
                searchMatches && statusMatches
                    ? ''
                    : 'none';
        });
    }
}


/*
|--------------------------------------------------------------------------
| TABLES — Generic Search
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', () => {
    initializeTableSearch(
        '.visit-search input',
        '.visits-table tbody tr'
    );

    initializeTableSearch(
        '.prescription-search input',
        '.prescriptions-table tbody tr'
    );

    initializeTableSearch(
        '.payment-search input',
        '.payments-table tbody tr'
    );

    initializeTableSearch(
        '.expense-search input',
        '.expenses-table tbody tr'
    );

});

function initializeTableSearch(inputSelector, rowSelector) {
    const searchInput = document.querySelector(inputSelector);
    const tableRows = document.querySelectorAll(rowSelector);

    if (!searchInput || tableRows.length === 0) {
        return;
    }

    searchInput.addEventListener('input', () => {
        const searchTerm = searchInput.value
            .trim()
            .toLowerCase();

        tableRows.forEach((row) => {
            const rowText = row.textContent.toLowerCase();

            row.style.display = rowText.includes(searchTerm)
                ? ''
                : 'none';
        });
    });
}

/*
|--------------------------------------------------------------------------
| EXPENSES — Category and Date Filters
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', () => {
    initializeExpenseFilters();
});

function initializeExpenseFilters() {
    const toolbar = document.querySelector(
        '.expenses-toolbar-actions'
    );

    const tableRows = document.querySelectorAll(
        '.expenses-table tbody tr'
    );

    if (!toolbar || tableRows.length === 0) {
        return;
    }

    const selects = toolbar.querySelectorAll('.form-select');

    const categorySelect = selects[0];
    const dateSelect = selects[1];

    if (!categorySelect || !dateSelect) {
        return;
    }

    categorySelect.addEventListener(
        'change',
        applyExpenseFilters
    );

    dateSelect.addEventListener(
        'change',
        applyExpenseFilters
    );

    function applyExpenseFilters() {
        const selectedCategory = categorySelect.value
            .trim()
            .toLowerCase();

        const selectedRange = dateSelect.value
            .trim()
            .toLowerCase();

        const today = new Date();

        tableRows.forEach((row) => {
            const categoryElement = row.querySelector(
                '.expense-category'
            );

            const dateCell = row.cells[3];

            if (!categoryElement || !dateCell) {
                row.style.display = 'none';
                return;
            }

            const rowCategory = categoryElement.textContent
                .trim()
                .toLowerCase();

            const categoryMatches =
                selectedCategory === 'all categories' ||
                rowCategory === selectedCategory;

            const dateMatch = dateCell.textContent
                .trim()
                .match(/\d{1,2}\s[A-Za-z]{3}\s\d{4}/);

            let dateMatches = true;

            if (dateMatch) {
                const rowDate = parseExpenseDate(dateMatch[0]);

                if (selectedRange === 'today') {
                    dateMatches =
                        rowDate.toDateString() ===
                        today.toDateString();
                }

                if (selectedRange === 'this week') {
                    const startOfWeek = new Date(today);

                    startOfWeek.setDate(
                        today.getDate() - today.getDay()
                    );

                    startOfWeek.setHours(0, 0, 0, 0);

                    const endOfWeek = new Date(startOfWeek);

                    endOfWeek.setDate(
                        startOfWeek.getDate() + 6
                    );

                    endOfWeek.setHours(23, 59, 59, 999);

                    dateMatches =
                        rowDate >= startOfWeek &&
                        rowDate <= endOfWeek;
                }

                if (selectedRange === 'this month') {
                    dateMatches =
                        rowDate.getMonth() === today.getMonth() &&
                        rowDate.getFullYear() === today.getFullYear();
                }

                if (selectedRange === 'all time') {
                    dateMatches = true;
                }
            }

            row.style.display =
                categoryMatches && dateMatches
                    ? ''
                    : 'none';
        });
    }
}

function parseExpenseDate(dateText) {
    const parts = dateText.split(' ');

    const day = Number(parts[0]);
    const month = parts[1];
    const year = Number(parts[2]);

    const monthIndex = {
        jan: 0,
        feb: 1,
        mar: 2,
        apr: 3,
        may: 4,
        jun: 5,
        jul: 6,
        aug: 7,
        sep: 8,
        oct: 9,
        nov: 10,
        dec: 11,
    };

    return new Date(
        year,
        monthIndex[month.toLowerCase()],
        day
    );
}

/*
|--------------------------------------------------------------------------
| VISITS — Status Filter
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', () => {
    initializeStatusFilter(
        '.visits-toolbar-actions .form-select:nth-child(2)',
        '.visits-table tbody tr',
        '.status-badge'
    );

    initializeStatusFilter(
        '.prescriptions-toolbar-actions .form-select:nth-child(2)',
        '.prescriptions-table tbody tr',
        '.status-badge'
    );
});

function initializeStatusFilter(
    selectSelector,
    rowSelector,
    statusSelector
) {
    const statusSelect = document.querySelector(selectSelector);
    const tableRows = document.querySelectorAll(rowSelector);

    if (!statusSelect || tableRows.length === 0) {
        return;
    }

    statusSelect.addEventListener('change', () => {
        const selectedStatus = statusSelect.value
            .trim()
            .toLowerCase();

        tableRows.forEach((row) => {
            const statusElement = row.querySelector(statusSelector);

            if (!statusElement) {
                return;
            }

            const rowStatus = statusElement.textContent
                .trim()
                .toLowerCase();

            const shouldShow =
                selectedStatus.startsWith('all') ||
                rowStatus === selectedStatus;

            row.style.display = shouldShow ? '' : 'none';
        });
    });
}


/*
|--------------------------------------------------------------------------
| VISITS — Date Filter
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', () => {
    initializeVisitDateFilter();
});

function initializeVisitDateFilter() {
    const dateSelect = document.querySelector(
        '.visits-toolbar-actions .form-select:first-child'
    );

    const tableRows = document.querySelectorAll(
        '.visits-table tbody tr'
    );

    if (!dateSelect || tableRows.length === 0) {
        return;
    }

    dateSelect.addEventListener('change', () => {
        const selectedRange = dateSelect.value
            .trim()
            .toLowerCase();

        const today = new Date();

        tableRows.forEach((row) => {
            const dateCell = row.cells[2];

            if (!dateCell) {
                return;
            }

            const dateMatch = dateCell.textContent
                .trim()
                .match(/\d{1,2}\s[A-Za-z]{3}\s\d{4}/);

            if (!dateMatch) {
                row.style.display = 'none';
                return;
            }

            const rowDate = parseVisitDate(dateMatch[0]);
            let shouldShow = true;

            if (selectedRange === 'all dates') {
                shouldShow = true;
            }

            if (selectedRange === 'today') {
                shouldShow =
                    rowDate.toDateString() ===
                    today.toDateString();
            }

            if (selectedRange === 'this week') {
                const startOfWeek = new Date(today);

                startOfWeek.setDate(
                    today.getDate() - today.getDay()
                );

                startOfWeek.setHours(0, 0, 0, 0);

                const endOfWeek = new Date(startOfWeek);

                endOfWeek.setDate(
                    startOfWeek.getDate() + 6
                );

                endOfWeek.setHours(23, 59, 59, 999);

                shouldShow =
                    rowDate >= startOfWeek &&
                    rowDate <= endOfWeek;
            }

            if (selectedRange === 'this month') {
                shouldShow =
                    rowDate.getMonth() === today.getMonth() &&
                    rowDate.getFullYear() === today.getFullYear();
            }

            row.style.display = shouldShow ? '' : 'none';
        });
    });
}

function parseVisitDate(dateText) {
    const parts = dateText.split(' ');

    const day = Number(parts[0]);
    const month = parts[1];
    const year = Number(parts[2]);

    const monthIndex = {
        jan: 0,
        feb: 1,
        mar: 2,
        apr: 3,
        may: 4,
        jun: 5,
        jul: 6,
        aug: 7,
        sep: 8,
        oct: 9,
        nov: 10,
        dec: 11,
    };

    return new Date(
        year,
        monthIndex[month.toLowerCase()],
        day
    );
}

/*
|--------------------------------------------------------------------------
| PRESCRIPTIONS — Date Filter
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', () => {
    initializePrescriptionDateFilter();
});

function initializePrescriptionDateFilter() {
    const dateSelect = document.querySelector(
        '.prescriptions-toolbar-actions .form-select:first-child'
    );

    const tableRows = document.querySelectorAll(
        '.prescriptions-table tbody tr'
    );

    if (!dateSelect || tableRows.length === 0) {
        return;
    }

    dateSelect.addEventListener('change', () => {
        const selectedRange = dateSelect.value
            .trim()
            .toLowerCase();

        const today = new Date();

        tableRows.forEach((row) => {
            const dateCell = row.cells[2];

            if (!dateCell) {
                return;
            }

            const rowDate = new Date(
                dateCell.textContent.trim()
            );

            let shouldShow = true;

            if (selectedRange === 'all dates') {
                shouldShow = true;
            }

            if (selectedRange === 'today') {
                shouldShow =
                    rowDate.toDateString() === today.toDateString();
            }

            if (selectedRange === 'this week') {
                const startOfWeek = new Date(today);
                startOfWeek.setDate(
                    today.getDate() - today.getDay()
                );
                startOfWeek.setHours(0, 0, 0, 0);

                const endOfWeek = new Date(startOfWeek);
                endOfWeek.setDate(
                    startOfWeek.getDate() + 6
                );
                endOfWeek.setHours(23, 59, 59, 999);

                shouldShow =
                    rowDate >= startOfWeek &&
                    rowDate <= endOfWeek;
            }

            if (selectedRange === 'this month') {
                shouldShow =
                    rowDate.getMonth() === today.getMonth() &&
                    rowDate.getFullYear() === today.getFullYear();
            }

            row.style.display = shouldShow ? '' : 'none';
        });
    });
}

/*
|--------------------------------------------------------------------------
| PAYMENTS — Method and Date Filters
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', () => {
    initializePaymentFilters();
});

function initializePaymentFilters() {
    const toolbar = document.querySelector(
        '.payments-toolbar-actions'
    );

    const tableRows = document.querySelectorAll(
        '.payments-table tbody tr'
    );

    if (!toolbar || tableRows.length === 0) {
        return;
    }

    const selects = toolbar.querySelectorAll('.form-select');

    const methodSelect = selects[0];
    const dateSelect = selects[1];

    if (!methodSelect || !dateSelect) {
        return;
    }

    methodSelect.addEventListener(
        'change',
        applyPaymentFilters
    );

    dateSelect.addEventListener(
        'change',
        applyPaymentFilters
    );

    function applyPaymentFilters() {
        const selectedMethod = methodSelect.value
            .trim()
            .toLowerCase();

        const selectedRange = dateSelect.value
            .trim()
            .toLowerCase();

        const today = new Date();

        tableRows.forEach((row) => {
            const methodElement = row.querySelector(
                '.payment-method'
            );

            const dateCell = row.cells[2];

            if (!methodElement || !dateCell) {
                row.style.display = 'none';
                return;
            }

            const rowMethod = methodElement.textContent
                .trim()
                .toLowerCase();

            const normalizedMethod =
                rowMethod === 'bank'
                    ? 'bank transfer'
                    : rowMethod;

            const methodMatches =
                selectedMethod === 'all methods' ||
                normalizedMethod === selectedMethod;

            const dateMatch = dateCell.textContent
                .trim()
                .match(/\d{1,2}\s[A-Za-z]{3}\s\d{4}/);

            let dateMatches = true;

            if (dateMatch) {
                const rowDate = parsePaymentDate(
                    dateMatch[0]
                );

                if (selectedRange === 'today') {
                    dateMatches =
                        rowDate.toDateString() ===
                        today.toDateString();
                }

                if (selectedRange === 'this week') {
                    const startOfWeek = new Date(today);

                    startOfWeek.setDate(
                        today.getDate() - today.getDay()
                    );

                    startOfWeek.setHours(0, 0, 0, 0);

                    const endOfWeek = new Date(startOfWeek);

                    endOfWeek.setDate(
                        startOfWeek.getDate() + 6
                    );

                    endOfWeek.setHours(23, 59, 59, 999);

                    dateMatches =
                        rowDate >= startOfWeek &&
                        rowDate <= endOfWeek;
                }

                if (selectedRange === 'this month') {
                    dateMatches =
                        rowDate.getMonth() === today.getMonth() &&
                        rowDate.getFullYear() === today.getFullYear();
                }

                if (selectedRange === 'all time') {
                    dateMatches = true;
                }
            }

            row.style.display =
                methodMatches && dateMatches
                    ? ''
                    : 'none';
        });
    }
}

function parsePaymentDate(dateText) {
    const parts = dateText.split(' ');

    const day = Number(parts[0]);
    const month = parts[1];
    const year = Number(parts[2]);

    const monthIndex = {
        jan: 0,
        feb: 1,
        mar: 2,
        apr: 3,
        may: 4,
        jun: 5,
        jul: 6,
        aug: 7,
        sep: 8,
        oct: 9,
        nov: 10,
        dec: 11,
    };

    return new Date(
        year,
        monthIndex[month.toLowerCase()],
        day
    );
}

/*
|--------------------------------------------------------------------------
| REPORTS — Filters and Actions
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', () => {
    initializeReportFilters();
    initializeReportActions();
});

function initializeReportFilters() {
    const filterPanel = document.querySelector(
        '.report-filter-panel'
    );

    const applyButton = document.querySelector(
        '.report-apply-button'
    );

    const periodLabel = document.querySelector(
        '.report-period-label'
    );

    if (!filterPanel || !applyButton || !periodLabel) {
        return;
    }

    const reportTypeSelect = document.querySelector(
        '#report_type'
    );

    const fromDate = document.querySelector(
        '#from_date'
    );

    const toDate = document.querySelector(
        '#to_date'
    );

    applyButton.addEventListener('click', () => {
        const reportType = reportTypeSelect
            ? reportTypeSelect.value
            : 'Report';

        const fromValue = fromDate
            ? formatReportDate(fromDate.value)
            : '';

        const toValue = toDate
            ? formatReportDate(toDate.value)
            : '';

        periodLabel.textContent =
            `${reportType} · ${fromValue} to ${toValue}`;

        applyButton.textContent = 'Applied';

        setTimeout(() => {
            applyButton.textContent = 'Apply';
        }, 1500);
    });
}

function formatReportDate(dateValue) {
    if (!dateValue) {
        return '';
    }

    const date = new Date(`${dateValue}T00:00:00`);

    return date.toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}

function initializeReportActions() {
    const printButton = document.querySelector(
        '.report-header-actions .btn-outline-secondary'
    );

    const exportButton = document.querySelector(
        '.report-header-actions .btn-primary'
    );

    if (printButton) {
        printButton.addEventListener('click', () => {
            window.print();
        });
    }

    if (exportButton) {
        exportButton.addEventListener('click', () => {
            exportButton.textContent = 'Export ready';

            setTimeout(() => {
                exportButton.textContent = 'Export report';
            }, 1500);
        });
    }
}

/*
|--------------------------------------------------------------------------
| REPORTS — Date Range Presets
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', () => {
    initializeReportDateRange();
});

function initializeReportDateRange() {
    const rangeSelect = document.querySelector(
        '#date_range'
    );

    const fromDate = document.querySelector(
        '#from_date'
    );

    const toDate = document.querySelector(
        '#to_date'
    );

    if (!rangeSelect || !fromDate || !toDate) {
        return;
    }

    rangeSelect.addEventListener('change', () => {
        const selectedRange = rangeSelect.value
            .trim()
            .toLowerCase();

        const today = new Date();

        if (selectedRange === 'today') {
            const todayValue = formatInputDate(today);

            fromDate.value = todayValue;
            toDate.value = todayValue;
        }

        if (selectedRange === 'this week') {
            const startOfWeek = new Date(today);

            startOfWeek.setDate(
                today.getDate() - today.getDay()
            );

            const endOfWeek = new Date(startOfWeek);

            endOfWeek.setDate(
                startOfWeek.getDate() + 6
            );

            fromDate.value = formatInputDate(startOfWeek);
            toDate.value = formatInputDate(endOfWeek);
        }

        if (selectedRange === 'this month') {
            const startOfMonth = new Date(
                today.getFullYear(),
                today.getMonth(),
                1
            );

            const endOfMonth = new Date(
                today.getFullYear(),
                today.getMonth() + 1,
                0
            );

            fromDate.value = formatInputDate(startOfMonth);
            toDate.value = formatInputDate(endOfMonth);
        }

        if (selectedRange === 'last month') {
            const startOfLastMonth = new Date(
                today.getFullYear(),
                today.getMonth() - 1,
                1
            );

            const endOfLastMonth = new Date(
                today.getFullYear(),
                today.getMonth(),
                0
            );

            fromDate.value = formatInputDate(
                startOfLastMonth
            );

            toDate.value = formatInputDate(
                endOfLastMonth
            );
        }
    });
}

function formatInputDate(date) {
    const year = date.getFullYear();

    const month = String(
        date.getMonth() + 1
    ).padStart(2, '0');

    const day = String(
        date.getDate()
    ).padStart(2, '0');

    return `${year}-${month}-${day}`;
}


/*
|--------------------------------------------------------------------------
| USERS — Search, Role and Status Filters
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', () => {
    initializeUserFilters();
});

function initializeUserFilters() {
    const searchInput = document.querySelector(
        '.user-search input'
    );

    const selects = document.querySelectorAll(
        '.users-toolbar-actions .form-select'
    );

    const tableRows = document.querySelectorAll(
        '.users-table tbody tr'
    );

    if (
        !searchInput ||
        selects.length < 2 ||
        tableRows.length === 0
    ) {
        return;
    }

    const roleSelect = selects[0];
    const statusSelect = selects[1];

    searchInput.addEventListener(
        'input',
        applyUserFilters
    );

    roleSelect.addEventListener(
        'change',
        applyUserFilters
    );

    statusSelect.addEventListener(
        'change',
        applyUserFilters
    );

    function applyUserFilters() {
        const searchTerm = searchInput.value
            .trim()
            .toLowerCase();

        const selectedRole = roleSelect.value
            .trim()
            .toLowerCase();

        const selectedStatus = statusSelect.value
            .trim()
            .toLowerCase();

        tableRows.forEach((row) => {
            const rowText = row.textContent
                .trim()
                .toLowerCase();

            const roleElement = row.querySelector(
                '.role-badge'
            );

            const statusElement = row.querySelector(
                '.status-badge'
            );

            const rowRole = roleElement
                ? roleElement.textContent
                    .trim()
                    .toLowerCase()
                : '';

            const rowStatus = statusElement
                ? statusElement.textContent
                    .trim()
                    .toLowerCase()
                : '';

            const searchMatches =
                searchTerm === '' ||
                rowText.includes(searchTerm);

            const roleMatches =
                selectedRole === 'all roles' ||
                rowRole === selectedRole;

            const statusMatches =
                selectedStatus === 'all statuses' ||
                rowStatus === selectedStatus;

            row.style.display =
                searchMatches &&
                roleMatches &&
                statusMatches
                    ? ''
                    : 'none';
        });
    }
}