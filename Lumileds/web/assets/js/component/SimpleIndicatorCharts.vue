<template>
    <div>
        <div class="row">
            <div class="col-xs-12 text-right">
                <button type="button" class="btn btn-default" @click="graphMode = !graphMode">Table / chart mode</button>
            </div>
        </div>

        <div v-if="graphMode" class="row">
            <div class="col-xs-4">
                <bar-chart :chartData="chartData[1]" :options="chartOptions[1]" />
                <vue-element-loading :active="isLoadingCharts[1]" spinner="bar-fade-scale" color="#FF6700"/>
            </div>

            <div class="col-xs-4">
                <bar-chart :chartData="chartData[32]" :options="chartOptions[32]" />
                <vue-element-loading :active="isLoadingCharts[32]" spinner="bar-fade-scale" color="#FF6700"/>
            </div>

            <div class="col-xs-4">
                <bar-chart :chartData="chartData[34]" :options="chartOptions[34]" />
                <vue-element-loading :active="isLoadingCharts[34]" spinner="bar-fade-scale" color="#FF6700"/>
            </div>
        </div>

        <div v-else class="row">
            <div class="col-xs-8 col-xs-offset-2">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th></th>
                            <th v-for="hLabel in tableHeader">{{ hLabel }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="row in tableData">
                            <template v-if="Object.keys(row).length">
                                <th nowrap>{{ row.vLabels[0] }}</th>
                                <td v-for="cell in row.cells['main']">
                                    {{ cell }}
                                </td>
                            </template>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import BarChart from './reporting/BarChart';
    import VueElementLoading from './../libs/vue-element-loading.min';
    import UrlResolver from '../services/UrlResolver';

    export default {
        components: { BarChart, VueElementLoading },
        props: {
            segment: {
                type: String,
                default: null
            },
            regions: {
                type: Array,
                default: null
            },
            markets: {
                type: Array,
                default: null
            },
            technologies: {
                type: Array,
                default: null
            },
            affectedCells: {
                type: Array,
                default: null
            },
        },
        data () {
            return {
                graphMode: true,
                isLoadingCharts: {
                    1: false,
                    32: false,
                    34: false
                },
                chartData: {
                    1: {},
                    32: {},
                    34: {}
                },
                tableData: {
                    1: {},
                    32: {},
                    34: {}
                },
                chartOptions: {
                    1: {
                        responsive: true,
                        title: {
                            display: true,
                            text: 'Parc'
                        },
                        legend: {
                            display: false
                        },
                        scales: {
                            yAxes: [{
                                stacked: true,
                                ticks: {
                                    callback: function(value) {
                                        return value;
                                    }
                                }
                            }],
                            xAxes: [ {
                                stacked: true,
                                categoryPercentage: 0.5,
                                barPercentage: 1
                            }]
                        }
                    },
                    32: {
                        responsive: true,
                        title: {
                            display: true,
                            text: 'Market Volume'
                        },
                        legend: {
                            display: false
                        },
                        scales: {
                            yAxes: [{
                                stacked: true,
                                ticks: {
                                    callback: function(value) {
                                        return value;
                                    }
                                }
                            }],
                            xAxes: [ {
                                stacked: true,
                                categoryPercentage: 0.5,
                                barPercentage: 1
                            }]
                        }
                    },
                    34: {
                        responsive: true,
                        title: {
                            display: true,
                            text: 'Market Value USD'
                        },
                        legend: {
                            display: false
                        },
                        scales: {
                            yAxes: [{
                                stacked: true,
                                ticks: {
                                    callback: function(value) {
                                        return value;
                                    }
                                }
                            }],
                            xAxes: [ {
                                stacked: true,
                                categoryPercentage: 0.5,
                                barPercentage: 1
                            }]
                        }
                    }
                }
            }
        },
        computed: {
            tableHeader () {
                return [...new Set(
                    [].concat(
                        this.tableData[1].hLabels || [],
                        this.tableData[32].hLabels || [],
                        this.tableData[34].hLabels || [],
                    )
                )].sort()
            }
        },
        methods: {
            toggleLoadingCharts (state) {
                this.isLoadingCharts[1] = state;
                this.isLoadingCharts[32] = state;
                this.isLoadingCharts[34] = state;
            },
            loadCharts () {
                let urlResolver = new UrlResolver;

                this.requestChart(urlResolver.getSimpleIndicatorChart(), 1);
                this.requestChart(urlResolver.getSimpleIndicatorChart(), 32);
                this.requestChart(urlResolver.getSimpleIndicatorChart(), 34);
            },
            requestChart (url, indicator) {
                this.isLoadingCharts[indicator] = true;

                let body = {
                    indicator: indicator,
                    segment: this.segment,
                    regions: this.regions,
                    markets: this.markets,
                    technologies: indicator === 1 ? null : this.technologies,
                    affectedCells: this.affectedCells,
                };

                this.$http.post(url, body).then((response) => {
                    this.chartData[indicator] = response.body.chart;
                    this.tableData[indicator] = response.body.table;

                    this.isLoadingCharts[indicator] = false;
                }, response => {
                    this.$toasted.error('Something went wrong: ' + response.statusText, {
                        duration: 1500
                    });

                    this.isLoadingCharts[indicator] = false;
                });
            }
        }
    }
</script>
