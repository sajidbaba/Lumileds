<script>
    import { Line, mixins } from 'vue-chartjs';

    export default {
        extends: Line,
        data: function() {
            return {
                filters: {}
            }
        },
        mixins: [mixins.reactiveProp],
        props: ['chartData', 'options'],
        mounted: function () {
            this.init();
        },
        methods: {
            init: function () {
                this.renderChart(this.chartData, this.options);
            },
            generateLegend: function () {
                let legend = this.$data._chart.generateLegend();
                this.$emit('generateLegend', legend);
            }
        },
        watch: {
            'chartData': {
                handler: function handler() {
                    this.generateLegend();
                }
            }
        }
    }
</script>
