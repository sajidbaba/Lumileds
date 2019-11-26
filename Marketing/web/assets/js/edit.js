import Vue from 'vue';
import VueResource from 'vue-resource';
import VTooltip from 'v-tooltip';
import Toasted from 'vue-toasted';
import vSelect from 'vue-select';
import Edit from './component/Edit';

require('../css/tooltip.scss');
require('../css/toast.scss');
require('jquery-file-download');

Vue.use(VueResource);
Vue.use(VTooltip);
Vue.use(Toasted);
Vue.component('v-select', vSelect);

new Vue({
	el: '#app',
    components: {
        Edit
	}
});
