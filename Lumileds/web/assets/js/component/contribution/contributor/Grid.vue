<template>
    <div>
        <div class="header">
            <table v-show="rows.length" class="edit-table">
                <thead>
                <tr>
                    <th class="th-market">Market</th>
                    <th class="th-technology">Technology</th>
                    <th class="th-technology">Indicator</th>
                    <th class="th-year" v-for="year in years">{{ year }}</th>
                </tr>
                </thead>
            </table>
        </div>

        <table v-show="rows.length" class="edit-table">
            <div class="data">
                <tbody>
                <tr class="tr-top" v-for="row in rows">
                    <td class="market">{{row.market}}</td>
                    <td class="technology">{{row.technology}}</td>
                    <td class="technology">{{row.indicator}}</td>
                    <Cell
                            v-for="cell in row.cells"
                            :cell="cell"
                            :key="cell.id"
                            :trackedCells="trackedCells"
                            @updateCells="updateCells"
                    />
                </tr>
                <tr v-show="showTotal">
                    <td></td>
                    <td></td>
                    <td>Total</td>
                    <td v-for="value in total">
                        <div class="form-group">
                            <input
                                    class="form-control"
                                    type="text"
                                    :readonly="true"
                                    :value="format(value)"
                            />
                        </div>
                    </td>
                </tr>
                </tbody>
            </div>
        </table>
    </div>
</template>

<script>
    import Cell from '../../Cell';

    export default {
        components: { Cell },
        props: {
            trackedCells: Object,
            rows: Array,
            years: Array,
            total: Array,
            showTotal: Boolean,
        },
        data () {
            return {
                segment: 'LV',
                regions: {},
                markets: {},
                indicators: {},
            }
        },
        methods: {
            updateCells () {
                this.$emit('updateCells');
            },
            format (value) {
                return parseFloat(value * 100).toFixed() + '%';
            }
        }
    }
</script>

<style lang="scss" scoped>
    @import "../../../../css/variables";

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

    .form-control {
        font-size: 12px;
    }

    .form-group {
        margin-bottom: 0;
    }

    .tab-active {
        background-color: $blue !important;
    }

    .tabs {
        margin-bottom: 20px;

        & > span > a {
            color:black;
            margin:auto;
            background-color: #f0f0f0;
            border: 1px solid black;
            padding: 5px 16px;
            text-align: center;
            text-decoration: none;
            font-size: 12px;
            display: inline-block;
        }
    }

    .buttons {
        display: block;
        text-align: center;
        margin-bottom: 20px;
    }

    button {
        background-color: $blue;
        border: 1px solid black;
        color: white;
        padding: 10px 32px;
        text-align: center;
        text-decoration: none;
        font-size: 16px;
        display: block;
        margin: 20px auto;
    }

    td {
        border-right: 1px dashed white;
    }

    .th-market {
        width: 108px;
    }

    .th-indicator {
        width: 180px;
    }

    .th-technology {
        width: 160px;
    }

    .th-year {
        width: 85px;
        white-space: nowrap;
    }

    .market {
        width: 100px;
    }

    .indicator {
        width: 180px;
    }

    .technology {
        width: 160px;
    }

    dt {
        width: 35px;
    }

    dd {
        margin-left: 45px;
    }
</style>
