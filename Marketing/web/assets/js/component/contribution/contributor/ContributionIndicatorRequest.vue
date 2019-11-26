<template>
    <div>
        <div class="loading edit-loading" v-show="!initiated"></div>

        <div class="row">
            <div class="col-xs-12">
                <div class="row">
                    <div class="col-sm-offset-4 col-sm-4 text-center">
                        <div class="buttons">
                            <button @click="submitFeedback">Submit</button>
                        </div>

                        <div class="tabs">
                            <span>
                                <a :class="{ 'tab-active': segment === 'LV' }" @click.prevent="loadBySegment('LV')" href="#">LV</a>
                            </span>

                            <span>
                                <a :class="{ 'tab-active': segment === 'HV' }" @click.prevent="loadBySegment('HV')" href="#">HV</a>
                            </span>

                            <span>
                                <a :class="{ 'tab-active': segment === '2W' }" @click.prevent="loadBySegment('2W')" href="#">2W</a>
                            </span>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <dl class="dl-horizontal">
                            <div class="well well-sm">
                                <div><strong>Note:</strong></div>
                                <div>Alert will display when variation is > 5%</div>
                                <div>Error will display when number is not in the right format</div>
                            </div>

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
            </div>
        </div>

        <!--<SimpleIndicatorCharts
                ref="simpleIndicatorCharts"
                :segment="segment"
                :affectedCells="affectedCells"
                :markets="[countryId]"
        />-->

        <div v-show="initiated">
            <div class="row" v-show="rows1.length">
                <div class="col-xs-12">
                    <h3>{{ tableTitle1 }}</h3>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <Grid
                            :rows="rows1"
                            :years="years1"
                            :total="total1"
                            :showTotal="showTotalForFirstTable"
                            :trackedCells="form.trackedCells"
                            @updateCells="sendCellsToApi"
                            @loadData="loadData"
                    ></Grid>
                </div>
            </div>

            <div class="row" v-show="rows2.length">
                <div class="col-xs-12">
                    <h3>{{ tableTitle2 }}</h3>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <Grid
                            :rows="rows2"
                            :years="years2"
                            :total="total2"
                            :showTotal="showTotalForSecondTable"
                            :trackedCells="form.trackedCells"
                            @updateCells="sendCellsToApi"
                            @loadData="loadData"
                    ></Grid>
                </div>
            </div>
        </div>

        <div class="container" v-show="initiated">
            <div class="row">
                <div class="col-xs-12">
                    <h4>All comments</h4>

                    <div class="comments-box" ref="commentsBox">
                        <div class="media" v-for="comment in comments">
                            <div class="media-body">
                                {{ comment.created_at|formatDateTime }} -
                                {{ comment.user.username }}:
                                {{ comment.comment }}
                            </div>
                        </div>
                    </div>

                    <template v-if="comments.length === 0">
                        <div class="alert alert-warning" role="alert">No messages</div>
                    </template>

                    <hr/>

                    <form @submit.prevent="submitComment">
                        <div class="form-group">
                            <label for="comment">Comment</label>
                            <textarea class="form-control" v-model="form.comment" id="comment"></textarea>
                        </div>

                        <button type="submit" class="btn btn-default" @click="submitFeedback">Submit comment</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="container" id="instructions">
            <label for="instructions">Instructions</label>
            <textarea class="form-control" rows="5" readonly>{{ instructions }}</textarea>
        </div>
    </div>
</template>

<style lang="scss" scoped>
    .comments-box {
        max-height: 100px;
        overflow-y: scroll;
    }

    div.edit-loading {
        display: block;
    }
</style>

<script>
    import Grid from './Grid.vue'
    import UrlResolver from '../../../services/UrlResolver';
    import Modal from 'modal-vue';
    import DatePicker from 'vue-bootstrap-datetimepicker';
    import Reminder from '../../Reminder';
    import SimpleIndicatorCharts from './../../SimpleIndicatorCharts.vue';

    export default {
        props: {
            countryId: Number,
            indicatorGroupId: Number,
            indicatorRequestId: Number,
            isReviewed: Boolean,
        },
        components: {
            Modal,
            DatePicker,
            Reminder,
            Grid,
            SimpleIndicatorCharts
        },
        data() {
            return {
                rows1: [],
                rows2: [],
                years1: [],
                years2: [],
                total1: [],
                total2: [],
                tableTitle1: '',
                tableTitle2: '',
                instructions: '',
                comments: [],
                initiated: false,
                showTotalForFirstTable: false,
                showTotalForSecondTable: false,
                segment: 'LV',
                form: {
                    comment: null,
                    trackedCells: {},
                },
                dependentCells: {},
                affectedCells: []
            }
        },
        mounted: function () {
            this.init();
        },
        updated: function () {
            this.$nextTick(function () {
                this.$refs.commentsBox.scrollTop = this.$refs.commentsBox.scrollHeight;
            })
        },
        methods: {
            init: function () {
                this.urlResolver = new UrlResolver;

                this.loadData('LV')
                    // .then(() => this.$refs.simpleIndicatorCharts.loadCharts());

                this.loadComments();
                this.showTable();
            },
            sendCellsToApi: function() {
                let url = this.urlResolver.getCalculate();

                this.$http.post(url, this.form.trackedCells, {
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
                        this.calculateTotal();
                    }
                }, (response) => {
                    if (!this.previousRequest) {
                        this.calculating = false;
                        this.$toasted.error('Something went wrong: ' + response.statusText, {
                            duration: 1500
                        });
                    }
                })
                    // .then(() => this.$refs.simpleIndicatorCharts.loadCharts());
            },
            getCellByKey(id) {
                for (let i= 0, lenRows = this.rows1.length; i < lenRows; i++) {
                    for (let j = 0, lenCells = this.rows1[i].cells.length; j < lenCells; j++) {
                        if (this.rows1[i].cells[j].id === id) {
                            return this.rows1[i].cells[j];
                        }
                    }
                }
                for (let i= 0, lenRows = this.rows2.length; i < lenRows; i++) {
                    for (let j = 0, lenCells = this.rows2[i].cells.length; j < lenCells; j++) {
                        if (this.rows2[i].cells[j].id === id) {
                            return this.rows2[i].cells[j];
                        }
                    }
                }

                return false;
            },
            resetTotal() {
                this.total1 = [];
                this.total2 = [];
            },
            calculateTotal() {
                this.resetTotal();

                for (let i= 0, lenRows = this.rows1.length; i < lenRows; i++) {
                    for (let j = 0, lenCells = this.rows1[i].cells.length; j < lenCells; j++) {
                        if (i === 0) {
                            this.total1[j] = parseFloat(this.rows1[i].cells[j].value);
                        } else {
                            this.total1[j] += parseFloat(this.rows1[i].cells[j].value);
                        }
                    }
                }
                for (let i= 0, lenRows = this.rows2.length; i < lenRows; i++) {
                    for (let j = 0, lenCells = this.rows2[i].cells.length; j < lenCells; j++) {
                        if (i === 0) {
                            this.total2[j] = parseFloat(this.rows2[i].cells[j].value);
                        } else {
                            this.total2[j] += parseFloat(this.rows2[i].cells[j].value);
                        }
                    }
                }
            },
            updateCells: function(cellArray) {
                let self = this;
                this.dependentCells = {};

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

                        if (!cell) {
                            continue;
                        }

                        if (newCell.hasOwnProperty('error')) {
                            this.$set(cell, 'error', newCell.error);
                            this.$set(cell, 'error_type', newCell.error_type);
                        } else {
                            this.$set(cell, 'error', null);
                            this.$set(cell, 'error_type', null);
                        }

                        if (cell.value !== newCell.value) {
                            cell.value = newCell.value;
                            cell.contributed = true;
                        }

                        this.dependentCells[newCell.id] = {
                            id: newCell.id,
                            value: newCell.value,
                        };
                    }
                }
            },
            loadBySegment(segment) {
                if (!segment || segment === this.segment) return;

                this.loadData(segment);
                this.segment = segment;

                setTimeout(() => {
                    // this.$refs.simpleIndicatorCharts.loadCharts();
                }, 0);
            },
            loadData: function(segment) {
                let url = this.urlResolver.getContributorContributionTable(this.indicatorRequestId);

                this.initiated = false;

                return this.$http.get(url, { params: { segment: segment }})
                    .then((response) => {
                        if (!!response.body) {
                            this.rows1 = response.body[0].rows;
                            this.years1 = response.body[0].years;
                            this.tableTitle1 = response.body[0].title;

                            this.rows2 = response.body[1].rows;
                            this.years2 = response.body[1].years;
                            this.tableTitle2 = response.body[1].title;

                            this.instructions = response.body[0].instruction;

                            this.initiated = true;
                            this.segment = segment;

                            this.calculateTotal();
                        }
                    }, (response) => {
                        this.$toasted.error('Something went wrong: ' + response.statusText, {
                            duration: 1500
                        });
                    });
            },
            loadComments: function() {
                let url = this.urlResolver.getContributionComments(this.countryId);

                this.$http.get(url).then((response) => {
                    if (!!response.body) {
                        this.comments = response.body;
                    }
                }, (response) => {
                    this.$toasted.error('Something went wrong: ' + response.statusText, {
                        duration: 1500
                    });
                });
            },
            submitComment: function() {
                if (!this.isFeedbackValid()) {
                    this.$toasted.error(
                        'Please write comment',
                        {duration: 1500}
                    );

                    return;
                }

                let url = this.urlResolver.saveContributorComment(this.countryId);

                this.$http.post(url, { comment: this.form.comment }).then((response) => {
                    this.resetForm();
                    this.loadComments();

                    this.$toasted.success('Success: comment sent', {
                        duration: 1500
                    });
                }, (response) => {
                    this.$toasted.error('Something went wrong: ' + response.statusText, {
                        duration: 1500
                    });
                });
            },
            submitFeedback: function () {
                let url = this.urlResolver.saveContributorIndicatorFeedback(this.indicatorRequestId);

                let requestCells = Object.assign({}, this.form.trackedCells, this.dependentCells);

                this.$http.post(url, {
                    comment: this.form.comment,
                    trackedCells: requestCells,
                    segment: this.segment
                }).then((response) => {
                    this.$toasted.success('Success: feedback sent', {
                        duration: 1500
                    });
                }, (response) => {
                    this.$toasted.error('Something went wrong: ' + response.statusText, {
                        duration: 1500
                    });
                });
            },
            resetForm: function() {
                this.form = {
                    comment: null,
                    trackedCells: {},
                };

                this.dependentCells = {};
            },
            isFeedbackValid: function() {
                return this.form.comment || Object.keys(this.form.trackedCells).length;
            },
            showTable() {
                if (this.indicatorGroupId === 2 || this.indicatorGroupId === 3) {
                    this.showTotalForSecondTable = true;
                }
            },
        }
    }
</script>

<style lang="scss" scoped>
    @import "../../../../css/variables";

    .tab-active {
        background-color: $blue !important;
    }

    .tabs {
        margin-bottom: 20px;

        & > span > a {
            color:black;
            margin:auto;
            background-color: #f0f0f0;
            border: 1px solid black;
            padding: 5px 16px;
            text-align: center;
            text-decoration: none;
            font-size: 12px;
            display: inline-block;
        }
    }

    .buttons {
        display: block;
        text-align: center;
        margin-bottom: 20px;

        button {
            background-color: $blue;
            border: 1px solid black;
            color: white;
            padding: 10px 32px;
            text-align: center;
            text-decoration: none;
            font-size: 16px;
            display: block;
            margin: 20px auto;
        }
    }

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

    #instructions {
        margin-top: 30px;
    }
</style>
