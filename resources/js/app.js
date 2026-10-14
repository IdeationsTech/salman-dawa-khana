import 'bootstrap';
import intlTelInput from 'intl-tel-input';
import 'intl-tel-input/styles';

document.addEventListener('DOMContentLoaded', () => {
    initPasswordToggles();
    initAlerts();
    initPrintButtons();
    initNuskhaPrint();
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
| PRESCRIPTIONS — Server-side Filters
|--------------------------------------------------------------------------
*/

function initPrescriptionFilters() {
    const form = document.querySelector(
        '[data-server-prescription-filters]'
    );

    if (!form || form.dataset.filtersInitialized === '1') {
        return;
    }

    form.dataset.filtersInitialized = '1';

    const dateRange = form.elements.namedItem('date_range');
    const status = form.elements.namedItem('status');

    const applyFilters = () => {
        form.requestSubmit();
    };

    dateRange?.addEventListener('change', applyFilters);
    status?.addEventListener('change', applyFilters);

    // Search submits with Enter or the Search / Apply button.
    // Laravel filters all matching records before pagination.
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
| EXPENSES — Server-side Category and Date Filters
|--------------------------------------------------------------------------
*/

function initExpenseFilters() {
    const form = document.querySelector(
        '#expense-filter-form[data-server-filters]'
    );

    if (!form || form.dataset.expenseFiltersInitialized === '1') {
        return;
    }

    form.dataset.expenseFiltersInitialized = '1';

    // Includes dropdowns linked through form="expense-filter-form".
    const category = form.elements.namedItem('category');
    const dateRange = form.elements.namedItem('date_range');

    const applyFilters = () => {
        form.requestSubmit();
    };

    category?.addEventListener('change', applyFilters);
    dateRange?.addEventListener('change', applyFilters);

    // Search uses Enter or the Search button.
    // Laravel combines search, category and date before pagination.
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


/*
|--------------------------------------------------------------------------
| PRESCRIPTIONS — Sidebar Submenu
|--------------------------------------------------------------------------
*/

function initializePrescriptionSidebarSubmenu() {
    const source = document.querySelector(
        'template[data-prescription-submenu]'
    );

    if (!source) {
        return;
    }

    const parentPath = new URL(
        source.dataset.parentUrl,
        window.location.origin
    ).pathname.replace(/\/+$/, '');

    document.querySelectorAll('.sidebar-nav').forEach((navigation) => {
        if (navigation.querySelector('.sidebar-subnav')) {
            return;
        }

        const parentLink = Array.from(
            navigation.querySelectorAll('a.sidebar-link')
        ).find((link) => {
            const linkPath = new URL(
                link.href,
                window.location.origin
            ).pathname.replace(/\/+$/, '');

            return linkPath === parentPath;
        });

        if (!parentLink) {
            return;
        }

        parentLink.classList.add('active');

        parentLink.after(
            source.content.cloneNode(true)
        );
    });
}

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        initializePrescriptionSidebarSubmenu
    );
} else {
    initializePrescriptionSidebarSubmenu();
}

/*
|--------------------------------------------------------------------------
| NUSKHA TEMPLATE — Print Ingredient List for Pansar
|--------------------------------------------------------------------------
*/

function initNuskhaPrint() {
    const button = document.querySelector('[data-print-nuskha]');
    const sheet = document.querySelector('[data-nuskha-print-sheet]');

    if (!button || !sheet) {
        return;
    }

    button.addEventListener('click', () => {
        document.body.classList.add('nuskha-print-mode');

        window.addEventListener(
            'afterprint',
            () => document.body.classList.remove('nuskha-print-mode'),
            { once: true }
        );

        window.print();
    });
}

/*
|--------------------------------------------------------------------------
| PLATFORM ADMIN — Jump To Payments Section
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', () => {
    const link = document.querySelector('[data-pa-payments-link]');
    const section = document.getElementById('recent-payments');

    // On other pages, the normal link navigates to the dashboard.
    if (!link || !section) {
        return;
    }

    let highlightTimer;

    link.addEventListener('click', (event) => {
        event.preventDefault();

        const reducedMotion = window.matchMedia(
            '(prefers-reduced-motion: reduce)'
        ).matches;

        section.scrollIntoView({
            behavior: reducedMotion ? 'instant' : 'smooth',
            block: 'start',
        });

        window.history.replaceState(
            null,
            '',
            '#recent-payments'
        );

        section.classList.add('pa-section-focused');

        window.clearTimeout(highlightTimer);

        highlightTimer = window.setTimeout(() => {
            section.classList.remove('pa-section-focused');
        }, 1600);
    });
});

/*
|--------------------------------------------------------------------------
| CLINIC SIDEBAR — Notifications Link
|--------------------------------------------------------------------------
*/

function initializeClinicNotificationLink() {
    // Platform admin has its own navigation.
    if (document.body.classList.contains('pa-app')) {
        return;
    }

    const notificationPath = '/notifications';

    document.querySelectorAll('.sidebar-nav').forEach((navigation) => {
        const links = Array.from(
            navigation.querySelectorAll('a.sidebar-link')
        );

        const existingLink = links.find((link) => {
            const url = new URL(link.href, window.location.origin);

            return url.pathname.replace(/\/+$/, '') === notificationPath;
        });

        if (existingLink) {
            return;
        }

        const link = document.createElement('a');

        link.href = notificationPath;
        link.className = 'sidebar-link';

        const iconWrapper = document.createElement('span');
        const icon = document.createElement('i');

        icon.className = 'bi bi-bell app-icon';
        icon.setAttribute('aria-hidden', 'true');

        iconWrapper.appendChild(icon);

        const label = document.createElement('span');
        label.textContent = 'Notifications';

        link.append(iconWrapper, label);

        if (
            window.location.pathname.replace(/\/+$/, '') ===
            notificationPath
        ) {
            link.classList.add('active');
            link.setAttribute('aria-current', 'page');
        }

        const settingsLink = links.find((item) => {
            const path = new URL(
                item.href,
                window.location.origin
            ).pathname;

            return path === '/settings' ||
                path.startsWith('/settings/');
        });

        if (settingsLink) {
            settingsLink.before(link);
        } else {
            navigation.appendChild(link);
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        initializeClinicNotificationLink
    );
} else {
    initializeClinicNotificationLink();
}

/*
|--------------------------------------------------------------------------
| CLINIC SIDEBAR — Users and Expense Access
|--------------------------------------------------------------------------
*/

function initializeClinicRoleNavigation() {
    if (document.body.classList.contains('pa-app')) {
        return;
    }

    const canManageUsers =
        document.body.dataset.canManageUsers === '1';

    const canAccessExpenses =
        document.body.dataset.canAccessExpenses === '1';

    const usersUrl = document.body.dataset.usersUrl;

    const getPath = (url) =>
        new URL(url, window.location.origin)
            .pathname
            .replace(/\/+$/, '');

    document.querySelectorAll('.sidebar-nav').forEach((navigation) => {
        let usersLink = null;

        navigation.querySelectorAll('a.sidebar-link').forEach((link) => {
            const path = getPath(link.href);

            if (path === '/users' || path.startsWith('/users/')) {
                if (!canManageUsers) {
                    link.remove();
                } else if (!usersLink) {
                    usersLink = link;
                } else {
                    link.remove();
                }
            }

            if (
                !canAccessExpenses &&
                (path === '/expenses' || path.startsWith('/expenses/'))
            ) {
                link.remove();
            }
        });

        if (!canManageUsers || !usersUrl) {
            return;
        }

        if (!usersLink) {
            usersLink = document.createElement('a');
            usersLink.className = 'sidebar-link';

            const iconWrapper = document.createElement('span');
            const icon = document.createElement('i');

            icon.className = 'bi bi-people app-icon';
            icon.setAttribute('aria-hidden', 'true');

            iconWrapper.appendChild(icon);

            const label = document.createElement('span');
            label.textContent = 'Users';

            usersLink.append(iconWrapper, label);
        }

        usersLink.href = usersUrl;

        const currentPath = getPath(window.location.href);
        const isUsersPage =
            currentPath === '/users' ||
            currentPath.startsWith('/users/');

        usersLink.classList.toggle('active', isUsersPage);

        if (isUsersPage) {
            usersLink.setAttribute('aria-current', 'page');
        } else {
            usersLink.removeAttribute('aria-current');
        }

        const settingsLink = Array.from(
            navigation.querySelectorAll('a.sidebar-link')
        ).find((link) => {
            const path = getPath(link.href);

            return path === '/settings' ||
                path.startsWith('/settings/');
        });

        if (settingsLink) {
            settingsLink.before(usersLink);
        } else {
            navigation.appendChild(usersLink);
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        initializeClinicRoleNavigation
    );
} else {
    initializeClinicRoleNavigation();
}

/*
|--------------------------------------------------------------------------
| SHARED PHONE INPUT — Country Selection and Validation
|--------------------------------------------------------------------------
*/

function initializeInternationalPhoneInputs() {
    const formFields = new Map();

    document.querySelectorAll('[data-phone-field]').forEach((wrapper) => {
        const input = wrapper.querySelector('[data-international-phone]');
        const hidden = wrapper.querySelector('[data-phone-full]');
        const form = input?.form;

        if (!input || !hidden || !form || wrapper.dataset.phoneInitialized) {
            return;
        }

        wrapper.dataset.phoneInitialized = '1';

        const state = {
            input,
            hidden,
            instance: null,
            ready: false,
            failed: false,
        };

        state.instance = intlTelInput(input, {
            // No country is assumed for an empty field.
            // Existing international numbers select their country.
            initialCountry: '',
            countryOrder: ['ae', 'pk'],
            countrySearch: true,
            countrySelectorMode: 'AUTO',
            separateDialCode: true,
            showFlags: true,
            strictMode: true,
            formatAsYouType: true,
            allowedNumberTypes: ['MOBILE', 'FIXED_LINE'],
            allowNumberExtensions: false,
            loadUtils: () => import('intl-tel-input/utils'),
        });

        if (!formFields.has(form)) {
            formFields.set(form, []);
        }

        formFields.get(form).push(state);

        const syncNumber = () => {
            input.setCustomValidity('');

            if (!state.ready) {
                return;
            }

            hidden.value = input.value.trim()
                ? state.instance.getNumber()
                : '';
        };

        input.addEventListener('input', syncNumber);
        input.addEventListener('change', syncNumber);
        input.addEventListener('countrychange', syncNumber);

        state.instance.promise.then(() => {
            state.ready = true;
            syncNumber();
        }).catch(() => {
            state.failed = true;
        });

        input.addEventListener('blur', () => {
            if (state.ready && input.value.trim()) {
                if (!state.instance.isValidNumberPrecise()) {
                    input.setCustomValidity(
                        'Enter a valid phone number for the selected country.'
                    );
                }
            }
        });
    });

    formFields.forEach((fields, form) => {
        form.addEventListener('submit', (event) => {
            let firstInvalid = null;

            fields.forEach((state) => {
                const { input, hidden, instance } = state;

                if (input.disabled) {
                    return;
                }

                input.setCustomValidity('');

                if (!state.ready) {
                    input.setCustomValidity(
                        state.failed
                            ? 'Phone validation could not load. Refresh and try again.'
                            : 'Phone validation is loading. Please try again shortly.'
                    );

                    firstInvalid ??= input;
                    return;
                }

                if (!input.value.trim()) {
                    hidden.value = '';

                    if (input.required) {
                        input.setCustomValidity('Enter a phone number.');
                        firstInvalid ??= input;
                    }

                    return;
                }

                const country = instance.getSelectedCountryData();

                if (!country?.iso2) {
                    input.setCustomValidity('Select a country.');
                    firstInvalid ??= input;
                    return;
                }

                if (!instance.isValidNumberPrecise()) {
                    input.setCustomValidity(
                        'Enter a valid phone number for the selected country.'
                    );

                    firstInvalid ??= input;
                    return;
                }

                hidden.value = instance.getNumber();

                if (!/^\+[1-9]\d{1,14}$/.test(hidden.value)) {
                    input.setCustomValidity(
                        'Enter a valid international phone number.'
                    );

                    firstInvalid ??= input;
                }
            });

            if (firstInvalid) {
                event.preventDefault();
                event.stopImmediatePropagation();
                firstInvalid.focus();
                firstInvalid.reportValidity();
            }
        }, true);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        initializeInternationalPhoneInputs
    );
} else {
    initializeInternationalPhoneInputs();
}

/*
|--------------------------------------------------------------------------
| PATIENT ID — CNIC and Emirates ID Formatting
|--------------------------------------------------------------------------
*/

function initializeNationalIdFormatting() {
    document.querySelectorAll('[data-national-id]').forEach((input) => {
        if (input.dataset.idInitialized === '1') {
            return;
        }

        input.dataset.idInitialized = '1';

        const groups = input.dataset.idCountry === 'PK'
            ? [5, 7, 1]
            : [3, 4, 7, 1];

        const maxDigits = groups.reduce((total, size) => total + size, 0);

        const formatDigits = (digits) => {
            const parts = [];
            let offset = 0;

            groups.forEach((size) => {
                const part = digits.slice(offset, offset + size);

                if (part) {
                    parts.push(part);
                }

                offset += size;
            });

            return parts.join('-');
        };

        const formatInput = (preserveCaret = true) => {
            const original = input.value;
            const caret = input.selectionStart ?? original.length;

            const digitsBeforeCaret = original
                .slice(0, caret)
                .replace(/\D/g, '')
                .length;

            const digits = original
                .replace(/\D/g, '')
                .slice(0, maxDigits);

            input.value = formatDigits(digits);

            if (!preserveCaret || document.activeElement !== input) {
                return;
            }

            let position = 0;
            let counted = 0;

            while (
                position < input.value.length &&
                counted < digitsBeforeCaret
            ) {
                if (/\d/.test(input.value[position])) {
                    counted++;
                }

                position++;
            }

            input.setSelectionRange(position, position);
        };

        input.addEventListener('input', () => formatInput());
        input.addEventListener('blur', () => formatInput(false));

        // Existing saved numbers also appear with hyphens.
        formatInput(false);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        initializeNationalIdFormatting
    );
} else {
    initializeNationalIdFormatting();
}

/*
|--------------------------------------------------------------------------
| NUSKHA ORDERS — Items, Delivery and Amount Preview
|--------------------------------------------------------------------------
*/

function initializeNuskhaOrderForm() {
    const form = document.querySelector('[data-nuskha-order-form]');

    if (!form) return;

    const list = form.querySelector('[data-order-items]');
    const source = list.querySelector('[data-order-item]').cloneNode(true);
    const fulfillment = form.querySelector('[data-order-fulfillment]');
    const address = form.querySelector('[data-order-address]');
    const delivery = form.querySelector('[name="delivery_fee"]');
    const discount = form.querySelector('[name="discount"]');
    const received = form.querySelector('[name="payment_amount"]');
    const currency = form.dataset.currency;

    const scaled = (value, places) => {
        const text = String(value).trim();

        if (!/^\d+(?:\.\d+)?$/.test(text)) return 0n;

        const [whole, fraction = ''] = text.split('.');

        return BigInt(whole) * (10n ** BigInt(places))
            + BigInt(fraction.slice(0, places).padEnd(places, '0'));
    };

    const money = (cents) =>
        `${currency} ${Number(cents / 100n).toLocaleString('en-US')}`
        + `.${String(cents % 100n).padStart(2, '0')}`;

    const refresh = () => {
        let subtotal = 0n;
        const rows = list.querySelectorAll('[data-order-item]');

        rows.forEach((row, index) => {
            row.querySelector('[data-order-item-number]').textContent = index + 1;

            row.querySelectorAll('[data-order-field]').forEach((field) => {
                field.name = `items[${index}][${field.dataset.orderField}]`;
            });

            row.querySelector('[data-order-remove]').disabled = rows.length === 1;

            const template = row.querySelector('[data-order-field="nuskha_template_id"]');
            const name = row.querySelector('[data-order-field="nuskha_name"]');

            name.required = !template.value;

            const quantity = scaled(
                row.querySelector('[data-order-field="quantity"]').value,
                3
            );

            const price = scaled(
                row.querySelector('[data-order-field="unit_price"]').value,
                2
            );

            subtotal += (quantity * price + 500n) / 1000n;
        });

        const isDelivery = fulfillment.value === 'delivery';

        address.hidden = !isDelivery;
        address.querySelector('textarea').disabled = !isDelivery;
        address.querySelector('textarea').required = isDelivery;

        form.querySelector('[data-order-delivery-fee]').hidden = !isDelivery;

        const total = subtotal - scaled(discount.value, 2)
            + (isDelivery ? scaled(delivery.value, 2) : 0n);

        const safeTotal = total > 0n ? total : 0n;
        const due = safeTotal - scaled(received.value, 2);

        form.querySelector('[data-order-subtotal]').textContent = money(subtotal);
        form.querySelector('[data-order-total]').textContent = money(safeTotal);
        form.querySelector('[data-order-due]').textContent = money(due > 0n ? due : 0n);

        const receiving = scaled(received.value, 2) > 0n;
        const method = form.querySelector('[name="payment_method"]');
        const reference = form.querySelector('[name="reference_no"]');

        method.required = receiving;
        reference.required = receiving && ['bank', 'card'].includes(method.value);

        form.querySelector('[data-order-total-error]').textContent =
            total <= 0n
                ? 'Enter charges so the order total is greater than zero.'
                : due < 0n
                    ? 'Amount received exceeds the order total.'
                    : '';
    };

    form.querySelector('[data-order-add]').addEventListener('click', () => {
        if (list.querySelectorAll('[data-order-item]').length >= 50) return;

        const row = source.cloneNode(true);

        row.querySelectorAll('[data-order-field]').forEach((field) => {
            field.value = field.dataset.orderField === 'quantity'
                ? '1'
                : field.dataset.orderField === 'unit'
                    ? 'pack'
                    : '';
        });

        list.appendChild(row);
        refresh();
    });

    list.addEventListener('click', (event) => {
        const button = event.target.closest('[data-order-remove]');

        if (button && list.querySelectorAll('[data-order-item]').length > 1) {
            button.closest('[data-order-item]').remove();
            refresh();
        }
    });

    form.addEventListener('input', refresh);
    form.addEventListener('change', refresh);

    refresh();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeNuskhaOrderForm);
} else {
    initializeNuskhaOrderForm();
}

/*
|--------------------------------------------------------------------------
| PATIENTS — Server Search and Status Filters
|--------------------------------------------------------------------------
*/

function initializeServerPatientFilters() {
    const form = document.querySelector(
        '[data-server-patient-filters]'
    );

    if (!form || form.dataset.patientFiltersInitialized === '1') {
        return;
    }

    form.dataset.patientFiltersInitialized = '1';

    const search = form.querySelector('[name="search"]');
    const status = form.querySelector('[name="status"]');

    // Search is applied by submitting the form, not by hiding table rows.
    search?.addEventListener('input', (event) => {
        event.stopImmediatePropagation();
    }, true);

    search?.addEventListener('change', (event) => {
        event.stopImmediatePropagation();
    }, true);

    status?.addEventListener('change', (event) => {
        event.stopImmediatePropagation();
        form.requestSubmit();
    }, true);
}

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        initializeServerPatientFilters
    );
} else {
    initializeServerPatientFilters();
}

/*
|--------------------------------------------------------------------------
| PAYMENTS — Saved Currency and Bill Balances
|--------------------------------------------------------------------------
*/

function initializeCurrencyPaymentForm() {
    const form = document.querySelector('[data-currency-payment-form]');

    if (!form || form.dataset.currencyPaymentInitialized === '1') {
        return;
    }

    form.dataset.currencyPaymentInitialized = '1';

    const get = (name) => form.querySelector(`[data-cp-${name}]`);

    const mode = get('mode');
    const patient = get('patient');
    const bill = get('bill');
    const subtotal = get('subtotal');
    const discount = get('discount');
    const amount = get('amount');
    const method = get('method');
    const reference = get('reference');
    const notes = get('notes');
    const submit = get('submit');
    const help = get('help');

    const editing = form.dataset.editing === '1';
    const clinicCurrency = form.dataset.clinicCurrency;
    const savedCurrency = form.dataset.savedCurrency;
    const allowPartial = form.dataset.allowPartial === '1';
    const decimals = form.dataset.moneyDecimals === '0' ? 0 : 2;

    let bills;

    try {
        bills = JSON.parse(get('bills').textContent);

        if (!Array.isArray(bills)) {
            throw new Error('Invalid bill data.');
        }
    } catch {
        help.textContent = 'Bill details could not load. Reload the page.';
        submit.disabled = true;
        return;
    }

    const validCurrency = (code) =>
        ['AED', 'PKR', 'USD'].includes(code);

    const cents = (input) => {
        const text = input.value.trim();

        if (!/^\d{1,10}(?:\.\d{1,2})?$/.test(text)) {
            return null;
        }

        const [whole, fraction = ''] = text.split('.');

        return BigInt(whole) * 100n
            + BigInt(fraction.padEnd(2, '0'));
    };

    const decimal = (value) =>
        `${value / 100n}.${String(value % 100n).padStart(2, '0')}`;

    const money = (value, code) => {
        if (!validCurrency(code)) return '—';

        if (decimals === 0) {
            return `${code} ${((value + 50n) / 100n).toLocaleString('en-US')}`;
        }

        return `${code} ${(value / 100n).toLocaleString('en-US')}`
            + `.${String(value % 100n).padStart(2, '0')}`;
    };

    const relatedBills = () => bills.filter(
        (item) => String(item.patient_id) === patient.value
    );

    const update = () => {
        const isNew = mode.value === 'new_bill';
        const isExisting = mode.value === 'existing_bill';
        const isUnallocated = mode.value === 'unallocated';

        get('new-section').hidden = !isNew;
        get('new-fields').disabled = !isNew;
        get('existing-section').hidden = !isExisting;

        bill.disabled = editing || !isExisting;
        bill.required = isExisting && !editing;
        amount.min = isNew ? '0' : '0.01';

        [subtotal, discount, amount, bill].forEach((input) =>
            input.setCustomValidity('')
        );

        const records = relatedBills();

        const selected = records.find(
            (item) => String(item.bill_id) === bill.value
        );

        const currency = isNew
            ? clinicCurrency
            : (isExisting ? selected?.currency : savedCurrency);

        form.querySelectorAll('[data-cp-currency]').forEach((element) => {
            element.textContent = currency || 'Select bill';
        });

        const outstanding = records
            .filter((item) => item.currency === currency)
            .reduce((sum, item) => sum + BigInt(item.due_cents), 0n);

        const charges = cents(subtotal);
        const reduction = cents(discount);
        const received = cents(amount);

        let current = 0n;
        let others = outstanding;
        let validBill = validCurrency(currency);

        if (isNew) {
            validBill = validBill
                && charges !== null
                && charges > 0n
                && reduction !== null
                && reduction < charges;

            if (
                charges !== null &&
                reduction !== null &&
                reduction >= charges
            ) {
                discount.setCustomValidity(
                    'Discount must be less than the charges amount.'
                );
            }

            current = validBill ? charges - reduction : 0n;
        } else if (isExisting) {
            validBill = validBill && Boolean(selected);
            current = selected ? BigInt(selected.due_cents) : 0n;
            others = outstanding >= current ? outstanding - current : 0n;

            if (bill.value && !validBill) {
                bill.setCustomValidity(
                    'Select a bill with a confirmed currency.'
                );
            }
        }

        let error = '';

        if (isExisting && !validBill) {
            error = 'Select a bill with a confirmed currency.';
        }

        if (
            !isUnallocated &&
            validBill &&
            received !== null &&
            received > current
        ) {
            error = 'Received amount exceeds this bill’s remaining balance.';
            amount.setCustomValidity(error);
        } else if (
            !isUnallocated &&
            validBill &&
            !allowPartial &&
            received !== null &&
            received > 0n &&
            received < current
        ) {
            error = 'Partial payments are disabled. Enter the full bill balance.';
            amount.setCustomValidity(error);
        }

        amount.max = !isUnallocated && validBill
            ? decimal(current)
            : '9999999999.99';

        get('other').textContent = patient.value
            ? money(others, currency)
            : '—';

        get('current').textContent = !isUnallocated && validBill
            ? money(current, currency)
            : '—';

        get('total').textContent = patient.value
            ? money(isNew ? outstanding + current : outstanding, currency)
            : '—';

        const receivedForPreview = received ?? 0n;

        if (isUnallocated) {
            get('remaining').textContent = 'Not applied to a bill';
            get('patient-remaining').textContent = money(outstanding, currency);
        } else if (validBill && !error) {
            const remaining = current >= receivedForPreview
                ? current - receivedForPreview
                : 0n;

            get('remaining').textContent = money(remaining, currency);
            get('patient-remaining').textContent = patient.value
                ? money(others + remaining, currency)
                : '—';
        } else {
            get('remaining').textContent = '—';
            get('patient-remaining').textContent = '—';
        }

        help.textContent = error || (
            isUnallocated
                ? 'This payment is not applied to any bill.'
                : `Only ${currency || 'the selected currency'} balances are included. `
                    + 'Other currencies remain separate.'
        );

        const receiving = received !== null && received > 0n;
        method.disabled = !receiving;
        method.required = receiving;
        reference.disabled = !receiving;
        notes.disabled = !receiving;

        const needsReference = receiving
            && ['bank', 'card'].includes(method.value);

        reference.required = needsReference;
        get('reference-star').hidden = !needsReference;

        get('empty').hidden = !(
            isExisting &&
            patient.value &&
            records.every((item) =>
                BigInt(item.due_cents) <= 0n
                && String(item.bill_id) !== bill.value
            )
        );

        const receipt = get('receipt');
        receipt.hidden = !(isExisting && selected);

        if (isExisting && selected) {
            receipt.href = selected.receipt_url;
        } else {
            receipt.removeAttribute('href');
        }

        submit.textContent = editing
            ? 'Update payment'
            : (isNew
                ? (receiving ? 'Save bill and payment' : 'Save unpaid bill')
                : 'Save payment');
    };

    const rebuildBills = (keep = '') => {
        bill.replaceChildren(new Option('Select bill', ''));

        relatedBills()
            .filter((item) =>
                BigInt(item.due_cents) > 0n
                || String(item.bill_id) === String(keep)
            )
            .forEach((item) => {
                const label = validCurrency(item.currency)
                    ? money(BigInt(item.due_cents), item.currency)
                    : 'Currency not recorded';

                const option = new Option(
                    `${item.bill_no} — ${label} due`,
                    String(item.bill_id)
                );

                option.disabled = !validCurrency(item.currency);
                bill.add(option);
            });

        bill.value = String(keep);

        if (bill.selectedIndex < 0) {
            bill.value = '';
        }

        update();
    };

    patient.addEventListener('change', () => rebuildBills(''));

    [mode, bill, method].forEach((element) => {
        element.addEventListener('change', update);
    });

    [subtotal, discount, amount].forEach((element) => {
        element.addEventListener('input', update);
    });

    form.addEventListener('submit', (event) => {
        update();

        if (!form.checkValidity()) {
            event.preventDefault();
            form.reportValidity();
            return;
        }

        submit.disabled = true;
        submit.textContent = 'Saving…';
    });

    window.addEventListener('pageshow', () => {
        submit.disabled = false;
        update();
    });

    rebuildBills(bill.dataset.selectedBill || '');
}

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        initializeCurrencyPaymentForm
    );
} else {
    initializeCurrencyPaymentForm();
}

/*
|--------------------------------------------------------------------------
| USERS — Server Search and Status Filters
|--------------------------------------------------------------------------
*/

function initializeServerUserFilters() {
    const form = document.querySelector('[data-server-user-filters]');

    if (!form || form.dataset.serverUsersInitialized === '1') {
        return;
    }

    form.dataset.serverUsersInitialized = '1';

    const search = form.elements.namedItem('search');
    const status = form.elements.namedItem('status');

    // Prevent the old handler from hiding only the currently loaded rows.
    search?.addEventListener('input', (event) => {
        event.stopImmediatePropagation();
    }, true);

    search?.addEventListener('change', (event) => {
        event.stopImmediatePropagation();
    }, true);

    status?.addEventListener('change', (event) => {
        event.stopImmediatePropagation();
        form.requestSubmit();
    }, true);
}

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        initializeServerUserFilters
    );
} else {
    initializeServerUserFilters();
}