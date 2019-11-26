<template>
    <div>
        <div class="loading edit-loading" v-show="!initiated"></div>

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

                    <form v-if="!isMarketApproved">
                        <div class="form-group">
                            <label for="comment">Comment</label>
                            <textarea class="form-control" v-model="form.comment" id="comment"></textarea>
                        </div>

                        <button type="button" class="btn btn-danger" @click="() => submitFeedback('requestFeedback')">
                            Request feedback
                        </button>

                        <button type="button" class="btn btn-success" @click="() => submitFeedback('approveSegment')">
                            Approve Segment
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="row" v-show="initiated">
            <div class="col-xs-12">
                <Grid
                        ref="grid"
                        :rows="rows"
                        :years="years"
                        :trackedCells="form.trackedCells"
                        :initialApproveSegment="initialApproveSegment"
                        :affectedCells="affectedCells"
                        :countryId="countryId"
                        @updateCells="sendCellsToApi"
                        @loadData="loadData"
                ></Grid>
            </div>
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

    export default {
        props: {
            countryId: Number,
            isMarketApproved: Boolean,
        },
        components: {
            Modal,
            DatePicker,
            Reminder,
            Grid
        },
        data() {
            return {
                rows: [],
                years: [],
                comments: [],
                initiated: false,
                segment: 'LV',
                form: {
                    comment: null,
                    trackedCells: {},
                },
                dependentCells: {},
                initialApproveSegment: null,
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
                this.loadData('LV');
                this.loadComments();
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
                    }
                }, (response) => {
                    if (!this.previousRequest) {
                        this.calculating = false;
                        this.$toasted.error('Something went wrong: ' + response.statusText, {
                            duration: 1500
                        });
                    }
                })
                    // .then(() => this.$refs.grid.$refs.simpleIndicatorCharts.loadCharts());
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
            loadData: function(segment) {
                let url = this.urlResolver.getAdminContributionTable(this.countryId);

                this.initiated = false;

                this.$http.get(
                    url,
                    {
                        params: {
                            segment: segment,
                        }
                    }
                ).then((response) => {
                    if (!!response.body) {
                        this.rows = response.body.rows;
                        this.years = response.body.years;
                        this.initialApproveSegment = response.body.approved;
                        this.initiated = true;
                        this.segment = segment;

                        this.rows.forEach(function(row) {
                            row.initialApproved = row.approved;
                        });
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
            submitFeedback: function(submitType) {
                if (!this.isFeedbackValid(submitType)) {
                    this.$toasted.error(
                        'Please write comment or approve changes.',
                        {duration: 1500}
                    );

                    return;
                }

                let url = this.urlResolver.saveAdminFeedback(this.countryId);

                let requestCells = Object.assign({}, this.form.trackedCells, this.dependentCells);

                this.$http.post(url, {
                    comment: this.form.comment,
                    trackedCells: requestCells,
                    reviewedRows: this.getReviewedRows(),
                    segment: this.segment,
                    approveType: submitType,
                }).then((response) => {
                    this.resetForm();
                    this.loadComments();

                    this.rows.forEach(function(row) {
                        row.initialApproved = row.approved;
                    });

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
            isFeedbackValid: function(submitType) {
                return this.form.comment || Object.keys(this.form.trackedCells).length || this.reviewedRows() || submitType === 'approveSegment';
            },
            reviewedRows: function() {
                let reviewed = false;

                this.rows.forEach(function(row) {
                    if (row.initialApproved !== row.approved) {
                        reviewed = true;
                    }
                });

                return reviewed;
            },
            getReviewedRows: function() {
                let reviewedRows = [];

                this.rows.forEach(function(row) {
                    if (row.initialApproved !== row.approved) {
                        reviewedRows.push({
                            indicator: row.indicator,
                            market: row.market,
                            segment: row.segment,
                            technology: row.technology,
                            state: row.approved,
                        });
                    }
                });

                return reviewedRows;
            }
        }
    }
</script>
