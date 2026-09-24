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
| BILLING — New Bill, Existing Bill and Payment Calculation
|--------------------------------------------------------------------------
*/

function initPaymentCalculation() {
    const form = document.querySelector('[data-payment-form]');

    if (!form || form.dataset.billingInitialized === '1') {
        return;
    }

    form.dataset.billingInitialized = '1';

    const get = (selector) => form.querySelector(selector);

    const mode = get('[data-payment-mode]');
    const patient = get('[data-payment-patient]');
    const bill = get('[data-payment-bill]');

    const subtotal = get('[data-bill-subtotal]');
    const discount = get('[data-bill-discount]');
    const amount = get('[data-payment-amount]');

    const method = get('[data-payment-method]');
    const reference = get('[data-payment-reference]');
    const notes = get('[data-payment-notes]');
    const referenceStar = get('[data-payment-reference-star]');

    const newSection = get('[data-new-bill-section]');
    const newFields = get('[data-new-bill-fields]');
    const existingSection = get('[data-existing-bill-section]');

    const otherDue = get('[data-payment-other-due]');
    const selectedDue = get('[data-total-payable]');
    const totalDue = get('[data-payment-total-due]');

    const remaining = get('[data-remaining-balance]');
    const patientRemaining = get('[data-patient-remaining]');
    const remainingLabel = get('[data-payment-remaining-label]');

    const otherLabel = get('[data-other-due-label]');
    const currentLabel = get('[data-current-bill-label]');
    const dateLabel = get('[data-payment-date-label]');

    const help = get('[data-payment-help]');
    const emptyMessage = get('[data-bill-empty-message]');
    const receiptLink = get('[data-bill-receipt-link]');
    const submitButton = get('[data-payment-submit]');

    if (!mode || !patient || !bill || !amount || !subtotal || !discount) {
        return;
    }

    const currency = form.dataset.currency || 'AED';
    const editing = form.dataset.editing === '1';

    let bills;

    try {
        bills = JSON.parse(
            get('[data-payment-bills]')?.textContent || '[]'
        );

        if (!Array.isArray(bills)) {
            throw new Error('Invalid bill data.');
        }
    } catch {
        help.textContent = 'Bill details could not load. Reload this page.';
        submitButton.disabled = true;
        return;
    }

    const money = (cents) => {
        const number = Number(cents) / 100;

        return `${currency} ${number.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}`;
    };

    const readCents = (input) => {
        const value = input.value.trim();

        if (!/^\d+(?:\.\d{1,2})?$/.test(value)) {
            return null;
        }

        const [whole, fraction = ''] = value.split('.');

        return Number(whole) * 100
            + Number(fraction.padEnd(2, '0'));
    };

    const relatedBills = () => bills.filter(
        (item) => String(item.patient_id) === patient.value
    );

    const update = () => {
        const isNew = mode.value === 'new_bill';
        const isExisting = mode.value === 'existing_bill';
        const isUnallocated = mode.value === 'unallocated';

        newSection.hidden = !isNew;
        newFields.disabled = !isNew;
        existingSection.hidden = !isExisting;

        bill.disabled = editing || !isExisting;
        bill.required = isExisting && !editing;

        amount.min = isNew ? '0' : '0.01';

        subtotal.setCustomValidity('');
        discount.setCustomValidity('');
        amount.setCustomValidity('');
        bill.setCustomValidity('');

        const records = relatedBills();

        const selected = records.find(
            (item) => String(item.bill_id) === bill.value
        );

        const outstanding = records.reduce(
            (sum, item) => sum + Number(item.due_cents),
            0
        );

        const charges = readCents(subtotal);
        const reduction = readCents(discount);
        const received = readCents(amount);
        const receivedForPreview = received ?? 0;

        let current = 0;
        let others = outstanding;
        let validBill = true;

        if (isNew) {
            otherLabel.textContent = 'Previous outstanding bills';
            currentLabel.textContent = 'New bill total';
            dateLabel.textContent = 'Bill / payment date and time';

            validBill = charges !== null
                && charges > 0
                && reduction !== null
                && reduction < charges;

            if (
                charges !== null
                && charges > 0
                && reduction !== null
                && reduction >= charges
            ) {
                discount.setCustomValidity(
                    'Discount must be less than the charges amount.'
                );
            }

            current = validBill ? charges - reduction : 0;

            help.textContent =
                'Previous dues are not added to the new bill. This payment applies only to the new bill.';
        } else if (isExisting) {
            otherLabel.textContent = 'Other outstanding bills';
            currentLabel.textContent = 'Selected bill due';
            dateLabel.textContent = 'Payment date and time';

            validBill = Boolean(selected);
            current = selected ? Number(selected.due_cents) : 0;
            others = outstanding - current;

            if (bill.value && !selected) {
                bill.setCustomValidity(
                    'Select a bill belonging to this patient.'
                );
            }

            help.textContent =
                'This payment reduces only the selected bill. Other bills remain unchanged.';
        } else {
            otherLabel.textContent = 'Outstanding bills';
            currentLabel.textContent = 'Unallocated payment';
            dateLabel.textContent = 'Payment date and time';

            help.textContent =
                'This existing unallocated payment is not applied to a bill.';
        }

        amount.max = !isUnallocated && validBill
            ? (current / 100).toFixed(2)
            : '9999999999.99';

        const overpaid = !isUnallocated
            && validBill
            && received !== null
            && received > current;

        if (overpaid) {
            amount.setCustomValidity(
                'Received amount cannot exceed this bill’s remaining balance.'
            );
        }

        otherDue.textContent = patient.value ? money(others) : '—';

        selectedDue.textContent = isUnallocated
            ? '—'
            : (validBill ? money(current) : '—');

        totalDue.textContent = patient.value
            ? money(isNew ? outstanding + current : outstanding)
            : '—';

        remainingLabel.textContent = isUnallocated
            ? 'Outstanding bills remain unchanged'
            : 'This bill’s remaining balance';

        if (isUnallocated) {
            remaining.textContent = patient.value
                ? money(outstanding)
                : '—';

            patientRemaining.textContent = patient.value
                ? money(outstanding)
                : '—';
        } else if (overpaid) {
            remaining.textContent = 'Amount exceeds bill balance';
            patientRemaining.textContent = '—';
        } else if (validBill) {
            const billRemaining = current - receivedForPreview;

            remaining.textContent = money(billRemaining);

            patientRemaining.textContent = patient.value
                ? money(others + billRemaining)
                : '—';
        } else {
            remaining.textContent = '—';
            patientRemaining.textContent = '—';
        }

        const receiving = received !== null && received > 0;

        method.disabled = !receiving;
        method.required = receiving;

        reference.disabled = !receiving;
        notes.disabled = !receiving;

        const needsReference = receiving
            && ['bank', 'card'].includes(method.value);

        reference.required = needsReference;
        referenceStar.hidden = !needsReference;

        reference.placeholder = needsReference
            ? 'Required transaction reference'
            : 'Optional reference';

        emptyMessage.hidden = !(
            isExisting
            && patient.value
            && records.every((item) =>
                Number(item.due_cents) <= 0
                && String(item.bill_id) !== bill.value
            )
        );

        receiptLink.hidden = !(isExisting && selected);

        if (isExisting && selected) {
            receiptLink.href = selected.receipt_url;
        } else {
            receiptLink.removeAttribute('href');
        }

        submitButton.textContent = editing
            ? 'Update payment'
            : (isNew
                ? (receiving ? 'Save bill and payment' : 'Save unpaid bill')
                : 'Save payment');
    };

    const rebuildBills = (keep = '') => {
        bill.replaceChildren(new Option('Select bill', ''));

        relatedBills()
            .filter((item) =>
                Number(item.due_cents) > 0
                || String(item.bill_id) === String(keep)
            )
            .forEach((item) => {
                bill.add(new Option(
                    `${item.bill_no} — ${money(item.due_cents)} due`,
                    String(item.bill_id)
                ));
            });

        bill.value = String(keep);

        if (bill.selectedIndex < 0) {
            bill.value = '';
        }

        update();
    };

    patient.addEventListener('change', () => rebuildBills(''));

    mode.addEventListener('change', update);
    bill.addEventListener('change', update);
    method.addEventListener('change', update);

    [subtotal, discount, amount].forEach((input) => {
        input.addEventListener('input', update);
    });

    form.addEventListener('submit', (event) => {
        update();

        if (!form.checkValidity()) {
            event.preventDefault();
            form.reportValidity();
            return;
        }

        // Prevent accidental repeated clicks while this submission is loading.
        submitButton.disabled = true;
        submitButton.textContent = 'Saving…';
    });

    window.addEventListener('pageshow', () => {
        submitButton.disabled = false;
        update();
    });

    rebuildBills(bill.dataset.selectedBill || '');
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
| VISITS — Server Filters
|--------------------------------------------------------------------------
*/

function initVisitFilters() {
    const form = document.querySelector(
        '[data-server-visit-filters]'
    );

    if (!form || form.dataset.visitFiltersInitialized === 'true') {
        return;
    }

    form.dataset.visitFiltersInitialized = 'true';

    // Includes dropdowns connected through form="visit-filter-form".
    Array.from(form.elements).forEach((element) => {
        if (element.tagName !== 'SELECT') {
            return;
        }

        element.addEventListener('change', () => {
            form.requestSubmit();
        });
    });

    // Search submits through Enter or the Search / Apply button.
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
| PAYMENTS — Server-side Filters
|--------------------------------------------------------------------------
*/

function initPaymentFilters() {
    const form = document.querySelector('[data-server-payment-filters]');

    if (!form || form.dataset.filtersInitialized === '1') {
        return;
    }

    form.dataset.filtersInitialized = '1';

    const method = form.elements.namedItem('method');
    const dateRange = form.elements.namedItem('date_range');

    const applyFilters = () => {
        form.requestSubmit();
    };

    method?.addEventListener('change', applyFilters);
    dateRange?.addEventListener('change', applyFilters);

    // Search runs with Enter or the Search button.
    // Laravel applies all filters before pagination.
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
| GLOBAL — Search Only on Client-filtered Tables
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
            const form = input.form;

            if (
                form?.matches(
                    '[data-server-payment-filters], ' +
                    '[data-server-visit-filters], ' +
                    '[data-server-filters]'
                )
            ) {
                return;
            }

            if (input.dataset.tableSearchInitialized === '1') {
                return;
            }

            input.dataset.tableSearchInitialized = '1';

            input.addEventListener('input', () => {
                const table = input
                    .closest('main')
                    ?.querySelector('tbody');

                if (!table) {
                    return;
                }

                const term = input.value.trim().toLowerCase();

                table.querySelectorAll('tr').forEach((row) => {
                    row.style.display = row.textContent
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


/*
|--------------------------------------------------------------------------
| PRESCRIPTIONS — Patient Visits and Saved Templates
|--------------------------------------------------------------------------
*/

function initializeDynamicPrescriptionForm() {
    const form = document.querySelector('[data-prescription-form]');

    if (!form || form.dataset.dynamicPrescriptionInitialized === 'true') {
        return;
    }

    const patientSelect = form.querySelector('[data-rx-patient]');
    const visitSelect = form.querySelector('[data-rx-visit]');
    const templateSelect = form.querySelector('[data-rx-template]');
    const templateSection = form.querySelector('[data-rx-template-section]');
    const applyButton = form.querySelector('[data-rx-apply-template]');
    const itemList = form.querySelector('[data-prescription-item-list]');
    const instructions = form.querySelector('[data-rx-instructions]');
    const message = form.querySelector('[data-rx-template-message]');
    const dataElement = form.querySelector('[data-rx-template-data]');
    const modeInputs = form.querySelectorAll('[name="prescription_type"]');

    if (
        !patientSelect || !visitSelect || !templateSelect ||
        !templateSection || !applyButton || !itemList ||
        !instructions || !message || !dataElement
    ) {
        return;
    }

    form.dataset.dynamicPrescriptionInitialized = 'true';

    /*
    | Patient → matching visits
    */

    function updatePatientVisits() {
        const patientId = patientSelect.value;

        Array.from(visitSelect.options).forEach((option) => {
            if (option.value === '') {
                return;
            }

            const belongsToPatient =
                option.dataset.patientId === patientId;

            option.hidden = !belongsToPatient;
            option.disabled = !belongsToPatient;

            if (!belongsToPatient && option.selected) {
                visitSelect.value = '';
            }
        });
    }

    patientSelect.addEventListener('change', updatePatientVisits);
    updatePatientVisits();

    /*
    | Custom/template mode
    */

    function updatePrescriptionMode() {
        const mode = form.querySelector(
            '[name="prescription_type"]:checked'
        )?.value;

        const usingTemplate = mode === 'template';

        templateSection.classList.toggle('d-none', !usingTemplate);
        templateSelect.disabled = !usingTemplate;
        templateSelect.required = usingTemplate;

        modeInputs.forEach((input) => {
            input.closest('.prescription-choice')
                ?.classList.toggle('active', input.checked);
        });
    }

    modeInputs.forEach((input) => {
        input.addEventListener('change', updatePrescriptionMode);
    });

    updatePrescriptionMode();

    /*
    | Load saved template into editable item rows
    */

    let templates = [];

    try {
        templates = JSON.parse(dataElement.textContent);
    } catch {
        message.textContent = 'Template data could not be loaded.';
        applyButton.disabled = true;
        return;
    }

    applyButton.addEventListener('click', () => {
        const template = templates.find(
            (entry) => String(entry.id) === templateSelect.value
        );

        if (!template) {
            message.textContent = 'Please select a saved template.';
            return;
        }

        if (!Array.isArray(template.items) || template.items.length === 0) {
            message.textContent = 'This template has no items.';
            return;
        }

        const firstRow = itemList.querySelector('.prescription-item-row');

        if (!firstRow) {
            message.textContent = 'An item row is required to load the template.';
            return;
        }

        const hasCurrentContent =
            instructions.value.trim() !== '' ||
            Array.from(itemList.querySelectorAll('input')).some(
                (input) => input.value.trim() !== ''
            );

        if (
            hasCurrentContent &&
            !window.confirm(
                'Replace the current items and patient instructions with this template?'
            )
        ) {
            return;
        }

        const fragment = document.createDocumentFragment();

        template.items.forEach((item, index) => {
            const row = firstRow.cloneNode(true);

            row.querySelector('.item-number').textContent = index + 1;

            row.querySelectorAll('[data-rx-field]').forEach((input) => {
                const field = input.dataset.rxField;

                input.name = `items[${index}][${field}]`;
                input.value = item[field] ?? '';
                input.classList.remove('is-invalid');
            });

            fragment.appendChild(row);
        });

        itemList.replaceChildren(fragment);
        instructions.value = template.instructions ?? '';

        message.textContent =
            'Template loaded. Review or edit the items before saving.';
    });
}

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        initializeDynamicPrescriptionForm
    );
} else {
    initializeDynamicPrescriptionForm();
}