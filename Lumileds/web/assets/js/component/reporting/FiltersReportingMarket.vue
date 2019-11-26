<template>
    <div class="col-xs-2">
        <div class="market"> {{ market.name }} </div>
        <div class="region-checkbox">
            <input
                    type="checkbox"
                    @change="(e) => $emit('change', { marketId: market.id, checked: e.target.checked })"
                    :checked="isChecked"
                    :value="market.id"
            />
        </div>
    </div>
</template>

<style scoped>
    .market {
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
    }
</style>

<script>
    export default {
        name: "filters-reporting-market",
        props: {
            market: {
                type: Object,
                required: true
            },
            checkedMarkets: {
                type: Array,
                required: true
            },
        },
        computed: {
            isChecked () {
                return this.checkedMarkets.find((checkedMarket) => checkedMarket == this.market.id) !== undefined;
            }
        }
    }
</script>
