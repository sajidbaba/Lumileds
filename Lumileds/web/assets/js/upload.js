import Vue from 'vue';
import Upload from './component/Upload';
import VueResource from 'vue-resource';
import Toasted from 'vue-toasted';

require('../css/toast.scss');

Vue.use(VueResource);
Vue.use(Toasted);

new Vue({
	el: '#uploads',
	components: {
		Upload
	}
});
