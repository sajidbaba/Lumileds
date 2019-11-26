<template>
    <div class="row">
        <div class="col-xs-5">
            <div class="years-block">
                <div><b>Years:</b></div>

                <div class="year-block" v-for="year in years">
                    <div class="year"> {{ year.year }} </div>
                    <div class="year-checkbox"> <input @change="filter" v-model="checkedYears" type="checkbox" :value="year.year"/> </div>
                </div>
            </div>

            <div class="segments-block">
                <div><b>Segments:</b></div>

                <div class="segment-block">
                    <div class="segment-name">LV</div>
                    <div class="segment-checkbox"> <input @change="filter" v-model="checkedSegments" type="checkbox" value="LV"/></div>
                </div>

                <div class="segment-block">
                    <div class="segment-name">HV</div>
                    <div class="segment-checkbox"> <input @change="filter" v-model="checkedSegments" type="checkbox" value="HV"/></div>
                </div>

                <div class="segment-block">
                    <div class="segment-name">2W</div>
                    <div class="segment-checkbox"><input @change="filter" v-model="checkedSegments" type="checkbox" value="2W"/></div>
                </div>
            </div>

            <div class="regions-block">
                <div><b>Regions:</b></div>

                <div class="region-block" v-for="region in regions">
                    <div class="region"> {{ region.name }} </div>
                    <div class="region-checkbox"> <input @change="changeRegion(region)" v-model="checkedRegions" type="checkbox" :value="region.id"/> </div>
                </div>
            </div>

            <div class="technologies-block">
                <div><b>Technologies:</b></div>

                <select v-model="technologySet" @change="filter" class="form-control">
                    <option :value="null">All</option>
                    <option value="hl">Headlighting</option>
                    <option value="sl">Signaling</option>
                    <option value="lamps">Lamps</option>
                    <option value="led">LED</option>
                </select>
            </div>
        </div>

        <div class="col-xs-7">
            <div class="markets-block">
                <div><b>Countries:</b></div>

                <div class="row" style="margin-top: 10px; margin-bottom: 10px;">
                    <div class="col-xs-12">
                        <button class="btn btn-default" type="button" @click="selectAllMarkets">Select all</button>
                        <button class="btn btn-default" type="button" @click="selectNoneMarkets">Select none</button>
                    </div>
                </div>

                <div class="row">
                    <FiltersReportingMarket
                            v-for="market in markets"
                            :key="market.id"
                            :market="market"
                            :checkedMarkets="checkedMarkets"
                            @change="changeMarket"
                    />
                </div>

                <template v-if="!markets.length">
                    <i>No countries are available</i>
                </template>
            </div>
        </div>
    </div>
</template>

<style scoped>
    .years-block, .segments-block, .regions-block, .technologies-block, .markets-block {
        padding: 10px;
    }

    .markets-block {
        width: 100%;
        max-height: 360px;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .year-block, .segment-block {
        display: inline-block;
        width: 50px;
    }

    .region-block {
        display: inline-block;
        width: 80px;
    }

    .markets-block {
        display:inline-block;
        margin-right: 10px;
    }

    .market {
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
    }
</style>

<script>
    import UrlResolver from '../../services/UrlResolver';
    import FiltersReportingMarket from './FiltersReportingMarket';

    export default {
        name: "filters-reporting",
        components: { FiltersReportingMarket },
        props: {
            savedFilter: {
                type: Object,
                required: false,
                default: null
            }
        },
        mounted: function () {
            this.init();
        },
        data() {
            return {
                years: {},
                regions: {},
                markets: [],
                allMarkets: [],
                emptyFilters: true,
                checkedSegments: ['LV', 'HV', '2W'],
                checkedRegions: [],
                checkedYears: [],
                checkedMarkets: [],
                technologySet: null
            }
        },
        methods: {
            init () {
                this.urlResolver = new UrlResolver();

                if (this.savedFilter && this.savedFilter.segments) {
                    this.checkedSegments = this.savedFilter.segments;
                }

                if (this.savedFilter && this.savedFilter.technologySet) {
                    this.technologySet = this.savedFilter.technologySet;
                }

                Promise.all([
                    this.loadYears(),
                    this.loadRegions(),
                    this.loadMarkets()
                ]).then(() => {
                    this.recheckMarkets();
                    this.filter();
                })
            },
            loadYears () {
                let url = this.urlResolver.getYears();

                return this.$http.get(url).then((response) => {
                    this.years = response.body;

                    if (this.savedFilter && this.savedFilter.years) {
                        this.checkedYears = this.savedFilter.years;
                    } else {
                        this.checkedYears = response.body.map((value) => value.year );
                    }
                }, response => {
                    this.$toasted.error('Something went wrong: ' + response.statusText, {
                        duration: 1500
                    });
                });
            },
            loadRegions () {
                let url = this.urlResolver.getRegions();

                return this.$http.get(url).then((response) => {
                    this.regions = response.body;

                    if (this.savedFilter && this.savedFilter.regions) {
                        this.checkedRegions = this.savedFilter.regions;
                    } else {
                        this.checkedRegions = response.body.map((value) => value.id);
                    }
                }, response => {
                    this.$toasted.error('Something went wrong: ' + response.statusText, {
                        duration: 1500
                    });
                });
            },
            loadMarkets () {
                let url = this.urlResolver.getMarkets();

                return this.$http.get(url).then((response) => {
                    if (!!response.body) {
                        this.allMarkets = response.body;
                        this.markets = response.body;

                        if (this.savedFilter && this.savedFilter.markets) {
                            this.checkedMarkets = this.savedFilter.markets;
                        } else {
                            this.checkedMarkets = this.markets.map((market) => market.id)
                        }
                    }
                }, (response) => {
                    this.$toasted.error('Something went wrong: ' + response.statusText, {
                        duration: 1500
                    });
                });
            },
            filter () {
                this.$emit('filter', {
                    segments: this.checkedSegments,
                    years: this.checkedYears,
                    markets: this.checkedMarkets,
                    regions: this.checkedRegions,
                    technologySet: this.technologySet,
                });
            },
            changeRegion (region) {
                let checked = this.checkedRegions.indexOf(region.id) !== -1;

                if (checked) {
                    let marketsToAdd = this.allMarkets.filter((market) => parseInt(market.region_id) === parseInt(region.id));

                    marketsToAdd.forEach((marketToAdd) => {
                        this.markets.push(marketToAdd);
                        this.checkedMarkets.push(marketToAdd.id);
                    });
                } else {
                    this.removeMarketsByRegion(region)
                }

                this.filter()
            },
            selectAllMarkets () {
                this.checkedMarkets = this.markets.map((market) => market.id);
                this.filter()
            },
            selectNoneMarkets () {
                this.checkedMarkets = [];
                this.filter()
            },
            changeMarket ({ marketId, checked }) {
                if (checked) {
                    // add to array
                    this.checkedMarkets.push(marketId);
                } else {
                    // remove from array
                    this.checkedMarkets = this.checkedMarkets.filter((market) => parseInt(market) !== parseInt(marketId))
                }

                this.filter()
            },
            recheckMarkets () {
                let inactiveRegions = Object.values(this.regions).filter((region) => {
                    let preparedRegions = this.checkedRegions.map((checkedRegions) => checkedRegions.toString());

                    return !preparedRegions.includes(region.id.toString())
                });

                for (let region of inactiveRegions) {
                    this.removeMarketsByRegion(region)
                }
            },
            removeMarketsByRegion (region) {
                let marketsToRemove = this.allMarkets.filter((market) => parseInt(market.region_id) === parseInt(region.id));
                marketsToRemove = marketsToRemove.map((marketsToRemove) => marketsToRemove.id);

                this.markets = this.markets.filter((market) => marketsToRemove.indexOf(market.id) === -1);
                this.checkedMarkets = this.checkedMarkets.filter((marketId) => {
                    return marketsToRemove.find((marketToRemove) => parseInt(marketToRemove) === parseInt(marketId)) === undefined;
                });
            }
        }
    }
</script>
