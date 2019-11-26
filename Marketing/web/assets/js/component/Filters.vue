<template>
    <div class="filters row">
        <div class="container">
            <div class="col-sm-2">
                <v-select
                        v-model="checkedSegment"
                        :options="segments"
                        :on-change="(segment) => $emit('sortBySegment', segment === 'All' ? null : segment)"
                        :get-option-label="(option) => option ? option : 'All'"
                        placeholder="Filter By Segment"
                        :clearable="false"
                />
            </div>

            <div class="col-sm-2">
                <v-select
                        multiple
                        v-model="checkedRegions"
                        :options="regions"
                        :on-change="handler"
                        placeholder="Filter By Region(s)"
                />
            </div>

            <div class="col-sm-2">
                <v-select
                        multiple
                        v-model="checkedMarkets"
                        :options="marketsArray"
                        :on-change="emitSortEvent"
                        placeholder="Filter By Market(s)"
                />
            </div>

            <div class="col-sm-3">
                <v-select
                        multiple
                        v-model="checkedIndicators"
                        :options="indicators"
                        :on-change="emitSortEvent"
                        placeholder="Filter By Indicator(s)"
                />
            </div>

            <div class="col-sm-3">
                <v-select
                        multiple
                        v-model="checkedTechnologies"
                        :options="technologies"
                        :on-change="emitSortEvent"
                        placeholder="Filter By Technology(ies)"
                />
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data () {
            return {
                showRegionList: false,
                showMarketList: false,
                showIndicatorList: false,
                showTechnologyList: false,
                checkedSegment: 'All',
                checkedRegions: null,
                checkedMarkets: null,
                checkedIndicators: null,
                checkedTechnologies: null,
                filteredMarkets: [],
                marketsArray: [],
                regionsFilter: [],
                marketsFilter: [],
                indicatorsFilter: [],
                technologiesFilter: [],
                segments: ['All', 'LV', 'HV', '2W']
            }
        },
        props: {
            regions: Array,
            indicators: Array,
            markets: Array,
            technologies: Array,
            savedFilter: {
                type: Object,
                required: false,
                default: null
            }
        },
        created () {
            if (this.savedFilter && this.savedFilter.segment) {
                this.checkedSegment = this.savedFilter.segment
            }
        },
        methods: {
            emitSortEvent () {
                this.regionsFilter = [];
                this.marketsFilter = [];
                this.indicatorsFilter = [];
                this.technologiesFilter = [];

                // prepare region filter parameters for API
                if (this.checkedRegions && this.checkedRegions.length) {
                    for (let key in this.checkedRegions) {
                        this.regionsFilter = this.regionsFilter.concat(this.checkedRegions[key].value);
                    }
                }

                // prepare market filter parameters for API
                if (this.checkedMarkets && this.checkedMarkets.length) {
                    for (let key in this.checkedMarkets) {
                        this.marketsFilter = this.marketsFilter.concat(this.checkedMarkets[key].value);
                    }
                }

                // prepare indicator filter parameters for API
                if (this.checkedIndicators && this.checkedIndicators.length) {
                    for (let key in this.checkedIndicators) {
                        this.indicatorsFilter = this.indicatorsFilter.concat(this.checkedIndicators[key].value);
                    }
                }
                // prepare indicator filter parameters for API
                if (this.checkedTechnologies && this.checkedTechnologies.length) {
                    for (let key in this.checkedTechnologies) {
                        this.technologiesFilter = this.technologiesFilter.concat(this.checkedTechnologies[key].value);
                    }
                }

                let filters = {
                    regions: this.regionsFilter,
                    markets: this.marketsFilter,
                    indicators: this.indicatorsFilter,
                    technologies: this.technologiesFilter
                };
                this.$emit('filterCells', filters);

                this.marketsArray = [];

                // create markets list
                if (this.regionsFilter.length) {
                    for (let key in this.regions) {
                        if (this.regionsFilter.includes(this.regions[key].value)) {
                            for (let index in this.regions[key].countries) {
                                this.marketsArray.push({
                                        value: this.regions[key].countries[index].id,
                                        label: this.regions[key].countries[index].name
                                    }
                                )
                            }
                        }
                    }
                } else {
                    this.marketsArray = this.markets;
                }
            },
            handler () {
                this.checkedMarkets = [];
                this.emitSortEvent();
            },
            marketsLoaded () {
                this.marketsArray = this.markets;

                if (this.savedFilter && this.savedFilter.markets) {
                    this.checkedMarkets = this.markets.filter((market) => this.savedFilter.markets.includes(market.value.toString()))
                }
            },
            regionsLoaded () {
                if (this.savedFilter && this.savedFilter.regions) {
                    this.checkedRegions = this.regions.filter((region) => this.savedFilter.regions.includes(region.value.toString()))
                }
            },
            indicatorsLoaded () {
                if (this.savedFilter && this.savedFilter.indicators) {
                    this.checkedIndicators = this.indicators.filter((indicator) => this.savedFilter.indicators.includes(indicator.value.toString()))
                }
            },
            technologiesLoaded () {
                if (this.savedFilter && this.savedFilter.technologies) {
                    this.checkedTechnologies = this.technologies.filter((technology) => this.savedFilter.technologies.includes(technology.value.toString()))
                }
            }
        }
    }
</script>

<style lang="scss" scoped>
    .filters {
        margin-top: 10px;
        margin-bottom: 10px;
    }
</style>
