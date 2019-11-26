<template>
    <bar-chart :chartData="chartData[1]" :options="chartOptions.parc" />
</template>

<script>
    export default {
        props: ['tableData']
    }
</script>

<style scoped>
    .table-responsive {
        margin-top: 30px;
    }
</style>
