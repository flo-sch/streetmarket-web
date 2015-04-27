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

// var Furniture = Vue.extend({
//   replace: true,
//   template: '#furniture-template',
//   data: function () {
//     return {
//       title: null,
//       tookAt: null,
//       picturePath: null
//     }
//   }
// });

var FurnituresList = Vue.extend({
  replace: true,
  inherit: true,
  template: '#furnitures-list-template'
});

var Camera = new Vue({
  el: 'body',
  data: {
    view: 'furnitures',
    isReady: false,
    isRecording: false,
    position: null,
    furnitures: []
  },
  ApiClient: null,
  ready: function () {
    this.$options.ApiClient = new StreetMarketClient(window.location.origin);

    var Camera = this;

    this.$options.ApiClient.list(function (success, response, status) {
      if (success && response.success) {
        this.furnitures = response.furnitures;
      }
    }, this);

    navigator.geolocation.getCurrentPosition(function (position) {
      Camera.isReady = true;

      Camera.position = position;
    }, function () {
      alert('This application requires to know your current location.');
    });
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
    'app:api:uploaded': function (furniture) {
      this.stopRecording();

      // NOW the picture has been correctly uploaded,
      // Provide a visual feedback [Flash message ?]
      this.furnitures.unshift(furniture);
    },
    'app:api:error': function (response, status) {
      console.error('API error', response, status);
    },
    'reflector:picture:taken': function (data) {
      // Now we have the picture data as base64 image/png.

      // Conversion to blob to reduce the data size
      // A base64 string is heavier to send than a blob
      var characters = atob(data.split(',')[1]);
      var chunkSize = 512;
      var bytes = [];

      for (var offset = 0; offset < characters.length; offset += chunkSize) {
          var slice = characters.slice(offset, offset + chunkSize);

          var byteNumbers = new Array(slice.length);

          for (var i = 0; i < slice.length; i++) {
              byteNumbers[i] = slice.charCodeAt(i);
          }

          var byteArray = new Uint8Array(byteNumbers);

          bytes.push(byteArray);
      }

      var blob = new Blob(bytes, {
          type: 'image/jpeg'
      });

      this.$options.ApiClient.create({
        title: 'TEST',
        latitude: this.position.coords.latitude,
        longitude: this.position.coords.longitude,
      }, function (success, response, status) {
        if (success && response.success && response.furniture) {
          this.$options.ApiClient.upload(response.furniture.id, {
            picture: blob,
            filename: 'test.jpg'
          }, function (success, response, status) {
            if (success && response.success) {
              this.$emit('app:api:uploaded', response.furniture);
            } else {
              // Handle an error...
              this.$emit('app:api:error', response, status);
            }
          }, this);
        }
      }, this);
    }
  },
  components: {
    'camera': UserCamera,
    'furnitures': FurnituresList,
  },
  methods: {
    record: function (event) {
      if (event) {
        event.preventDefault();
      }

      this.view = 'camera';
      this.$broadcast('app:record:on');
    },
    stopRecording: function (event) {
      if (event) {
        event.preventDefault();
      }

      this.view = 'furnitures';
      this.isRecording = false;
      this.$broadcast('app:record:off');
    },
    takePicture: function (event) {
      if (event) {
        event.preventDefault();
      }

      this.$broadcast('app:take-picture');
    }
  }
});