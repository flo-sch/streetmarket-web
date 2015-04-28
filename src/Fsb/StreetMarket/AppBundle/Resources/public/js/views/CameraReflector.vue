<template>
  <canvas class='background-read-only' resize='true' v-class='visible: visible'></canvas>
</template>

<script>
  var Vue = require('vue');

  var CameraReflector = Vue.extend({
    replace: true,
    data: function () {
      return {
        video: null,
        visible: false,
      }
    },
    events: {
      'renderer:video:ready': function (video) {
        this.setSize(video.width, video.height);
        this.video = video.element;
      },
      'app:record:pause': function () {
        this.visible = true;
      },
      'app:record:on': function () {
        this.visible = false;
      },
      'app:take-picture': function (event) {
        this.drawPicture();
      }
    },
    methods: {
      getContext: function () {
        return this.$el.getContext('2d');
      },
      clearCanvas: function () {
        var context = this.getContext();
        context.fillStyle = '#AAA';
        context.fillRect(0, 0, this.$el.width, this.$el.height);
      },
      setSize: function (width, height) {
        if (width && height) {
          this.$el.width = width;
          this.$el.height = height;
        }
      },
      drawPicture: function () {
        if (this.video) {
          var context = this.getContext();
          context.drawImage(this.video, 0, 0, this.$el.width, this.$el.height);
          var data = this.$el.toDataURL('image/jpeg', 1.0);

          this.$dispatch('reflector:picture:taken', data);
        } else {
          console.error('Unknown source element...');
        }
      }
    }
  });

  module.exports = CameraReflector;
</script>