<template>
    <div>
        <div class="container">
            <a
                    @click="exportVersion"
                    :disabled="exportBtnDisabled"
                    class="btn btn-default btn-export"
            >Export</a>
        </div>

        <div class="container">
            <dl class="dl-horizontal pull-right">
                <dt>
                    <span class="label label-default legend read-only-cell">&nbsp;</span>
                </dt>
                <dd>Read-only field</dd>
                <dt>
                    <span class="label label-success legend">&nbsp;</span>
                </dt>
                <dd>Modified field</dd>
            </dl>
        </div>

        <Grid
                :rows="rows"
                :years="years"
                :emptyResponse="emptyResponse"
                @filterCells="loadData"
                @clearTable="clearTable"
        ></Grid>

        <div v-if="loading" class="loading"></div>

        <div v-if="exportAlert" class="col-sm-offset-5 col-sm-2 alert alert-danger text-center export-alert" role="alert">
            <p>
                <span class="glyphicon glyphicon-alert" aria-hidden="true"></span>
                Please wait.
            </p>
            <p>Export may take a few minutes.</p>
        </div>
    </div>
</template>

<style lang="scss" scoped>
    .loading {
        width: 42px;
        height: 42px;
        position: absolute;
        margin-left: auto;
        margin-right: auto;
        top: 50%;
        left: 0;
        right: 0;
        background-image: url('web/assets/img/Spinner.gif');
        display: block;
    }
    .btn-export {
        margin-top: -40px;
        float: right;
    }
    .export-alert {
        position: absolute;
        margin-left: auto;
        margin-right: auto;
        top: 40%;
        left: 0;
        right: 0;
    }
    .legend {
        width: 35px;
        display: inline-block;
    }
    .read-only-cell {
        background-color: #ddd;
    }
</style>

<script>
    import Grid from './Grid.vue';
    import UrlResolver from '../services/UrlResolver';

    export default {
        props: {
            version: {
                type: Number
            }
        },
        data() {
            return {
                rows: [],
                years: [],
                trackedCells: {},
                emptyResponse: false,
                loading: false,
                exportAlert: false,
                exportBtnDisabled: false
            }
        },
        mounted: function () {
            this.init();
        },
        methods: {
            init: function () {
                this.urlResolver = new UrlResolver;
            },
            loadData: function(filters) {
                let url = this.urlResolver.getTableVersion(this.version);

                this.showLoading();

                this.$http.get(url, {params: filters}).then((response) => {
                    if (!!response.body) {
                        if (response.body.rows.length === 0 && response.body.years.length === 0) {
                            this.emptyResponse = true;
                            this.rows = [];
                            this.years = [];
                        } else {
                            this.rows = response.body.rows;
                            this.years = response.body.years;
                        }

                        this.hideLoading();
                    }
                }, (response) => {
                    this.$toasted.error('Something went wrong: ' + response.statusText, {
                        duration: 1500
                    });
                    this.hideLoading();
                });
            },
            clearTable: function () {
                this.emptyResponse = false;
                this.rows = [];
                this.years = [];
            },
            exportVersion: function () {
                let self = this;
                let url = this.urlResolver.exportVersion(this.version);

                this.showLoading();
                this.showExportAlert();
                this.exportBtnDisabled = true;

                $.fileDownload(url, {
                    successCallback: function () {
                        self.hideLoading();
                        self.hideExportAlert();
                        self.exportBtnDisabled = false;
                    },
                    failCallback: function () {
                        self.hideLoading();
                        self.hideExportAlert();
                        self.exportBtnDisabled = false;
                    }
                });
            },
            showLoading: function () {
                this.loading = true;
            },
            hideLoading: function () {
                this.loading = false;
            },
            showExportAlert: function () {
                this.exportAlert = true;
            },
            hideExportAlert: function () {
                this.exportAlert = false;
            }
        },
        components: {
            Grid
        }
    }
</script>
