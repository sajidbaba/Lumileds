<template>
    <div>
        <div class="info">
            <div class="alert alert-success" role="alert" v-if="isMarketReviewed">
                All indicators are reviewed
            </div>
            <div class="alert alert-info" role="alert" v-else>
                Your feedback is required on the following indicators:
            </div>
        </div>

        <table class="table table-condensed" v-if="contributionCountryRequest">
            <thead>
            <tr>
                <th>Market</th>
                <th>Indicator</th>
                <th>Review Status</th>
                <th></th>
            </tr>
            </thead>

            <tbody>
            <template v-for="contributionIndicatorRequest in contributionCountryRequest.contribution_indicator_requests">
                <tr valign="top">
                    <td>{{ contributionCountryRequest.country.name }}</td>
                    <td>{{ indicatorGroupLabels[contributionIndicatorRequest.indicator_group] }}</td>
                    <td>{{ statusLabels[contributionIndicatorRequest.status] }}</td>
                    <td>
                        <a :href="getIndicatorContributionLink(contributionIndicatorRequest)">
                            <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
                        </a>
                    </td>
                </tr>
            </template>
            </tbody>
        </table>

        <div class="alert alert-info info" role="alert">
            <p>Please click on each indicator to give your feedback.</p>
            <p>Once you have reviewed all indicators you can submit your feedback for approval. Please include any comments you may have on your changes below:</p>
        </div>

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

                <form @submit.prevent="submitFeedback">
                    <div class="form-group">
                        <label for="comment">Comment</label>
                        <textarea class="form-control" v-model="form.comment" id="comment"></textarea>
                    </div>

                    <button type="submit" class="btn btn-default" :disabled="!isMarketReviewed">Submit feedback</button>
                </form>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
    .comments-box {
        max-height: 100px;
        overflow-y: scroll;
    }

    td {
        height: 35px;
    }

    .info {
        margin-top: 30px;
    }
</style>

<script>

    import UrlResolver from "../../../services/UrlResolver";

    export default {
        props: {
            countryId: Number,
            contributionCountryRequestId: Number,
        },
        data() {
            return {
                contributionCountryRequest: null,
                comments: [],
                isMarketReviewed: false,
                form: {
                    comment: null,
                    trackedCells: {},
                },
                statusLabels: {
                    0: 'Required',
                    1: 'Reviewed',
                    2: 'Submitted',
                    3: 'Approved'
                },
                indicatorGroupLabels: {
                    0: 'Park Split By Technology',
                    1: 'Upgrade Take Rate',
                    4: 'Price Development'
                },
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
                this.loadComments();
                this.loadContributionCountryRequest(this.countryId);
            },
            loadContributionCountryRequest: function (id) {
                let url = this.urlResolver.getContributionCountryRequest(id);

                this.$http.get(url).then((response) => {
                    this.contributionCountryRequest = response.body;

                    let notReviewedIndicators = 0;
                    for (let contributionIndicatorRequest in response.body.contribution_indicator_requests) {
                        let status = response.body.contribution_indicator_requests[contributionIndicatorRequest].status;
                        if (!status) {
                            notReviewedIndicators++;
                        }
                    }

                    if (notReviewedIndicators === 0) {
                        this.isMarketReviewed = true;
                    }

                }, response => {
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
            submitFeedback: function() {
                let url = this.urlResolver.saveContributorFeedback(this.countryId);

                this.$http.post(url, { comment: this.form.comment }).then((response) => {
                    this.resetForm();
                    this.loadComments();

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
            },
            getIndicatorContributionLink: function(contributionIndicatorRequest) {
                return this.urlResolver.indicatorContributorRequestContribution(contributionIndicatorRequest.id);
            }
        }
    }
</script>
