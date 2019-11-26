import Vue from 'vue';
import ContributionRequest from '../../component/contribution/admin/ContributionRequest';
import Toasted from "vue-toasted";
import VueResource from "vue-resource";
import VeeValidate from 'vee-validate';
import VTooltip from "v-tooltip";
import moment from "moment/moment";

require('../../../css/tooltip.scss');
require('../../../css/toast.scss');

Vue.use(VueResource);
Vue.use(Toasted);
Vue.use(VeeValidate);
Vue.use(VTooltip);

Vue.filter('formatDateTime', function(value) {
    if (value) {
        return moment(String(value)).format('DD/MM/YYYY HH:mm:ss')
    }
});

new Vue({
    el: '#app',
    components: {
        ContributionRequest
    }
});
