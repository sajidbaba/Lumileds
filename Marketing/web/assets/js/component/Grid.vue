<template>
    <div>
        <div class="container">
            <div class="row">
                <div class="col-sm-offset-4 col-sm-4">
                    <div class="row">
                        <div v-if="emptyFilters" class="alert alert-danger col-sm-offset-2 col-sm-8" role="alert">
                            Please pick at least 1 market or 1 indicator
                        </div>
                    </div>

                    <div class="row">
                        <div v-if="onlyRegionIsSelected" class="alert alert-danger col-sm-offset-2 col-sm-8" role="alert">
                            Please select a country or indicator
                        </div>
                    </div>

                    <div class="row">
                        <div v-if="emptyResponse" class="alert alert-danger col-sm-offset-2 col-sm-8" role="alert">
                            No data for your filter
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--<div class="container">
            <SimpleIndicatorCharts
                    ref="simpleIndicatorCharts"
                    :segment="filters.segment"
                    :regions="filters.regions"
                    :markets="filters.markets"
                    :technologies="filters.technologies"
                    :affectedCells="affectedCells"
            />
        </div>-->

        <Filters
                ref="filters"
                :regions="regions"
                :markets="markets"
                :indicators="indicators"
                :technologies="technologies"
                :saved-filter="savedFilter"
                @filterCells="filterCells"
                @sortBySegment="sortBySegment"
        />

        <div class="header">
            <table v-show="rows.length" ref="table">
                <thead>
                <tr>
                    <th class="th-segment">Segment</th>
                    <th class="th-market">Market</th>
                    <th class="th-indicator">Indicator</th>
                    <th class="th-technology">Technology</th>
                    <th class="th-year" v-for="year in years">{{ year }}</th>
                </tr>
                </thead>
            </table>
        </div>

        <table v-show="rows.length" ref="table">
            <div class="data">
                <tbody>
                <Row
                        v-for="(row, index) in rows"
                        :row="row"
                        :key="row.key"
                        :even="index % 2 == 0"
                        :trackedCells="trackedCells"
                        @updateCells="updateCells"
                />
                </tbody>
            </div>
        </table>
    </div>
</template>

<script>
    import Row from './Row.vue'
    import Filters from './Filters.vue';
    import SimpleIndicatorCharts from './SimpleIndicatorCharts.vue';
    import UrlResolver from '../services/UrlResolver';

    export default {
        components: { Row, Filters, SimpleIndicatorCharts },
        props: {
            trackedCells: Object,
            rows: Array,
            years: Array,
            affectedCells: Array,
            emptyResponse: Boolean,
            savedFilter: {
                type: Object,
                required: false,
                default: null
            }
        },
        data() {
            return {
                regions: [],
                markets: [],
                indicators: [],
                technologies: [],
                filters: {},
                emptyFilters: true,
                onlyRegionIsSelected: false,
                segment: null
            }
        },
        mounted: function () {
            this.init();
        },
        methods: {
            init: function () {
                this.urlResolver = new UrlResolver;
                this.loadFilters();
            },
            loadFilters: function() {
                this.getRegionsFromApi();
                this.getMarketsFromApi();
                this.getIndicatorsFromApi();
                this.getTechnologiesFromApi();
            },
            getIndicatorsFromApi() {
                let url = this.urlResolver.getIndicators();

                this.$http.get(url).then((response) => {
                    if (!!response.body) {
                        for (let key in response.body) {
                            this.indicators.push({
                                    value: key,
                                    label: response.body[key].name
                                }
                            )
                        }

                        this.$refs.filters.indicatorsLoaded()
                    }
                }, (response) => {
                    this.$toasted.error('Something went wrong: ' + response.statusText, {
                        duration: 1500
                    });
                });
            },
            getRegionsFromApi() {
                let url = this.urlResolver.getRegions();

                this.$http.get(url).then((response) => {
                    if (!!response.body) {
                        for (let key in response.body) {
                            this.regions.push({
                                    value: response.body[key].id,
                                    label: response.body[key].name,
                                    countries: response.body[key].countries
                                }
                            )
                        }

                        this.$refs.filters.regionsLoaded()
                    }
                }, (response) => {
                    this.$toasted.error('Something went wrong: ' + response.statusText, {
                        duration: 1500
                    });
                });
            },
            getMarketsFromApi() {
                let url = this.urlResolver.getMarkets();

                this.$http.get(url).then((response) => {
                    if (!!response.body) {
                        for (let key in response.body) {
                            this.markets.push({
                                value: response.body[key].id,
                                label: response.body[key].name
                            })
                        }

                        this.$refs.filters.marketsLoaded()
                    }
                }, (response) => {
                    this.$toasted.error('Something went wrong: ' + response.statusText, {
                        duration: 1500
                    });
                });
            },
            getTechnologiesFromApi() {
                let url = this.urlResolver.getTechnologies();

                this.$http.get(url).then((response) => {
                    if (!!response.body) {
                        for (let key in response.body) {
                            this.technologies.push({
                                    value: response.body[key].id,
                                    label: response.body[key].name
                                }
                            )
                        }

                        this.$refs.filters.technologiesLoaded()
                    }
                }, (response) => {
                    this.$toasted.error('Something went wrong: ' + response.statusText, {
                        duration: 1500
                    });
                });
            },
            sortBySegment(segment) {
                this.segment = segment;
                this.filters = Object.assign({}, this.filters, { segment: segment });

                setTimeout(() => {
                    // this.$refs.simpleIndicatorCharts.loadCharts();
                }, 0);

                if (this.isEmptyFilters()) return;

                this.emitFilterCells();
            },
            filterCells(filters) {
                this.onlyRegionIsSelected = false;
                this.emptyFilters = false;

                this.filters = Object.assign(this.filters, filters);
                this.filters.segment = this.segment;

                setTimeout(() => {
                    // this.$refs.simpleIndicatorCharts.loadCharts();
                }, 0);

                if (this.isEmptyFilters()) {
                    this.emptyFilters = true;
                    this.$emit('clearTable');
                    return;
                }

                if (filters.regions.length > 0 && filters.markets.length === 0 && filters.indicators.length === 0 && filters.technologies.length === 0) {
                    this.onlyRegionIsSelected = true;
                    this.$emit('clearTable');
                    return;
                }

                this.emitFilterCells();
            },
            emitFilterCells() {
                this.$emit('filterCells', this.filters)
            },
            updateCells() {
                this.$emit('updateCells');
            },
            isEmptyFilters() {
                let filters = this.filters;

                return filters.length === 0 ||
                    filters.regions === undefined &&
                    filters.markets === undefined &&
                    filters.indicators === undefined &&
                    filters.technologies === undefined ||
                    filters.hasOwnProperty('regions') && filters.regions.length === 0 &&
                    filters.hasOwnProperty('markets') && filters.markets.length === 0 &&
                    filters.hasOwnProperty('indicators') && filters.indicators.length === 0 &&
                    filters.hasOwnProperty('technologies') && filters.technologies.length === 0;
            }
        }
    }
</script>

<style lang="scss" scoped>
    @import "../../css/variables.scss";

    .tab-active {
        background-color: $blue !important;
    }
    .tabs {
        display: block;
        margin: auto;
        width: 170px;
    }
    .tabs > span > a {
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
    table {
        border: 1px solid black;
        background-color: #f0f0f0;
        font-size: 13px;
        margin: auto;
    }
    th {
        cursor: pointer;
        color: #5b5b5b;
        background-color: #e0e0e0;
        padding:10px;
        border-bottom: 1px solid black;
    }
    .alert-danger {
        margin-top: 15px;
        text-align: center;
    }
    .header {
        margin-left: -17px;
    }
    .data {
        max-height: calc(100vh - 500px);
        overflow-y: scroll;
    }
    .th-segment {
        width: 80px;
    }
    .th-indicator {
        width: 200px;
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
    dt {
        width: 35px;
    }
    dd {
        margin-left: 45px;
    }
</style>
