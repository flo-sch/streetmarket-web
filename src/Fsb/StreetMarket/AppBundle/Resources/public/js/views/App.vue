<script>
  var moment = require('moment');
  var StreetMarketClient = require('../api/StreetMarketClient.js');

  var Alert = require('./Alert.vue');
  var Camera = require('./Camera.vue');
  var FurnituresList = require('./FurnituresList.vue');

  module.exports = {
    el: 'body',
    data: {
      view: 'furnitures',
      isCompatible: true,
      isLocated: false,
      isRecording: false,
      isLookingForSources: false,
      isTaken: false,
      title: '',
      picture: null,
      position: null,
      sources: [],
      currentSource: 0,
      furnitures: []
    },
    ApiClient: null,
    ready: function () {
      if (typeof navigator.getUserMedia !== 'function' || typeof window.URL !== 'function'  || typeof window.URL.createObjectURL !== 'function') {
        this.isCompatible = false;
        this.displayAlert('warning', 'Your browser does not allow you to take geolocated pictures.', true);
      } else {
        this.getMediaSources();
      }

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
        var message = 'Something went wrong... Please, try again!';

        switch (status) {
          case 400:
            message = 'Something is not working. please, contact us!';
            break;
          case 500:
            message = 'Something is not working... Please, try again!';
            break;
          case 503:
            message = 'Our server is actually unavailable... Please try again later!';
            break;
        }

        this.displayAlert('danger', 'Oh snap! ' + message, true);
      },
      'app:geolocation:found': function () {
        this.isLocated = true;
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
    components: {
      'camera': Camera,
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
            } else {
              this.$emit('app:api:error', response, status);
            }
          }, this);
        }
      },
      definePosition: function () {
        var App = this;

        navigator.geolocation.getCurrentPosition(function (position) {
          App.position = position;

          App.$emit('app:geolocation:found');
        }, function (error) {
          App.$emit('app:geolocation:canceled', error);
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

          this.$broadcast('app:source:change', this.sources[this.currentSource]);
        }
      },
      getMediaSources: function () {
        var App = this;

        if (typeof MediaStreamTrack === 'undefined' || typeof MediaStreamTrack.getSources !== 'function') {
          this.isLookingForSources = false;
        } else {
          this.isLookingForSources = true;

          MediaStreamTrack.getSources(function (sources) {
            sources.forEach(function (source) {
              if (source.kind === 'video') {
                App.sources.push(source.id);
              }
            });

            App.isLookingForSources = false;
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
  }
</script>