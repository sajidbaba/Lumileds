<template>
    <div class="container reporting-container">
        <filters-reporting :saved-filter="savedFilter" @filter="setFilters" />

        <ul class="nav nav-tabs">
            <li :class="currentTab === 1 ? 'active' : ''">
                <a @click="changeChart(1)" href="javascript:void(0);">Parc</a>
            </li>

            <!--<li :class="currentTab === 2 ? 'active' : ''">-->
                <!--<a @click="changeChart(2)" href="javascript:void(0);">Parc split by technology share</a>-->
            <!--</li>-->

            <li :class="currentTab === 3 ? 'active' : ''">
                <a @click="changeChart(3)" href="javascript:void(0);">Market volume & size by region</a>
            </li>

            <li :class="currentTab === 4 ? 'active' : ''">
                <a @click="changeChart(4)" href="javascript:void(0);">Market volume & size by segment</a>
            </li>

            <li :class="currentTab === 5 ? 'active' : ''">
                <a @click="changeChart(5)" href="javascript:void(0);">Market volume & size by technology</a>
            </li>

            <li :class="currentTab === 6 ? 'active' : ''">
                <a @click="changeChart(6)" href="javascript:void(0);">Market share</a>
            </li>
        </ul>

        <div class="row" v-if="currentTab === 1">
            <h5 class="tab-header">Parc (in million vehicles)</h5>

            <div class="col-md-6">
                <bar-chart :chartData="chartData.first" :options="options.parcBySegment" />
            </div>

            <div class="col-md-6">
                <bar-chart :chartData="chartData.second" :options="options.parcByRegion" />
            </div>
        </div>

        <div class="row" v-if="currentTab === 2">
            <h5 class="tab-header">Parc split by technology share (%)</h5>
            <div v-html="legend"></div>

            <line-chart
                    :chartData="chartData.first"
                    :options="options.parcByTechnology"
                    :width="1140"
                    @generateLegend="setLegend"
            />
        </div>

        <div class="row" v-if="currentTab === 3">
            <h5 class="tab-header">Market volume & size by region (in million units or mUSD)</h5>

            <div class="col-md-6">
                <bar-chart :chartData="chartData.first" :options="options.marketVolumeByRegion" />
            </div>

            <div class="col-md-6">
                <bar-chart :chartData="chartData.second" :options="options.marketSizeByRegion" />
            </div>
        </div>

        <div class="row" v-if="currentTab === 4">
            <h5 class="tab-header">Market volume & size by segment (in million units or mUSD)</h5>

            <div class="col-md-6">
                <bar-chart :chartData="chartData.first" :options="options.marketVolumeBySegment" />
            </div>

            <div class="col-md-6">
                <bar-chart :chartData="chartData.second" :options="options.marketSizeBySegment" />
            </div>
        </div>

        <div class="row" v-if="currentTab === 5">
            <h5 class="tab-header">Market volume & size by technology (in million units or mUSD)</h5>

            <div class="col-md-6">
                <bar-chart :chartData="chartData.first" :options="options.marketVolumeByRegion" />
            </div>

            <div class="col-md-6">
                <bar-chart :chartData="chartData.second" :options="options.marketSizeByRegion" />
            </div>
        </div>

        <div class="row" v-if="currentTab === 6">
            <h5 class="tab-header">Market share (%)</h5>

            <div class="col-md-6">
                <bar-chart :chartData="chartData.first" :options="options.marketShareByRegion" />
            </div>

            <div class="col-md-6">
                <bar-chart :chartData="chartData.second" :options="options.marketShareByTechnology" />
            </div>
        </div>

        <div class="row">
            <div :class="[currentTab === 2 ? 'col-md-12' : 'col-md-6']">
                <report-table :tableData="tableData.first" />
            </div>

            <div class="col-md-6" v-if="currentTab !== 2">
                <report-table :tableData="tableData.second" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <button :class="{ disabled: downloading }" :disabled="downloading" @click="exportModel()" class="btn btn-default btn-export">
                    <img src="/assets/img/Spinner.gif">
                    Download
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    import Vue from 'vue';
    import VueResource from 'vue-resource';
    import UrlResolver from '../../services/UrlResolver';
    import BarChart from './BarChart';
    import LineChart from './LineChart';
    import FiltersReporting from "./FiltersReporting";
    import ReportTable from './ReportTable';

    Vue.use(VueResource);

    export default {
        components: { FiltersReporting, BarChart, LineChart, ReportTable },
        props: {
            savedFilter: {
                type: Object,
                required: false,
                default: null
            }
        },
        data: function() {
            return {
                currentTab: 1,
                chartData: {
                    first: {},
                    second: {}
                },
                tableData: {
                    first: {},
                    second: {}
                },
                chartNumber: {
                    first: 1,
                    second: 2
                },
                chartDataCache: {},
                tableDataCache: {},
                chartNumberCache: {},
                filters: {},
                regions: {},
                years: {},
                legend: '',
                downloading: false,
                options: {
                    parcBySegment: {
                        responsive: true,
                        maintainAspectRatio: false,
                        title: {
                            display: true,
                            text: 'Parc by segment'
                        },
                        scales: {
                            yAxes: [{
                                stacked: true,
                                ticks: {
                                    callback: function(value) {
                                        return value;
                                    }
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Million Units'
                                }
                            }],
                            xAxes: [ {
                                stacked: true,
                                categoryPercentage: 0.5,
                                barPercentage: 1
                            }]
                        }
                    },
                    parcByRegion: {
                        responsive: true,
                        maintainAspectRatio: false,
                        title: {
                            display: true,
                            text: 'Parc by region'
                        },
                        scales: {
                            yAxes: [{
                                stacked: true,
                                ticks: {
                                    callback: function(value) {
                                        return value;
                                    }
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Million Units'
                                }
                            }],
                            xAxes: [{
                                stacked: true,
                                categoryPercentage: 0.5,
                                barPercentage: 1
                            }]
                        }
                    },
                    parcByTechnology: {
                        responsive: false,
                        legend: {
                            display: false
                        },
                        legendCallback: function(chart) {
                            let text = [];
                            text.push('<ul class="chart-3-legend">');
                            for (let i = 0; i < chart.data.datasets.length; i++) {
                                text.push('<li>');
                                text.push('<span style="background-color:' + chart.data.datasets[i].borderColor + '">');
                                text.push('</span>');
                                text.push(chart.data.datasets[i].label);
                                text.push('</li>');
                            }
                            text.push('</ul>');
                            return text.join("");
                        },
                        scales: {
                            yAxes: [{
                                ticks: {
                                    min: 0,
                                    max: 100,
                                    callback: function(value) {
                                        return value + "%"
                                    }
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Million Units'
                                }
                            }]
                        }
                    },
                    marketVolumeByRegion: {
                        responsive: true,
                        maintainAspectRatio: false,
                        title: {
                            display: true,
                            text: 'Market volume'
                        },
                        scales: {
                            yAxes: [{
                                stacked: true,
                                ticks: {
                                    callback: function(value) {
                                        return value;
                                    }
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Million Units'
                                }
                            }],
                            xAxes: [{
                                stacked: true,
                                barPercentage: 1,
                                categoryPercentage: 0.8
                            }]
                        }
                    },
                    marketSizeByRegion: {
                        responsive: true,
                        maintainAspectRatio: false,
                        title: {
                            display: true,
                            text: 'Market size'
                        },
                        scales: {
                            yAxes: [{
                                stacked: true,
                                ticks: {
                                    callback: function(value) {
                                        return value;
                                    }
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Million USD'
                                }
                            }],
                            xAxes: [{
                                stacked: true,
                                barPercentage: 1,
                                categoryPercentage: 0.8
                            }]
                        }
                    },
                    marketVolumeBySegment: {
                        responsive: true,
                        maintainAspectRatio: false,
                        title: {
                            display: true,
                            text: 'Market volume'
                        },
                        scales: {
                            yAxes: [{
                                stacked: true,
                                ticks: {
                                    callback: function(value) {
                                        return value;
                                    }
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Million Units'
                                }
                            }],
                            xAxes: [{
                                stacked: true,
                                barPercentage: 1,
                                categoryPercentage: 0.8
                            }]
                        }
                    },
                    marketSizeBySegment:  {
                        responsive: true,
                        maintainAspectRatio: false,
                        title: {
                            display: true,
                            text: 'Market size'
                        },
                        scales: {
                            yAxes: [{
                                stacked: true,
                                ticks: {
                                    callback: function(value) {
                                        return value;
                                    }
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Million USD'
                                }
                            }],
                            xAxes: [{
                                stacked: true,
                                barPercentage: 1,
                                categoryPercentage: 0.8
                            }]
                        }
                    },
                    marketVolumeByTechnology: {
                        responsive: true,
                        maintainAspectRatio: false,
                        title: {
                            display: true,
                            text: 'Market volume'
                        },
                        scales: {
                            yAxes: [{
                                stacked: true,
                                ticks: {
                                    callback: function(value) {
                                        return value;
                                    }
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Million Units'
                                }
                            }],
                            xAxes: [{
                                stacked: true,
                                barPercentage: 1,
                                categoryPercentage: 0.8
                            }]
                        }
                    },
                    marketSizeByTechnology: {
                        responsive: true,
                        maintainAspectRatio: false,
                        title: {
                            display: true,
                            text: 'Market size'
                        },
                        scales: {
                            yAxes: [{
                                stacked: true,
                                ticks: {
                                    callback: function(value) {
                                        return value;
                                    }
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Million USD'
                                }
                            }],
                            xAxes: [{
                                stacked: true,
                                barPercentage: 1,
                                categoryPercentage: 0.8
                            }]
                        }
                    },
                    marketShareByRegion: {
                        responsive: true,
                        maintainAspectRatio: false,
                        title: {
                            display: true,
                            text: 'Market Share by region'
                        },
                        scales: {
                            xAxes: [{
                                stacked: true,
                                barPercentage: 1,
                                categoryPercentage: 0.8,
                                ticks: {
                                    autoSkip: false,
                                    maxRotation: 15,
                                }
                            }],
                            yAxes: [{
                                stacked: true,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                },
                                scaleLabel: {
                                    display: false
                                }
                            }],
                        },
                        tooltips: {
                            enabled: true,
                            mode: 'single',
                            callbacks: {
                                label: function(tooltipItems, data) {
                                    return data.datasets[tooltipItems.datasetIndex].label + ': ' + tooltipItems.yLabel;
                                }
                            }
                        },
                        legend: {
                            display: true,
                            labels: {
                                filter: (legendItem) => {
                                    return legendItem.text !== 'Competitors';
                                }
                            }
                        },
                    },
                    marketShareByTechnology: {
                        responsive: true,
                        maintainAspectRatio: false,
                        title: {
                            display: true,
                            text: 'Market Share by technology'
                        },
                        scales: {
                            xAxes: [{
                                stacked: true,
                                barPercentage: 1,
                                categoryPercentage: 0.8,
                                ticks: {
                                    autoSkip: false,
                                    maxRotation: 15,
                                }
                            }],
                            yAxes: [{
                                stacked: true,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                },
                                scaleLabel: {
                                    display: false
                                }
                            }],
                        },
                        tooltips: {
                            enabled: true,
                            mode: 'single',
                            callbacks: {
                                label: function(tooltipItems, data) {
                                    return data.datasets[tooltipItems.datasetIndex].label + ': ' + tooltipItems.yLabel;
                                }
                            }
                        },
                        legend: {
                            display: true,
                            labels: {
                                filter: (legendItem) => {
                                    return legendItem.text !== 'Competitors';
                                }
                            }
                        },
                    }
                }
            }
        },
        mounted: function () {
            this.init();
        },
        methods: {
            init: function () {
                this.urlResolver = new UrlResolver();
                this.loadData();
            },
            setFilters: function(filters) {
                this.filters = filters;
                this.clearCache();
                if (this.urlResolver) {
                    this.loadData();
                }
            },
            exportModel: function() {
                let self = this;
                this.downloading = true;

                $.fileDownload(this.urlResolver.exportModel(), {
                    data: { reporting: true },
                    successCallback: function () {
                        self.downloading = false;
                    },
                    failCallback: function () {
                        self.downloading = false;
                    }
                });
            },
            loadData: function () {
                if (this.currentTab === 1) {
                    this.loadParcBySegment();
                    this.loadParcByRegion();
                } else if (this.currentTab === 2) {
                    this.loadParcByTechnology();
                } else if (this.currentTab === 3) {
                    this.loadMarketVolumeByRegion();
                    this.loadMarketSizeByRegion();
                } else if (this.currentTab === 4) {
                    this.loadMarketVolumeBySegment();
                    this.loadMarketSizeBySegment();
                } else if (this.currentTab === 5) {
                    this.loadMarketVolumeByTechnology();
                    this.loadMarketSizeByTechnology();
                } else if (this.currentTab === 6) {
                    this.loadMarketShareByRegion();
                    this.loadMarketShareByTechnology();
                }
            },
            changeChart: function (tab) {
                this.currentTab = tab;
                this.loadData();
            },
            loadParcBySegment: function () {
                let url = this.urlResolver.getParcBySegment();
                this.requestUrl(url, true);
            },
            loadParcByRegion: function() {
                let url = this.urlResolver.getParcByRegion();
                this.requestUrl(url, false);
            },
            loadParcByTechnology: function () {
                let url = this.urlResolver.getParcByTechnologyData();
                this.requestUrl(url, true);
            },
            loadMarketVolumeByRegion: function () {
                let url = this.urlResolver.getMarketVolumeByRegion();
                this.requestUrl(url, true);
            },
            loadMarketSizeByRegion: function () {
                let url = this.urlResolver.getMarketSizeByRegion();
                this.requestUrl(url, false);
            },
            loadMarketVolumeBySegment: function () {
                let url = this.urlResolver.getMarketVolumeBySegment();
                this.requestUrl(url, true);
            },
            loadMarketSizeBySegment: function () {
                let url = this.urlResolver.getMarketSizeBySegment();
                this.requestUrl(url, false);
            },
            loadMarketVolumeByTechnology: function () {
                let url = this.urlResolver.getMarketVolumeByTechnology();
                this.requestUrl(url, true);
            },
            loadMarketSizeByTechnology: function () {
                let url = this.urlResolver.getMarketSizeByTechnology();
                this.requestUrl(url, false);
            },
            loadMarketShareByRegion: function () {
                let url = this.urlResolver.getMarketShareByRegion();
                this.requestUrl(url, true);
            },
            loadMarketShareByTechnology: function () {
                let url = this.urlResolver.getMarketShareByTechnology();
                this.requestUrl(url, false);
            },
            preloadCache: function (url, first) {
                if (this.chartDataCache.hasOwnProperty(url)) {
                    if (first) {
                        this.chartData.first = this.chartDataCache[url];
                        this.tableData.first = this.tableDataCache[url];
                        this.chartNumber.first = this.chartNumberCache[url];
                    } else {
                        this.chartData.second = this.chartDataCache[url];
                        this.tableData.second = this.tableDataCache[url];
                        this.chartNumber.second = this.chartNumberCache[url];
                    }

                    return true;
                }

                return false;
            },
            clearCache: function () {
                this.chartDataCache = {};
                this.tableDataCache = {};
                this.chartNumberCache = {};
            },
            requestUrl: function (url, first) {
                let hasCache = this.preloadCache(url, first);
                if (hasCache) {
                    return;
                }

                this.$http.get(url, { params: this.filters }).then((response) => {
                    let chartData = response.body.chart;
                    let tableData = response.body.table;
                    let chartNumber = parseInt(response.body.number);

                    if (first) {
                        this.chartData.first = chartData;
                        this.tableData.first = tableData;
                        this.chartNumber.first = chartNumber;
                    } else {
                        this.chartData.second = chartData;
                        this.tableData.second = tableData;
                        this.chartNumber.second = chartNumber;
                    }

                    this.chartDataCache[url] = JSON.parse(JSON.stringify(chartData));
                    this.tableDataCache[url] = JSON.parse(JSON.stringify(tableData));
                    this.chartNumberCache[url] = chartNumber;
                }, response => {
                    this.$toasted.error('Something went wrong: ' + response.statusText, {
                        duration: 1500
                    });
                });
            },
            setLegend: function (legend) {
                this.legend = legend;
            }
        }
    }
</script>

<style lang="scss">
    .nav-tabs {
        margin-top: 30px;
        margin-bottom: 30px;
    }

    .btn-export {
        margin-top: 30px;
    }

    ul.chart-3-legend {
        font-size: 12px;
        color: #666;
        list-style-type: none;
        text-align: center;

        li {
            display: inline-block;

            span {
                display: inline-block;
                height: 4px;
                width: 30px;
                vertical-align: middle;
                margin-left: 14px;
                margin-right: 5px;
                margin-bottom: 2px;
            }
        }
    }

    .tab-header {
        text-align: center;
        font-weight: bold;
    }

    .reporting-container {
        margin-bottom: 15px;
    }

    button.btn-export {
        padding: 6px 25px;

        img {
            height: 21px;
            position: absolute;
            margin-left: -23px;
            display: none;
        }

        &.disabled {
            opacity: 0.5;

            img {
                display: inline-block;
            }
        }
    }
</style>
