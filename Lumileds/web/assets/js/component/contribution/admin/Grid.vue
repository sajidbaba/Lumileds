<template>
    <div>
        <div class="row">
            <div class="col-sm-offset-4 col-sm-4 text-center">
                <div class="tabs">
                    <span>
                        <a :class="{ 'tab-active': segment === 'LV' }" @click.prevent="loadBySegment('LV')" href="#">LV</a>
                    </span>

                    <span>
                        <a :class="{ 'tab-active': segment === 'HV' }" @click.prevent="loadBySegment('HV')" href="#">HV</a>
                    </span>

                    <span>
                        <a :class="{ 'tab-active': segment === '2W' }" @click.prevent="loadBySegment('2W')" href="#">2W</a>
                    </span>
                </div>
            </div>

            <div class="col-sm-4">
                <dl class="dl-horizontal">
                    <div class="well well-sm">
                        <div><strong>Note:</strong></div>
                        <div>Alert will display when variation is > 5%</div>
                        <div>Error will display when number is not in the right format</div>
                    </div>

                    <dt>
                        <span class="label label-default legend editable-cell">&nbsp;</span>
                    </dt>
                    <dd>Editable field</dd>
                    <dt>
                        <span class="label label-warning legend">&nbsp;</span>
                    </dt>
                    <dd>Editable field with alert</dd>
                    <dt>
                        <span class="label label-danger legend">&nbsp;</span>
                    </dt>
                    <dd>Editable field with error</dd>
                    <dt>
                        <span class="label label-default legend read-only-cell">&nbsp;</span>
                    </dt>
                    <dd>Read-only field</dd>
                    <dt>
                        <span class="label label-default legend read-only-warning-cell">&nbsp;</span>
                    </dt>
                    <dd>Read-only field with alert</dd>
                </dl>
            </div>
        </div>

        <!--<div class="container">
            <SimpleIndicatorCharts
                    ref="simpleIndicatorCharts"
                    :segment="segment"
                    :affectedCells="affectedCells"
                    :markets="[countryId]"
            />
        </div>-->

        <div class="header">
            <table v-show="rows.length" class="edit-table" ref="table">
                <thead>
                <tr>
                    <th class="th-market">Market</th>
                    <th class="th-indicator">Indicator</th>
                    <th class="th-technology">Technology</th>
                    <th class="th-approved">Approved</th>
                    <th class="th-year" v-for="year in years">{{ year }}</th>
                </tr>
                </thead>
            </table>
        </div>

        <table v-show="rows.length" class="edit-table" ref="table">
            <div class="data">
                <tbody>
                <tr class="tr-top" v-for="(row, index) in rows">
                    <td class="market">{{row.market}}</td>
                    <td class="indicator">{{row.indicator}}</td>
                    <td class="technology">{{row.technology}}</td>
                    <td class="approved text-center">
                        <input type="checkbox"
                               v-model="row.approved"
                               v-if="row.is_contributed_by_contributor"
                        />
                    </td>
                    <Cell
                            v-for="cell in row.cells"
                            :cell="cell"
                            :key="cell.id"
                            :trackedCells="trackedCells"
                            @updateCells="updateCells"
                    />
                </tr>
                </tbody>
            </div>
        </table>
    </div>
</template>

<script>
    import Cell from '../../Cell';
    import SimpleIndicatorCharts from './../../SimpleIndicatorCharts.vue';

    export default {
        components: { Cell, SimpleIndicatorCharts },
        props: {
            trackedCells: Object,
            rows: Array,
            years: Array,
            initialApproveSegment: Boolean,
            affectedCells: Array,
            countryId: Number,
        },
        data() {
            return {
                segment: 'LV',
            }
        },
        mounted () {
            // this.$refs.simpleIndicatorCharts.loadCharts();
        },
        methods: {
            updateCells () {
                this.$emit('updateCells');
            },
            loadBySegment (segment) {
                if (!segment || segment === this.segment) return;

                this.segment = segment;
                this.$emit('loadData', segment);

                setTimeout(() => {
                    // this.$refs.simpleIndicatorCharts.loadCharts();
                }, 0);
            }
        }
    }
</script>

<style lang="scss" scoped>
    @import "../../../../css/variables";

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

    .legend {
        width: 35px;
        display: inline-block;
    }

    .editable-cell {
        background-color: white;
        border: 1px solid black;
    }

    .read-only-cell {
        background-color: #ddd;
    }

    .read-only-warning-cell {
        background-color: $readonly-warning-background-color;
    }

    .header {
        margin-left: -17px;
    }

    .data {
        max-height: calc(100vh - 640px);
        overflow-y: scroll;
    }

    .th-market {
        width: 100px;
    }

    .th-indicator {
        width: 180px;
    }

    .th-technology {
        width: 160px;
    }

    .th-year {
        width: 85px;
    }

    .th-approved {
        width: 85px;
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

    .approved {
        width: 85px;
    }

    dt {
        width: 35px;
    }

    dd {
        margin-left: 45px;
    }
</style>
