<template>
  <section class='camera'>
    <camera-renderer v-ref='Renderer'></camera-renderer>
    <camera-reflector v-ref='Reflector'></camera-reflector>
  </section>
  <nav class='navbar navbar-fixed-bottom navbar-default navbar-center'>
    <p class='navbar-text text-muted' v-if='!isLocated'><span class='glyphicon glyphicon-map-marker' aria-hidden='true'></span> Looking for your location...</p>
    <a class='btn btn-md btn-info btn-aligned' href='#take-picture' v-on='click: takePicture' v-if='isLocated && isRecording && !isTaken'><span class='glyphicon glyphicon-camera' aria-hidden='true'></span></a>
    <a class='btn btn-md btn-info btn-aligned' href='#record' v-on='click: record' v-if='isTaken'><span class='glyphicon glyphicon-refresh' aria-hidden='true'></span></a>
    <form class='navbar-form' action='#picture' v-if='isTaken'>
      <div class='form-group'>
        <input class='form-control' type='text' placeholder='Name' v-model='title' />
      </div>
    </form>
    <a class='btn btn-md btn-success btn-aligned' href='#validate-picture' v-on='click: validatePicture' v-if='isTaken'><span class='glyphicon glyphicon-check' aria-hidden='true'></span></a>
  </nav>
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