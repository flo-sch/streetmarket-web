<template>
  <video class='fullscreen' v-on='canplay: onSourceReady' autoplay='true'>Your browser does not support this application :(</video>
</template>

<script>
  var Vue = require('vue');

  var CameraRenderer = Vue.extend({
    replace: true,
    data: function () {
      return {
        width: 0,
        height: 0,
        source: null,
        stream: null
      }
    },
    watch: {
      source: function () {
        this.getUserMedia(true);
      }
    },
    events: {
      'app:record:on': function () {
        if (this.$el.src) {
          this.$el.play();
        } else {
          this.getUserMedia();
        }
      },
      'app:record:pause': function () {
        this.$el.pause();
      },
      'app:record:off': function () {
        // Stop the video and clear its source
        this.$el.pause();
        this.$el.src = null;
        // Cancel the current stream
        if (this.stream) {
          this.stream.stop();
          this.stream = null;
        }
      },
      'app:source:change': function (source) {
        this.source = source;
      }
    },
    methods: {
      getUserMedia: function (sourceHasChanged) {
        sourceHasChanged = sourceHasChanged || false;
        var renderer = this;
        var constraints = true;

        if (sourceHasChanged) {
          this.$el.pause();
          this.$el.src = null;
          if (this.stream) {
            this.stream.stop();
            this.stream = null;
          }
        }

        if (this.source) {
          constraints = {
            optional: [{
              sourceId: this.source
            }]
          };
        }

        if (navigator.getUserMedia) {
          navigator.getUserMedia({
            video: constraints,
            audio: false
          }, function (stream) {
            renderer.stream = stream;
            renderer.$el.src = window.URL.createObjectURL(stream);
            renderer.$el.play();
          }, function (error) {
            renderer.$dispatch('browser:getUserMedia:error', error);
          });
        } else {
          renderer.$dispatch('browser:getUserMedia:unsupported');
        }
      },
      onSourceReady: function (event) {
        this.$dispatch('renderer:video:ready', {
          width: this.$el.videoWidth,
          height: this.$el.videoHeight,
          element: this.$el
        });
      }
    }
  });

  module.exports = CameraRenderer;
</script>