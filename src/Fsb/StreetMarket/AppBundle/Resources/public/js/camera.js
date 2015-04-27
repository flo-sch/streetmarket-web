navigator.getUserMedia = (navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia || function () {});

Vue.config.debug = true;

var UserCameraRenderer = Vue.extend({
  replace: true,
  template: '#user-camera-renderer-template',
  data: function () {
    return {
      width: 0,
      height: 0
    }
  },
  events: {
    'app:record:on': function () {
      var renderer = this;

      if (navigator.getUserMedia) {
        navigator.getUserMedia({
          video: true,
          audio: false
        }, function (stream) {
          renderer.$el.src = window.URL.createObjectURL(stream);
          renderer.$el.play();
        }, function (error) {
          renderer.$dispatch('browser:getUserMedia:error', error);
        });
      } else {
        renderer.$dispatch('browser:getUserMedia:unsupported');
      }
    },
    'app:record:off': function () {
      // Stop the video and clear its source
      this.$el.pause();
      this.$el.src = '';
    }
  },
  methods: {
    onSourceReady: function (event) {
      this.$dispatch('renderer:video:ready', {
        width: this.$el.videoWidth,
        height: this.$el.videoHeight,
        source: this.$el
      });
    },
  }
});

var UserCameraReflector = Vue.extend({
  replace: true,
  template: '#user-camera-reflector-template',
  data: function () {
    return {
      source: null
    }
  },
  events: {
    'renderer:video:ready': function (video) {
      this.setSize(video.width, video.height);
      this.source = video.source;
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
      if (this.source) {
        var context = this.getContext();
        context.drawImage(this.source, 0, 0, this.$el.width, this.$el.height);
        var data = this.$el.toDataURL('image/jpeg', 1.0);

        this.$dispatch('reflector:picture:taken', data);
      } else {
        console.error('Unknown source...');
      }
    }
  }
});

var UserCamera = Vue.extend({
  replace: true,
  template: '#user-camera-template',
  events: {
    'renderer:video:ready': function (video) {
      this.$broadcast('renderer:video:ready', video);
    }
  },
  components: {
    'camera-renderer': UserCameraRenderer,
    'camera-reflector': UserCameraReflector
  }
});

var Camera = new Vue({
  el: 'body',
  data: {
    view: 'camera',
    isRecording: false
  },
  events: {
    'browser:getUserMedia:unsupported': function () {
      console.error('Sorry, the browser you are using doesn\'t support getUserMedia');
    },
    'browser:getUserMedia:error': function (error) {
      console.error(error);
    },
    'renderer:video:ready': function () {
      this.isRecording = true;
    },
    'reflector:picture:taken': function (data) {
      console.log('Picture taken', data.length, data);
      // Now we have the picture data as base64 image/png
    }
  },
  components: {
    'camera': UserCamera
  },
  methods: {
    record: function (event) {
      event.preventDefault();

      this.$broadcast('app:record:on');
    },
    stopRecording: function (event) {
      event.preventDefault();

      this.isRecording = false;
      this.$broadcast('app:record:off');
    },
    takePicture: function (event) {
      event.preventDefault();

      this.$broadcast('app:take-picture');
    }
  }
});