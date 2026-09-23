import 'bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    initPasswordToggles();
    initAlerts();
    initPrintButtons();
    initItemRows();
    initPaymentCalculation();

    initPatientFilters();
    initVisitFilters();
    initPrescriptionFilters();
    initPaymentFilters();
    initExpenseFilters();
    initUserFilters();
    initTableSearch();
    initReportActions();
});

/*
|--------------------------------------------------------------------------
| Global
|--------------------------------------------------------------------------
*/

function initPasswordToggles() {
    document
        .querySelectorAll('input[type="password"][data-password-toggle]')
        .forEach((input) => {
            const button = document.querySelector(
                `[data-toggle-target="${input.id}"]`
            );

            if (!button) return;

            button.addEventListener('click', () => {
                const hidden = input.type === 'password';

                input.type = hidden ? 'text' : 'password';
                button.textContent = hidden ? 'Hide' : 'Show';
            });
        });
}

function initAlerts() {
    document
        .querySelectorAll('[data-dismiss-alert]')
        .forEach((button) => {
            button.addEventListener('click', () => {
                button.closest('.alert')?.remove();
            });
        });
}

function initPrintButtons() {
    document
        .querySelectorAll('[data-print-page]')
        .forEach((button) => {
            button.addEventListener('click', () => {
                window.print();
            });
        });
}

/*
|--------------------------------------------------------------------------
| Nuskha / Prescription Item Rows
|--------------------------------------------------------------------------
*/

function initItemRows() {
    initRepeater(
        '[data-item-list]',
        '[data-add-item]',
        '.nuskha-item-row',
        '.remove-item-button'
    );

    initRepeater(
        '[data-prescription-item-list]',
        '[data-add-prescription-item]',
        '.prescription-item-row',
        '[data-remove-prescription-item]'
    );
}

function initRepeater(listSelector, addSelector, rowSelector, removeSelector) {
    const list = document.querySelector(listSelector);
    const addButton = document.querySelector(addSelector);

    if (!list || !addButton) return;

    updateItemRows(list, rowSelector);

    addButton.addEventListener('click', () => {
        const firstRow = list.querySelector(rowSelector);

        if (!firstRow) return;

        const newRow = firstRow.cloneNode(true);

        newRow.querySelectorAll('input, textarea, select')
            .forEach((field) => {
                field.value = '';
            });

        list.appendChild(newRow);
        updateItemRows(list, rowSelector);
    });

    list.addEventListener('click', (event) => {
        const removeButton = event.target.closest(removeSelector);

        if (!removeButton) return;

        const rows = list.querySelectorAll(rowSelector);

        if (rows.length === 1) {
            rows[0]
                .querySelectorAll('input, textarea, select')
                .forEach((field) => {
                    field.value = '';
                });

            return;
        }

        removeButton.closest(rowSelector)?.remove();
        updateItemRows(list, rowSelector);
    });
}

function updateItemRows(list, rowSelector) {
    list.querySelectorAll(rowSelector).forEach((row, index) => {
        const number = row.querySelector('.item-number');

        if (number) {
            number.textContent = index + 1;
        }

        row.querySelectorAll('input, textarea, select')
            .forEach((field) => {
                const name = field.getAttribute('name');

                if (!name) return;

                field.setAttribute(
                    'name',
                    name.replace(
                        /items\[\d+\]/,
                        `items[${index}]`
                    )
                );
            });
    });
}

/*
|--------------------------------------------------------------------------
| Payment Amount
|--------------------------------------------------------------------------
*/

function initPaymentCalculation() {
    const total = document.querySelector('[data-total-payable]');
    const amount = document.querySelector('[data-payment-amount]');
    const remaining = document.querySelector('[data-remaining-balance]');

    if (!total || !amount || !remaining) return;

    const totalAmount = parseAmount(total.textContent);

    amount.addEventListener('input', () => {
        const received = Number(amount.value) || 0;
        const balance = Math.max(totalAmount - received, 0);

        remaining.textContent = `PKR ${formatAmount(balance)}`;
    });
}

/*
|--------------------------------------------------------------------------
| Patients
|--------------------------------------------------------------------------
*/

function initPatientFilters() {
    const search = document.querySelector('.patient-search input');
    const status = document.querySelector('.patient-filters select');
    const clear = document.querySelector('.filter-clear');
    const rows = document.querySelectorAll('.patients-table tbody tr');

    if (!search || !status || rows.length === 0) return;

    const apply = () => {
        const term = search.value.trim().toLowerCase();
        const selected = status.value.trim().toLowerCase();

        rows.forEach((row) => {
            const text = row.textContent.toLowerCase();
            const badge = row.querySelector('.status-badge');

            const rowStatus = badge
                ? badge.textContent.trim().toLowerCase()
                : '';

            const searchMatch =
                !term || text.includes(term);

            const statusMatch =
                selected === 'all' ||
                selected === 'all statuses' ||
                rowStatus === selected;

            row.style.display =
                searchMatch && statusMatch ? '' : 'none';
        });
    };

    search.addEventListener('input', apply);
    status.addEventListener('change', apply);

    clear?.addEventListener('click', () => {
        search.value = '';
        status.value = 'all';
        apply();
    });
}

/*
|--------------------------------------------------------------------------
| Visits
|--------------------------------------------------------------------------
*/

function initVisitFilters() {
    initDateAndSearch(
        '.visits-toolbar-actions .form-select:first-child',
        '.visit-search input',
        '.visits-table tbody tr',
        2
    );
}

/*
|--------------------------------------------------------------------------
| Prescriptions
|--------------------------------------------------------------------------
*/

function initPrescriptionFilters() {
    const date = document.querySelector(
        '.prescriptions-toolbar-actions .form-select:first-child'
    );

    const status = document.querySelector(
        '.prescriptions-toolbar-actions .form-select:nth-child(2)'
    );

    const search = document.querySelector(
        '.prescription-search input'
    );

    const rows = document.querySelectorAll(
        '.prescriptions-table tbody tr'
    );

    if (rows.length === 0) return;

    const apply = () => {
        const term = search?.value.trim().toLowerCase() || '';
        const range = date?.value.trim().toLowerCase() || 'all dates';
        const selectedStatus =
            status?.value.trim().toLowerCase() || 'all';

        const today = new Date();

        rows.forEach((row) => {
            const text = row.textContent.toLowerCase();
            const badge = row.querySelector('.status-badge');

            const rowStatus = badge
                ? badge.textContent.trim().toLowerCase()
                : '';

            const date = parseTableDate(row.cells[2]?.textContent);
            const searchMatch = !term || text.includes(term);

            const statusMatch =
                selectedStatus === 'all' ||
                selectedStatus === 'all statuses' ||
                rowStatus === selectedStatus;

            const dateMatch = matchDateRange(date, range, today);

            row.style.display =
                searchMatch && statusMatch && dateMatch
                    ? ''
                    : 'none';
        });
    };

    search?.addEventListener('input', apply);
    date?.addEventListener('change', apply);
    status?.addEventListener('change', apply);
}

/*
|--------------------------------------------------------------------------
| Payments — method, reference_no, payment_date
|--------------------------------------------------------------------------
*/

function initPaymentFilters() {
    const method = document.querySelector(
        '.payments-toolbar-actions .form-select:first-child'
    );

    const date = document.querySelector(
        '.payments-toolbar-actions .form-select:nth-child(2)'
    );

    const search = document.querySelector(
        '.payment-search input'
    );

    const rows = document.querySelectorAll(
        '.payments-table tbody tr'
    );

    if (rows.length === 0) return;

    const apply = () => {
        const selectedMethod =
            method?.value.trim().toLowerCase() || 'all';

        const selectedRange =
            date?.value.trim().toLowerCase() || 'all time';

        const term = search?.value.trim().toLowerCase() || '';
        const today = new Date();

        rows.forEach((row) => {
            const text = row.textContent.toLowerCase();
            const methodElement = row.querySelector(
                '.payment-method'
            );

            const rowMethod = methodElement
                ? methodElement.textContent.trim().toLowerCase()
                : '';

            const rowDate = parseTableDate(row.cells[2]?.textContent);

            const searchMatch = !term || text.includes(term);

            const methodMatch =
                selectedMethod === 'all' ||
                selectedMethod === 'all methods' ||
                rowMethod === selectedMethod;

            const dateMatch = matchDateRange(
                rowDate,
                selectedRange,
                today
            );

            row.style.display =
                searchMatch && methodMatch && dateMatch
                    ? ''
                    : 'none';
        });
    };

    method?.addEventListener('change', apply);
    date?.addEventListener('change', apply);
    search?.addEventListener('input', apply);
}

/*
|--------------------------------------------------------------------------
| Expenses — category, date range
|--------------------------------------------------------------------------
*/

function initExpenseFilters() {
    const category = document.querySelector(
        '.expenses-toolbar-actions .form-select:first-child'
    );

    const date = document.querySelector(
        '.expenses-toolbar-actions .form-select:nth-child(2)'
    );

    const search = document.querySelector(
        '.expense-search input'
    );

    const rows = document.querySelectorAll(
        '.expenses-table tbody tr'
    );

    if (rows.length === 0) return;

    const apply = () => {
        const selectedCategory =
            category?.value.trim().toLowerCase() || 'all';

        const selectedRange =
            date?.value.trim().toLowerCase() || 'all time';

        const term = search?.value.trim().toLowerCase() || '';
        const today = new Date();

        rows.forEach((row) => {
            const text = row.textContent.toLowerCase();
            const categoryElement = row.querySelector(
                '.expense-category'
            );

            const rowCategory = categoryElement
                ? categoryElement.textContent.trim().toLowerCase()
                : '';

            const rowDate = parseTableDate(row.cells[3]?.textContent);

            const searchMatch = !term || text.includes(term);

            const categoryMatch =
                selectedCategory === 'all' ||
                selectedCategory === 'all categories' ||
                rowCategory === selectedCategory;

            const dateMatch = matchDateRange(
                rowDate,
                selectedRange,
                today
            );

            row.style.display =
                searchMatch && categoryMatch && dateMatch
                    ? ''
                    : 'none';
        });
    };

    category?.addEventListener('change', apply);
    date?.addEventListener('change', apply);
    search?.addEventListener('input', apply);
}

/*
|--------------------------------------------------------------------------
| Users
|--------------------------------------------------------------------------
*/

function initUserFilters() {
    const search = document.querySelector('.user-search input');
    const status = document.querySelector(
        '.users-toolbar-actions .form-select'
    );

    const rows = document.querySelectorAll(
        '.users-table tbody tr'
    );

    if (!search || !status || rows.length === 0) return;

    const apply = () => {
        const term = search.value.trim().toLowerCase();
        const selected = status.value.trim().toLowerCase();

        rows.forEach((row) => {
            const text = row.textContent.toLowerCase();
            const badge = row.querySelector('.status-badge');

            const rowStatus = badge
                ? badge.textContent.trim().toLowerCase()
                : '';

            const searchMatch =
                !term || text.includes(term);

            const statusMatch =
                selected === 'all' ||
                selected === 'all statuses' ||
                rowStatus === selected;

            row.style.display =
                searchMatch && statusMatch ? '' : 'none';
        });
    };

    search.addEventListener('input', apply);
    status.addEventListener('change', apply);
}

/*
|--------------------------------------------------------------------------
| Generic Table Search
|--------------------------------------------------------------------------
*/

function initTableSearch() {
    document
        .querySelectorAll(
            '.visit-search input, ' +
            '.prescription-search input, ' +
            '.payment-search input, ' +
            '.expense-search input'
        )
        .forEach((input) => {
            input.addEventListener('input', () => {
                const table = input
                    .closest('main')
                    ?.querySelector('tbody');

                if (!table) return;

                const term = input.value.trim().toLowerCase();

                table.querySelectorAll('tr').forEach((row) => {
                    row.style.display =
                        row.textContent
                            .toLowerCase()
                            .includes(term)
                            ? ''
                            : 'none';
                });
            });
        });
}

/*
|--------------------------------------------------------------------------
| Reports
|--------------------------------------------------------------------------
*/

function initReportActions() {
    document
        .querySelectorAll('.report-apply-button')
        .forEach((button) => {
            button.addEventListener('click', () => {
                button.form?.submit();
            });
        });
}

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function initDateAndSearch(
    dateSelector,
    searchSelector,
    rowSelector,
    dateColumn
) {
    const date = document.querySelector(dateSelector);
    const search = document.querySelector(searchSelector);
    const rows = document.querySelectorAll(rowSelector);

    if (rows.length === 0) return;

    const apply = () => {
        const range = date?.value.trim().toLowerCase() || 'all';
        const term = search?.value.trim().toLowerCase() || '';
        const today = new Date();

        rows.forEach((row) => {
            const text = row.textContent.toLowerCase();
            const rowDate = parseTableDate(
                row.cells[dateColumn]?.textContent
            );

            const searchMatch = !term || text.includes(term);
            const dateMatch = matchDateRange(
                rowDate,
                range,
                today
            );

            row.style.display =
                searchMatch && dateMatch ? '' : 'none';
        });
    };

    date?.addEventListener('change', apply);
    search?.addEventListener('input', apply);
}

function parseTableDate(value = '') {
    const match = value.match(
        /\d{1,2}\s[A-Za-z]{3}\s\d{4}/
    );

    if (!match) return null;

    const parts = match[0].split(' ');
    const months = {
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
        Number(parts[2]),
        months[parts[1].toLowerCase()],
        Number(parts[0])
    );
}

function matchDateRange(date, range, today) {
    if (!date || range === 'all' || range === 'all dates') {
        return true;
    }

    if (range === 'today') {
        return date.toDateString() === today.toDateString();
    }

    if (range === 'this week') {
        const start = new Date(today);

        start.setDate(today.getDate() - today.getDay());
        start.setHours(0, 0, 0, 0);

        const end = new Date(start);
        end.setDate(start.getDate() + 6);
        end.setHours(23, 59, 59, 999);

        return date >= start && date <= end;
    }

    if (range === 'this month') {
        return (
            date.getMonth() === today.getMonth() &&
            date.getFullYear() === today.getFullYear()
        );
    }

    return true;
}

function parseAmount(value = '') {
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
| PATIENTS — Marital Status and Children Fields
|--------------------------------------------------------------------------
*/

function initializePatientFamilyFields() {
    document.querySelectorAll('[data-patient-family-form]').forEach((form) => {
        if (form.dataset.familyFieldsInitialized === 'true') {
            return;
        }

        const maritalStatus = form.querySelector('[name="marital_status"]');
        const childrenQuestion = form.querySelector('[data-children-question]');
        const childrenRadios = form.querySelectorAll('[name="has_children"]');
        const countField = form.querySelector('[data-children-count-field]');
        const countInput = form.querySelector('[name="children_count"]');

        if (
            !maritalStatus ||
            !childrenQuestion ||
            !childrenRadios.length ||
            !countField ||
            !countInput
        ) {
            return;
        }

        form.dataset.familyFieldsInitialized = 'true';

        function updateFamilyFields() {
            const isMarried = maritalStatus.value === 'married';

            childrenQuestion.classList.toggle('d-none', !isMarried);

            childrenRadios.forEach((radio) => {
                radio.disabled = !isMarried;
                radio.required = isMarried;
            });

            const selectedChildren = form.querySelector(
                '[name="has_children"]:checked'
            );

            const needsCount =
                isMarried && selectedChildren?.value === '1';

            countField.classList.toggle('d-none', !needsCount);
            countInput.disabled = !needsCount;
            countInput.required = needsCount;
        }

        maritalStatus.addEventListener('change', updateFamilyFields);

        childrenRadios.forEach((radio) => {
            radio.addEventListener('change', updateFamilyFields);
        });

        updateFamilyFields();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        initializePatientFamilyFields
    );
} else {
    initializePatientFamilyFields();
}

/*
|--------------------------------------------------------------------------
| VISITS — Gregorian Date, Weekday and Hijri Date
|--------------------------------------------------------------------------
*/

function initializeVisitCalendarFields() {
    document.querySelectorAll('[data-visit-calendar-form]').forEach((form) => {
        if (form.dataset.visitCalendarInitialized === 'true') {
            return;
        }

        const dateInput = form.querySelector('[data-visit-date]');
        const weekdayInput = form.querySelector('[data-visit-weekday]');
        const hijriInput = form.querySelector('[data-visit-hijri]');
        const message = form.querySelector('[data-visit-calendar-message]');

        if (!dateInput || !weekdayInput || !hijriInput || !message) {
            return;
        }

        form.dataset.visitCalendarInitialized = 'true';

        const weekdayFormatter = new Intl.DateTimeFormat('en-GB', {
            weekday: 'long',
            timeZone: 'UTC',
        });

        let hijriFormatter = null;

        try {
            const formatter = new Intl.DateTimeFormat('en-GB', {
                calendar: 'islamic-umalqura',
                numberingSystem: 'latn',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                era: 'short',
                timeZone: 'UTC',
            });

            if (
                formatter.resolvedOptions().calendar === 'islamic-umalqura'
            ) {
                hijriFormatter = formatter;
            }
        } catch {
            hijriFormatter = null;
        }

        function updateVisitCalendar() {
            weekdayInput.value = '';
            hijriInput.value = '';

            if (!dateInput.value || !dateInput.validity.valid) {
                message.textContent = 'Select a valid Gregorian date.';
                return;
            }

            const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(dateInput.value);

            if (!match) {
                message.textContent = 'Select a valid Gregorian date.';
                return;
            }

            const year = Number(match[1]);
            const month = Number(match[2]);
            const day = Number(match[3]);

            // Use a date-only UTC reference to avoid browser timezone shifts.
            const selectedDate = new Date(
                Date.UTC(year, month - 1, day, 12)
            );

            if (
                selectedDate.getUTCFullYear() !== year ||
                selectedDate.getUTCMonth() !== month - 1 ||
                selectedDate.getUTCDate() !== day
            ) {
                message.textContent = 'Select a valid Gregorian date.';
                return;
            }

            weekdayInput.value = weekdayFormatter.format(selectedDate);

            if (!hijriFormatter) {
                message.textContent =
                    'Umm al-Qura conversion is unavailable in this browser.';
                return;
            }

            hijriInput.value = hijriFormatter.format(selectedDate);

            message.textContent =
                'Calculated using the Umm al-Qura calendar.';
        }

        dateInput.addEventListener('input', updateVisitCalendar);
        dateInput.addEventListener('change', updateVisitCalendar);

        updateVisitCalendar();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        initializeVisitCalendarFields
    );
} else {
    initializeVisitCalendarFields();
}