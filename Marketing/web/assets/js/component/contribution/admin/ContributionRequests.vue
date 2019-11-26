<template>
    <div>
        <modal :showModal="showModal" :closeAction="closeModal">
            <h4 slot="header">Request contribution</h4>

            <div slot="body">
                <form @submit.prevent="saveForm">
                    <div class="form-group" :class="{'has-error': errors.has('deadline') }">
                        <label>Deadline</label>
                        <date-picker v-model="form.deadline" :config="datetimePickerConfig" v-validate="'required'" name="deadline"></date-picker>
                        <span class="help-block" v-show="errors.has('deadline')">{{ errors.first('deadline') }}</span>
                    </div>

                    <div class="form-group" :class="{'has-error': errors.has('cc') }">
                        <label>CC</label>
                        <input type="text" class="form-control" placeholder="" v-model="form.carbonCopy" name="cc">
                        <span class="help-block" v-show="errors.has('cc')">{{ errors.first('cc') }}</span>
                    </div>

                    <button type="submit" class="btn btn-default">Submit</button>
                </form>
            </div>
        </modal>

        <table class="table table-condensed">
            <thead>
            <tr>
                <th>Region</th>
                <th>Deadline</th>
                <th>Market</th>
                <th>Status</th>
                <th><!-- Last contributor name --></th>
                <th><!-- Last contributor date --></th>
                <th><!-- Action button --></th>
            </tr>
            </thead>

            <tbody>
            <template v-for="region in regions">
                <template v-for="(country, key, index) in region.countries">
                    <tr valign="top">
                        <td v-if="!key" :rowspan="region.countries.length">{{ region.name }}</td>
                        <td v-if="!key" :rowspan="region.countries.length">
                            <button
                                    class="btn btn-default"
                                    @click="openModal(region)"
                                    v-if="region.contribution_request"
                            >
                                {{ region.contribution_request.deadline | formatDate }}
                            </button>
                            <button
                                    class="btn btn-default"
                                    @click="openModal(region)"
                                    v-else
                            >
                                Request Contribution
                            </button>
                        </td>

                        <template v-if="country.active">
                            <td>
                                {{ country.name }}
                                <Reminder
                                        :id="country.id"
                                        v-if="reminderIsAllowed(region.contribution_request, country.contribution_country_request)"
                                ></Reminder>
                            </td>

                            <template v-if="country.contribution_country_request && country.contribution_country_request.status === 3">
                                <td>{{ statusLabels[country.contribution_country_request.status] }}</td>
                                <td></td>
                                <td></td>
                                <td>
                                    <a :href="getViewContributionLink(country)">
                                        <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
                                    </a>
                                </td>
                            </template>
                            <template v-else-if="country.contribution_country_request">
                                <td>{{ statusLabels[country.contribution_country_request.status] }}</td>
                                <td>{{ country.contribution_country_request.last_contribution ? country.contribution_country_request.last_contribution.user.username : ''}}</td>
                                <td>{{ (country.contribution_country_request.last_contribution ? country.contribution_country_request.last_contribution.created_at : '')|formatDateTime}}</td>
                                <td>
                                    <a :href="getViewContributionLink(country)">
                                        <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
                                    </a>
                                </td>
                            </template>
                            <template v-else>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </template>
                        </template>
                    </tr>
                </template>
            </template>
            </tbody>
        </table>
    </div>
</template>

<script>
    import UrlResolver from '../../../services/UrlResolver';
    import Modal from 'modal-vue';
    import DatePicker from 'vue-bootstrap-datetimepicker';
    import moment from 'moment/moment';
    import Reminder from '../../Reminder';

    export default {
        components: {
            Modal,
            DatePicker,
            Reminder
        },
        data() {
            return {
                showModal: false,
                datetimePickerConfig: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    showClear: true,
                    showClose: true,
                },
                form: {
                    id: null,
                    deadline: null,
                    carbonCopy: null
                },
                regions: null,
                statusLabels: {
                    0: 'Required',
                    1: 'Reminded',
                    2: 'Submitted',
                    3: 'Approved'
                }
            }
        },
        mounted: function () {
            this.init();
        },
        methods: {
            init: function () {
                this.urlResolver = new UrlResolver;
                this.loadRegions();
            },
            openModal: function(region) {
                this.form.id = region.id;

                if (region.contribution_request) {
                    this.form.deadline = moment(String(region.contribution_request.deadline)).format('DD/MM/YYYY');
                } else {
                    this.form.deadline = null;
                }

                this.$validator.reset();

                this.showModal = true;
            },
            closeModal: function() {
                this.showModal = false;
                this.resetForm();
            },
            saveForm: function() {
                let url = this.urlResolver.requestContribution(this.form.id);

                this.$validator.validateAll().then(result => {
                    if (!result) {
                        return;
                    }

                    this.$http.post(
                        url,
                        {
                            deadline: moment(this.form.deadline, 'DD/MM/YYYY').format(),
                            carbonCopy: this.form.carbonCopy
                        }
                    ).then((response) => {
                        if (response.status === 204) {
                            this.$toasted.success('Contribution requested successfully.', {
                                duration: 1500,
                                className: 'toast-success'
                            });

                            this.loadRegions();

                            this.closeModal();
                        } else {
                            this.$toasted.error('Unexpected response code: ' + response.status, {
                                duration: 1500
                            });
                        }
                    }, response => {
                        this.$toasted.error('Something went wrong: ' + response.statusText, {
                            duration: 1500
                        });
                    });
                })
            },
            loadRegions: function () {
                let url = this.urlResolver.getRegionsRelatedToUser();

                this.$http.get(url).then((response) => {
                    this.regions = response.body;
                }, response => {
                    this.$toasted.error('Something went wrong: ' + response.statusText, {
                        duration: 1500
                    });
                });
            },
            resetForm: function()
            {
                this.form.id = null;
                this.form.deadline = null;
                this.form.carbonCopy = null;
            },
            reminderIsAllowed: function (contributionRequest, contributionCountryRequest) {
                return contributionRequest && contributionCountryRequest.status !== 3;
            },
            getViewContributionLink: function(country) {
                return this.urlResolver.viewRequestContribution(country.id);
            }
        }
    }
</script>
