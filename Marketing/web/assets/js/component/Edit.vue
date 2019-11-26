<template>
    <div>
        <div class="container">
            <div class="pull-right">
                <div class="well well-sm">
                    <div><strong>Note:</strong></div>
                    <div>Alert will display when variation is > 5%</div>
                    <div>Error will display when number is not in the right format</div>
                </div>

                <dl class="dl-horizontal">
                    <dt>
                        <span class="label label-default legend editable-cell">&nbsp;</span>
                    </dt>
                    <dd>Editable field</dd>
                    <dt>
                        <span class="label label-warning legend">&nbsp;</span>
                    </dt>
                    <dd>Editable field with alert</dd>
                    <dt>
                        <span class="label label-danger legend">&nbsp;</span>
                    </dt>
                    <dd>Editable field with error</dd>
                    <dt>
                        <span class="label label-default legend read-only-cell">&nbsp;</span>
                    </dt>
                    <dd>Read-only field</dd>
                    <dt>
                        <span class="label label-default legend read-only-warning-cell">&nbsp;</span>
                    </dt>
                    <dd>Read-only field with alert</dd>
                </dl>
            </div>
        </div>

        <div v-if="isAdmin" class="buttons">
            <button :disabled="disabledButton" :class="{ calculating: calculating, disabled: disabledButton }" data-toggle="modal" data-target="#versionModal">
                <img src="/assets/img/Spinner.gif">
                Finalize cycle
            </button>

            <button :disabled="disabledButton" :class="{ calculating: calculating, disabled: disabledButton }" @click="saveTable">
                <img src="/assets/img/Spinner.gif">
                Save
            </button>

            <button @click="() => exportModel(false)" :disabled="exportBtnDisabled" class="export">
                Export All
            </button>

            <button @click="() => exportModel(true)" :disabled="exportBtnDisabled" class="export">
                Export
            </button>
        </div>

        <div v-if="exportAlert" class="col-sm-offset-5 col-sm-2 alert alert-danger text-center export-alert" role="alert">
            <p>
                <span class="glyphicon glyphicon-alert" aria-hidden="true"></span>
                Please wait.
            </p>
            <p>Export may take a few minutes.</p>
        </div>

        <div ref="loading" class="loading"></div>
        <Grid
                ref="grid"
                :rows="rows"
                :years="years"
                :tracked-cells="trackedCells"
                :empty-response="emptyResponse"
                :affected-cells="affectedCells"
                :saved-filter="savedFilter"
                @updateCells="sendCellsToApi"
                @filterCells="loadData"
                @clearTable="clearTable"
        />

        <div class="modal fade" tabindex="-1" role="dialog" id="versionModal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="title">Cycle title</label>
                            <input type="text" v-model="versionName" class="form-control" id="title">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button @click="saveTable(true)" type="button" class="btn btn-primary" data-dismiss="modal">Finalize</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    import Grid from './Grid.vue';
    import UrlResolver from '../services/UrlResolver';

    export default {
        components: {
            Grid
        },
        props: {
            isAdmin: {
                type: Boolean
            },
            savedFilter: {
                type: Object,
                required: false,
                default: null
            }
        },
        data() {
            return {
                rows: [],
                years: [],
                trackedCells: {},
                affectedCells: [],
                calculating: false,
                previousRequest: false,
                emptyResponse: false,
                exportAlert: false,
                exportBtnDisabled: false,
                versionName: null
            }
        },
        mounted: function () {
            this.init();
        },
        updated: function () {
            this.$nextTick(function () {
                this.$refs.loading.style.display = "none";
            })
        },
        computed: {
            disabledButton () {
                return this.calculating || !this.affectedCells.length
            }
        },
        methods: {
            init: function () {
                this.urlResolver = new UrlResolver;
            },
            loadData: function(filters) {
                let url = this.urlResolver.getTable();

                this.$refs.loading.style.display = "inline-block";

                this.$http.get(url, {
                    params: filters,
                    before: function(request) {
                        if (this.previousRequest) {
                            this.previousRequest.abort();
                        }

                        this.previousRequest = request;
                    },
                }).then((response) => {
                    if (!!response.body) {
                        if (response.body.rows.length === 0 && response.body.years.length === 0) {
                            this.emptyResponse = true;
                            this.rows = [];
                            this.years = [];
                        } else {
                            this.rows = response.body.rows;
                            this.years = response.body.years;
                        }
                    }
                }, (response) => {
                    if (!this.previousRequest) {
                        this.$refs.loading.style.display = "none";

                        this.$toasted.error('Something went wrong: ' + response.statusText, {
                            duration: 1500
                        });
                    }
                });
            },
            getCellByKey(id) {
                for (let i= 0, lenRows = this.rows.length; i < lenRows; i++) {
                    for (let j = 0, lenCells = this.rows[i].cells.length; j < lenCells; j++) {
                        if (this.rows[i].cells[j].id === id) {
                            return this.rows[i].cells[j];
                        }
                    }
                }
            },
            updateCells: function(cellArray) {
                let self = this;

                if (!this.isAdmin) {
                    return;
                }

                this.affectedCells = cellArray.map((cell) => {
                    return {
                        id: cell.id,
                        value: cell.value
                    }
                });

                for (let key in cellArray) {
                    if (cellArray.hasOwnProperty(key)) {

                        let newCell = cellArray[key];
                        let cell = self.getCellByKey(newCell.id);

                        if (typeof cell === 'undefined') {
                            continue;
                        }

                        if (newCell.hasOwnProperty('error')) {
                            this.$set(cell, 'error', newCell.error);
                            this.$set(cell, 'error_type', newCell.error_type);
                        } else {
                            this.$set(cell, 'error', null);
                            this.$set(cell, 'error_type', null);
                        }

                        cell.value = newCell.value;
                    }
                }
            },
            sendCellsToApi: function() {
                if (!this.isAdmin) {
                    return;
                }

                // this.$refs.grid.$refs.simpleIndicatorCharts.toggleLoadingCharts(true);

                this.calculating = true;
                let url = this.urlResolver.getCalculate();

                this.$http.post(url, this.trackedCells, {
                    before(request) {
                        if (this.previousRequest) {
                            this.previousRequest.abort();
                        }

                        this.previousRequest = request;
                    }
                }).then((response) => {
                    this.calculating = false;

                    if (!!response.body) {
                        this.updateCells(response.body);
                    }
                }, (response) => {
                    this.calculating = false;

                    if (!this.previousRequest) {
                        this.$refs.grid.toggleLoadingCharts(false);

                        this.$toasted.error('Something went wrong: ' + response.statusText, {
                            duration: 1500
                        });
                    }
                })
                    // .then(() => this.$refs.grid.$refs.simpleIndicatorCharts.loadCharts());
            },
            saveTable: function (useVersionName) {
                if (!this.isAdmin) {
                    return;
                }

                this.calculating = true;

                const url = this.urlResolver.getSave();

                this.$http.post(url, { cells: this.trackedCells, versionName: useVersionName ? this.versionName : null }).then((response) => {
                    if (!!response) {
                        this.$toasted.success("Your data has been saved successfully.", {
                            duration: 1500,
                            className: 'toast-success'
                        });
                    }

                    this.calculating = false;
                }, (response) => {
                    this.$toasted.error('Something went wrong: ' + response.statusText, {
                        duration: 1500
                    });

                    this.calculating = false;
                });
            },
            clearTable: function () {
                this.emptyResponse = false;
                this.rows = [];
                this.years = [];
            },
            exportModel: function(withFilter) {
                let url = this.urlResolver.exportModel();
                let self = this;

                this.showExportAlert();
                this.exportBtnDisabled = true;

                $.fileDownload(url, {
                    data: withFilter ? self.$refs.grid.filters : null,
                    successCallback: function () {
                        self.hideExportAlert();
                        self.exportBtnDisabled = false;
                    },
                    failCallback: function () {
                        self.hideExportAlert();
                        self.exportBtnDisabled = false;
                    }
                });
            },
            showExportAlert: function () {
                this.exportAlert = true;
            },
            hideExportAlert: function () {
                this.exportAlert = false;
            }
        }
    }
</script>

<style lang="scss" scoped>
    @import "../../css/variables.scss";

    .legend {
        width: 35px;
        display: inline-block;
    }
    .editable-cell {
        background-color: white;
        border: 1px solid black;
    }
    .read-only-cell {
        background-color: #ddd;
    }
    .read-only-warning-cell {
        background-color: $readonly-warning-background-color;
    }
    .export-alert {
        position: absolute;
        margin-left: auto;
        margin-right: auto;
        top: 40%;
        left: 0;
        right: 0;
    }
    .buttons {
        display: block;
        text-align: center;
        margin-bottom: 20px;
    }
    .export {
        background-color: white;
        color: $blue;
    }

    div.loading {
        width: 42px;
        height: 42px;
        position: absolute;
        margin-left: auto;
        margin-right: auto;
        top: 50%;
        left: 0;
        right: 0;
        background-image: url('web/assets/img/Spinner.gif');
        display:none;
    }

    button {
        display: inline-block;
        background-color: $blue;
        border: 1px solid black;
        color: white;
        padding: 10px 30px;
        text-align: center;
        text-decoration: none;
        font-size: 16px;
        margin: auto;

        img {
            height: 21px;
            position: absolute;
            margin-left: -23px;
            display: none;
        }
    }

    button {
        &.disabled {
            opacity: 0.5;
        }

        &.calculating {
            img {
                display: inline-block;
            }
        }
    }
</style>
