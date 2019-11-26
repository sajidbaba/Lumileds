<template>
    <td>
        <div class="form-group" :class=
                "{'has-error': cell.error && cell.error_type === 0,
                'has-warning': cell.error && cell.error_type === 1,
                'readonly-cell-has-warning': cell.error && cell.error_type === 1 && !cell.is_editable,
                'has-contributions': cell.contributed,
                'version': versionValue(cell) }">
            <input
                    class="form-control"
                    :class="{'no-border': !cell.is_editable}"
                    type="text"
                    :readonly="!cell.is_editable"
                    v-model="formatted"
                    v-tooltip.down="{ content: tooltip }"
                    @focus="onFocus(cell, $event)"
                    @blur="onBlur(cell, $event)"
                    @keyup.enter="emitUpdate(cell.id, cell.value)"
            />
        </div>
    </td>
</template>

<script>
    const locale = 'en';
    export default {
        props: {
            cell: Object,
            trackedCells: Object,
        },
        data() {
            return {
                lastValue: null,
                isInputActive: false,
            }
        },
        methods: {
            emitUpdate: function(id, value) {
                this.trackedCells[id] = {id: id, value: value};
                this.$emit('updateCells');
            },
            onFocus: function(cell, event) {
                if (!cell.is_editable) {
                    return;
                }

                let elem = event.target;

                if (cell.is_percentage && (elem.value.search('%') !== -1)) {
                    elem.value = elem.value.replace('%', '');
                }

                this.isInputActive = true;
                this.lastValue = elem.value;
            },
            onBlur: function(cell, event) {
                if (!cell.is_editable) {
                    return;
                }

                let elem = event.target || event.relatedTarget;

                if (elem.value !== this.lastValue) {
                    this.emitUpdate(cell.id, cell.value);
                }

                if (elem.value === '') {
                    elem.value = 0;
                }
                if (cell.is_percentage && (elem.value.search('%') === -1)) {
                    elem.value += '%';
                }

                this.isInputActive = false;
            },
            getFormatted: function (value, isPercentage, precision) {
                value = parseFloat(value);
                if (isNaN(value)) {
                    return;
                }

                if (isPercentage) {
                    value = value * 100;
                }

                value = parseFloat(value)
                    .toLocaleString(locale, {maximumFractionDigits: precision});

                if (isPercentage && this.isInputActive === false) {
                    value = value + '%';
                }

                return value;
            },
            versionValue: function (cell) {
                if (!cell.hasOwnProperty('version_value')) {
                    return false;
                }

                let formattedValue = this.getFormatted(
                    cell.value,
                    cell.is_percentage,
                    cell.precision,
                );

                let formattedVersionValue = this.getFormatted(
                    cell.version_value,
                    cell.is_percentage,
                    cell.precision,
                );

                return formattedValue !== formattedVersionValue;
            }
        },
        computed: {
            formatted: {
                get: function () {
                    return this.getFormatted(
                        this.cell.value,
                        this.cell.is_percentage,
                        this.cell.precision,
                    );
                },
                set: function (newValue) {
                    newValue = newValue.replace(/,/g,'');

                    if (newValue.indexOf('.') !== -1) {
                        newValue = parseFloat(newValue).toFixed(this.cell.precision);
                    } else {
                        newValue = parseInt(newValue).toFixed(this.cell.precision);
                    }

                    if (this.cell.is_percentage) {
                        newValue = newValue.replace('%', '') / 100;
                    }

                    this.cell.value = newValue;
                }
            },
            tooltip: function () {
                if (this.cell.hasOwnProperty('error')) {
                    return this.cell.error;
                }

                if (this.cell.hasOwnProperty('version_value')) {
                    return this.getFormatted(
                        this.cell.version_value,
                        this.cell.is_percentage,
                        this.cell.precision,
                    );
                }

                return '';
            }
        }
    }
</script>

<style lang="scss" scoped>
    @import "../../css/variables";

    input {
        width: 80px;
        margin: 2px;
        padding: 5px;
        text-align: right;
    }

    input[readonly] {
        background-color: #ddd;
        cursor: default;
        box-shadow: none;
    }

    .no-border {
        border: none;
    }

    .tracked-cell {
        border: 1px green solid;
    }

    .has-error .form-control {
        color: $font-color;
        background-color: $error-background-color;
    }

    .has-warning .form-control {
        color: $font-color;
        background-color: $warning-background-color;
        border-color: $warning-border-color;
    }

    .readonly-cell-has-warning .form-control {
        background-color: $readonly-warning-background-color;
    }

    .version .form-control {
        background-color: #5cb85c;
    }

    .has-contributions.has-warning, .has-contributions.has-error {
        text-decoration-color: $font-color;
    }

    .has-contributions {
        color: black;
        font-weight: bold;
        text-decoration: underline;
    }

    .has-contributions input {
        border: 1px solid red;
    }

    .form-control {
        font-size: 12px;
    }

    .form-group {
        margin-bottom: 0;
    }
</style>
