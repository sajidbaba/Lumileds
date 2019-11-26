<template>
    <div class="table-responsive">
        <table class="table" v-if="tableData">
            <thead>
            <tr>
                <th><!--Vertical Labels--></th>
                <th v-for="hLabel in tableData.hLabels">{{ hLabel }}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(row, key, index) in tableData.cells">
                <th nowrap>{{ tableData.vLabels[index] }}</th>
                <td v-for="cell in row">
                    {{ cell }}
                </td>
            </tr>
            </tbody>
        </table>
    </div>
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
