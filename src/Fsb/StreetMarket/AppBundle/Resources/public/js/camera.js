navigator.getUserMedia = (navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia || function () {});

Vue.config.debug = true;

var UserCameraRenderer = Vue.extend({
  replace: true,
  template: '#user-camera-renderer-template',
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

      console.log('getUserMedia', this.source, constraints);

      this.$dispatch('test', JSON.stringify(constraints));

      if (navigator.getUserMedia) {
        navigator.getUserMedia({
          video: constraints,
          audio: false
        }, function (stream) {
          renderer.stream = stream;
          renderer.$el.src = window.URL.createObjectURL(stream);

          renderer.$dispatch('test', 'new stream! ' + renderer.$el.src);
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
        source: this.$el
      });
    }
  }
});

var UserCameraReflector = Vue.extend({
  replace: true,
  template: '#user-camera-reflector-template',
  data: function () {
    return {
      source: null,
      visible: false,
    }
  },
  events: {
    'renderer:video:ready': function (video) {
      this.setSize(video.width, video.height);
      this.source = video.source;
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
  inherit: true,
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

var Alert = Vue.extend({
  template: '#alert-template',
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

var Camera = new Vue({
  el: 'body',
  data: {
    view: 'furnitures',
    isLocated: false,
    isRecording: false,
    isLookingForSources: false,
    isTaken: false,
    title: 'TEST',
    picture: null,
    position: null,
    sources: [],
    currentSource: 0,
    furnitures: []
  },
  ApiClient: null,
  ready: function () {
    this.getMediaSources();

    this.$options.ApiClient = new StreetMarketClient(window.location.origin);

    this.$options.ApiClient.list(function (success, response, status) {
      if (success && response.success) {
        response.furnitures.forEach(function (furniture) {
          this.furnitures.push(this.parseFurniture(furniture));
        }, this);
      }
    }, this);
  },
  events: {
    'test': function (message) {
      this.displayAlert('info', message, true);
    },
    'browser:getUserMedia:unsupported': function () {
      this.displayAlert('danger', 'Sorry, the browser you are using doesn\'t support getUserMedia', false);
    },
    'browser:getUserMedia:error': function (error) {
      this.displayAlert('danger', error.name, true);
    },
    'renderer:video:ready': function () {
      this.isRecording = true;
    },
    'app:api:uploaded': function (furniture) {
      this.stopRecording();

      // NOW the picture has been correctly uploaded,
      // Provide a visual feedback [Flash message ?]
      this.furnitures.unshift(this.parseFurniture(furniture));
      this.displayAlert('success', 'Amazing! Huge thanks from everyone here :)', true);
    },
    'app:api:error': function (response, status) {
      this.displayAlert('danger', 'Oh snap! ' + response.message, true);
    },
    'app:geolocation:found': function () {
      this.displayAlert('success', 'Nice! We just located you :)', true);
    },
    'app:geolocation:canceled': function () {
      this.displayAlert('warning', 'Sorry, but we need to know where the pictures are taken...', false);
    },
    'reflector:picture:taken': function (data) {
      // Now we have the picture data as base64 image/png.
      this.isTaken = true;
      this.pauseRecording();
      this.picture = data;
    }
  },
  watch: {
    sources: function () {
      this.displayAlert('info', 'sources found : ' + this.sources.length, true);
    }
  },
  components: {
    'camera': UserCamera,
    'furnitures': FurnituresList,
    'alert': Alert
  },
  methods: {
    setView: function (view) {
      this.view = view;
    },
    record: function (event) {
      if (event) {
        event.preventDefault();
      }

      if (this.position === null) {
        this.definePosition();
      }

      this.setView('camera');

      this.isTaken = false;
      this.$broadcast('app:record:on');
    },
    pauseRecording: function () {
      this.$broadcast('app:record:pause');
    },
    stopRecording: function (event) {
      if (event) {
        event.preventDefault();
      }

      this.pauseRecording();
      this.isRecording = false;
      this.$broadcast('app:record:off');

      this.setView('furnitures');
    },
    takePicture: function (event) {
      if (event) {
        event.preventDefault();
      }

      this.$broadcast('app:take-picture');
    },
    validatePicture: function () {
      if (this.picture) {
        this.displayAlert('info', 'Thanks! We are currently uploading the picture...', true);

        // Conversion to blob to reduce the data size
        // A base64 string is heavier to send than a blob
        var characters = atob(this.picture.split(',')[1]);
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
          title: this.title,
          latitude: this.position.coords.latitude,
          longitude: this.position.coords.longitude,
        }, function (success, response, status) {
          if (success && response.success && response.furniture) {
            this.$options.ApiClient.upload(response.furniture.id, {
              picture: blob,
              filename: 'test.jpg'
            }, function (success, response, status) {
              this.isTaken = false;
              this.picture = null;

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
    definePosition: function () {
      var Camera = this;

      navigator.geolocation.getCurrentPosition(function (position) {
        Camera.isLocated = true;
        Camera.position = position;

        Camera.$emit('app:geolocation:found');
      }, function (error) {
        Camera.$emit('app:geolocation:canceled', error);
      }, {
        enableHighAccuracy: true
      });
    },
    reverseVideo: function (event) {
      if (event) {
        event.preventDefault();
      }

      if (this.sources.length > 0) {
        if (this.currentSource >= (this.sources.length - 1)) {
          this.currentSource = 0;
        } else {
          this.currentSource++;
        }

        this.displayAlert('info', 'Current source: [' + this.currentSource + '] with id : ' + this.sources[this.currentSource], true);
        this.$broadcast('app:source:change', this.sources[this.currentSource]);
      }
    },
    getMediaSources: function () {
      var Camera = this;

      if (typeof MediaStreamTrack === 'undefined') {
        this.isLookingForSources = false;
      } else {
        this.isLookingForSources = true;

        MediaStreamTrack.getSources(function (sources) {
          sources.forEach(function (source) {
            if (source.kind === 'video') {
              Camera.sources.push(source.id);
            }
          });

          Camera.isLookingForSources = false;
        });
      }
      
    },
    displayAlert: function (level, message, dismissable) {
      this.$broadcast('app:flash:display', level, message, dismissable);
    },
    parseFurniture: function (furniture) {
      return {
        id: furniture.id,
        title: furniture.title,
        tookAt: moment(furniture.took_at).fromNow(),
        picture: furniture.picture
      }
    }
  }
});