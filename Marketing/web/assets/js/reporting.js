import Vue from 'vue';
import Toasted from 'vue-toasted';
import Reporting from './component/reporting/Reporting';

require('jquery-file-download');
require('../css/tooltip.scss');
require('../css/toast.scss');

Vue.use(Toasted);

new Vue({
    el: '#app',
    components: {
       Reporting
    }
});
