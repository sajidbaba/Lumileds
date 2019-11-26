<script>
    import { Bar, mixins } from 'vue-chartjs';

    export default {
        extends: Bar,
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
        }
    }
</script>
