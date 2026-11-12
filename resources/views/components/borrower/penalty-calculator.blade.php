@props([])

<div class="ldr-penalty-calculator">
    <div class="ldr-penalty-calculator__field">
        <label class="ldr-penalty-calculator__label" for="calculator-hours">{{ __('ui.penalty.calculator_hours') }}</label>
        <input id="calculator-hours" type="number" data-calculator-hours data-rate="10000" data-subsidy="10" class="ldr-input" min="0" step="1" value="1">
    </div>

    <div class="ldr-penalty-calculator__field">
        <label class="ldr-penalty-calculator__label" for="calculator-units">{{ __('ui.penalty.calculator_units') }}</label>
        <input id="calculator-units" type="number" data-calculator-units class="ldr-input" min="1" step="1" value="1">
    </div>

    <div class="ldr-penalty-calculator__result">
        <div class="ldr-penalty-calculator__result__row">
            <span>{{ __('ui.penalty.calculator_total') }}</span>
            <span data-calculator-result>Rp 10.000</span>
        </div>
    </div>
</div>