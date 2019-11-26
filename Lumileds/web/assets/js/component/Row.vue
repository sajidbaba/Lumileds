<template>
    <tr class="tr-top" :class="{even: even}">
        <td class="segment">{{row.segment}}</td>
        <td class="market">{{row.market}}</td>
        <td class="indicator">{{row.indicator}}</td>
        <td class="technology">{{row.technology}}</td>
        <Cell
                v-for="cell in row.cells"
                :cell="cell"
                :key="cell.id"
                :trackedCells="trackedCells"
                @updateCells="updateCells"
        />
    </tr>
</template>

<script>
    import Cell from './Cell.vue'

    export default {
        props: ['row', 'even', 'trackedCells'],
        components: {
            Cell
        },
        methods: {
            updateCells() {
                this.$emit('updateCells');
            }
        }
    }
</script>

<style scoped>
    .even {
        background-color: lightgrey;
    }
    .segment, .market, .indicator, .technology {
        padding-left: 5px;
        white-space: nowrap;
    }
    td {
        border-right: 1px dashed white;
    }
    .segment {
        width: 80px;
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
</style>
