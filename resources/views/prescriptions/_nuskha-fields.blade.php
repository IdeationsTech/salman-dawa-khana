@php
    $rowSource = $row['source'] ?? (!empty($row['nuskha_template_id']) ? 'template' : 'custom');
    $rowTemplate = (string) ($row['nuskha_template_id'] ?? '');
    $rowPrefix = 'rx-nuskha-' . $index;
@endphp
<article class="rx-nuskha-card" data-rx-nuskha-row data-original-template="{{ $rowTemplate }}">
    <div class="rx-nuskha-card-heading">
        <h3>{{ __('Nuskha') }} <span data-rx-nuskha-number>{{ is_numeric($index) ? $index + 1 : '' }}</span></h3>
        <button type="button" class="btn btn-outline-danger btn-sm" data-rx-remove-nuskha aria-label="{{ __('Remove nuskha') }}">
            <i class="bi bi-trash3" aria-hidden="true"></i>
            {{ __('Remove') }}
        </button>
    </div>

    <input type="hidden" name="nuskhas[{{ $index }}][assignment_id]" value="{{ $row['assignment_id'] ?? '' }}" data-rxn-field="assignment_id">

    <div class="template-form-grid">
        <div class="form-field">
            <label for="{{ $rowPrefix }}-source" class="form-label">{{ __('Nuskha source') }} *</label>
            <select id="{{ $rowPrefix }}-source" name="nuskhas[{{ $index }}][source]" class="form-select" data-rxn-field="source" required>
                <option value="template" @selected($rowSource === 'template')>{{ __('Saved template') }}</option>
                <option value="custom" @selected($rowSource === 'custom')>{{ __('Custom nuskha') }}</option>
            </select>
        </div>

        <div class="form-field" data-rxn-template-section @if($rowSource !== 'template') hidden @endif>
            <label for="{{ $rowPrefix }}-nuskha_template_id" class="form-label">{{ __('Saved nuskha') }} *</label>
            <select id="{{ $rowPrefix }}-nuskha_template_id" name="nuskhas[{{ $index }}][nuskha_template_id]" class="form-select" data-rxn-field="nuskha_template_id" @required($rowSource === 'template') @disabled($rowSource !== 'template')>
                <option value="">{{ __('Select a nuskha') }}</option>
                @foreach ($templates as $template)
                    <option
                        value="{{ $template->nuskha_template_id }}"
                        data-template-name="{{ $template->name }}"
                        data-active="{{ $template->is_active ? '1' : '0' }}"
                        @selected($rowTemplate === (string) $template->nuskha_template_id)
                        @disabled(!$template->is_active && $rowTemplate !== (string) $template->nuskha_template_id)
                    >
                        {{ $template->name }}{{ !$template->is_active ? ' — inactive; existing assignment only' : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-field template-field-wide">
            <label for="{{ $rowPrefix }}-nuskha_name" class="form-label">{{ __('Nuskha name') }} *</label>
            <input type="text" id="{{ $rowPrefix }}-nuskha_name" name="nuskhas[{{ $index }}][nuskha_name]" class="form-control" maxlength="160" value="{{ $row['nuskha_name'] ?? '' }}" data-rxn-field="nuskha_name" @readonly($rowSource === 'template') @required($rowSource === 'custom')>
        </div>

        <div class="form-field">
            <label for="{{ $rowPrefix }}-quantity" class="form-label">{{ __('Total nuskha quantity / weight') }} *</label>
            <input type="number" id="{{ $rowPrefix }}-quantity" name="nuskhas[{{ $index }}][quantity]" class="form-control" min="0.001" max="999999999.999" step="0.001" inputmode="decimal" placeholder="e.g. 20" value="{{ $row['quantity'] ?? '' }}" data-rxn-field="quantity" required>
            <small class="form-help">{{ __('Total finished nuskha, for example 20 g.') }}</small>
        </div>

        <div class="form-field">
            <label for="{{ $rowPrefix }}-unit" class="form-label">{{ __('Unit') }} *</label>
            <select id="{{ $rowPrefix }}-unit" name="nuskhas[{{ $index }}][unit]" class="form-select" data-rxn-field="unit" required>
                <option value="">{{ __('Select unit') }}</option>
                @foreach ($units as $value => $label)
                    <option value="{{ $value }}" @selected(($row['unit'] ?? '') === $value)>{{ __($label) }}</option>
                @endforeach
            </select>
        </div>

        @foreach ([
            'frequency' => ['Frequency', 'e.g. Twice daily'],
            'duration' => ['Duration', 'e.g. 7 days'],
            'timing' => ['Timing', 'e.g. After meals'],
        ] as $field => [$label, $placeholder])
            <div class="form-field">
                <label for="{{ $rowPrefix }}-{{ $field }}" class="form-label">{{ __($label) }}</label>
                <input type="text" id="{{ $rowPrefix }}-{{ $field }}" name="nuskhas[{{ $index }}][{{ $field }}]" class="form-control" maxlength="80" placeholder="{{ $placeholder }}" value="{{ $row[$field] ?? '' }}" data-rxn-field="{{ $field }}">
            </div>
        @endforeach

        <div class="form-field template-field-wide">
            <label for="{{ $rowPrefix }}-instructions" class="form-label">{{ __('How to take this nuskha') }}</label>
            <input type="text" id="{{ $rowPrefix }}-instructions" name="nuskhas[{{ $index }}][instructions]" class="form-control" maxlength="255" placeholder="{{ __('Patient instructions, if needed') }}" value="{{ $row['instructions'] ?? '' }}" data-rxn-field="instructions">
        </div>
    </div>
</article>
