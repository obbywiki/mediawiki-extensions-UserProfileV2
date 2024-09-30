(function () {
	const Vue = require('vue');
	const App = require('./components/App.vue');

	const app = Vue.createMwApp(App);
	const mountElement = document.querySelector('.profile-masthead');
	const vueRoot = document.createElement('div');
	mountElement.appendChild(vueRoot);
	app.mount(vueRoot);
}());
