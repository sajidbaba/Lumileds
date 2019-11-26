import Vue from 'vue';
import VueResource from 'vue-resource';
import VTooltip from 'v-tooltip';
import Toasted from 'vue-toasted';
import vSelect from 'vue-select';
import Versioning from './component/Versioning';

require('jquery-file-download');
require('../css/tooltip.scss');

Vue.use(VueResource);
Vue.use(VTooltip);
Vue.use(Toasted);
Vue.component('v-select', vSelect);

new Vue({
	el: '#app',
    components: {
        Versioning
	}
});
