import Vue from 'vue';
import ContributionRequests from '../../component/contribution/contributor/ContributionRequests';
import Toasted from "vue-toasted";
import VueResource from "vue-resource";
import VeeValidate from 'vee-validate';
import moment from "moment/moment";

require('../../../css/toast.scss');

Vue.use(VueResource);
Vue.use(Toasted);
Vue.use(VeeValidate);

Vue.filter('formatDate', function(value) {
    if (value) {
        return moment(String(value)).format('DD/MM/YYYY')
    }
});

Vue.filter('formatDateTime', function(value) {
    if (value) {
        return moment(String(value)).format('DD/MM/YYYY HH:mm:ss')
    }
});

new Vue({
    el: '#app',
    components: {
        ContributionRequests
    }
});
