import Vue from 'vue';
import ContributionIndicatorRequests from '../../component/contribution/contributor/ContributionIndicatorRequests';
import Toasted from "vue-toasted";
import VueResource from "vue-resource";
import moment from "moment/moment";

require('../../../css/tooltip.scss');
require('../../../css/toast.scss');

Vue.use(VueResource);
Vue.use(Toasted);

Vue.filter('formatDateTime', function(value) {
    if (value) {
        return moment(String(value)).format('DD/MM/YYYY HH:mm:ss')
    }
});

new Vue({
    el: '#app',
    components: {
        ContributionIndicatorRequests
    }
});
