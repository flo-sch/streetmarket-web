<template>
  <section class='camera'>
    <camera-renderer v-ref='Renderer'></camera-renderer>
    <camera-reflector v-ref='Reflector'></camera-reflector>
  </section>
</template>

<script>
  var Vue = require('vue');

  var CameraRenderer = require('./CameraRenderer.vue');
  var CameraReflector = require('./CameraReflector.vue');

  var Camera = Vue.extend({
    replace: true,
    inherit: true,
    events: {
      'renderer:video:ready': function (video) {
        this.$broadcast('renderer:video:ready', video);
      }
    },
    components: {
      'camera-renderer': CameraRenderer,
      'camera-reflector': CameraReflector
    }
  });

  module.exports = Camera;
</script>