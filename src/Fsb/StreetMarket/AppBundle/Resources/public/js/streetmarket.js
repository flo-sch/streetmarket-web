var Vue = require('vue');
Vue.config.debug = true;

var appOptions = require('./views/App.vue');
var app = new Vue(appOptions);