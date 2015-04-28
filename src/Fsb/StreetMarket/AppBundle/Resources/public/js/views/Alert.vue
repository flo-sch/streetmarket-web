<template>
  <div class='container'>
    <div class='alert' role='alert' v-if='display' v-class='"alert-" + level, alert-dismissable: dismissable' v-transition='fade'>
      <a href='#close' type='button' class='close' aria-label='Close' v-if='dismissable' v-on='click: dismiss'><span aria-hidden='true'>&times;</span></a>
      <p>{{ message }}</p>
    </div>
  </div>
</template>

<script>
  var Vue = require('vue');

  var Alert = Vue.extend({
    events: {
      'app:flash:display': function (level, message, abc) {
        this.level = level;
        this.message = message;
        this.dismissable = abc;
        this.display = true;
      }
    },
    data: function () {
      return {
        display: false,
        dismissable: true,
        level: '',
        message: ''
      }
    },
    methods: {
      dismiss: function (event) {
        if (event) {
          event.preventDefault();
        }

        this.display = false;
      }
    }
  });

  module.exports = Alert;
</script>