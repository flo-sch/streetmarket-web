<template>
  <section class='map-container full'>
    <div class='map-canvas'></div>
  </section>
</template>

<script>
  var Vue = require('vue');

  var Map = Vue.extend({
    replace: true,
    inherit: true,
    map: null,
    data: function () {
      return {
        zoomLevel: 13,
        currentInfoWindow: null,
        markers: {
          user: null,
          furnitures: [],
        },
        infoWindows: {
          furnitures: []
        }
      }
    },
    watch: {
      position: function () {
        this.setMapCenter(this.position.coords, this.zoomLevel);
        this.setUserLocationMarker(this.position.coords);

        if (this.furnitures.length > 0) {
          this.displayFurnituresMarkers();
        }
      }
    },
    events: {
      'app:furnitures:listed': function () {
        if (this.position) {
          this.displayFurnituresMarkers();
        }
      }
    },
    ready: function () {
      var Map = this;

      google.maps.event.addDomListener(window, 'load', function () {
        var latitude, longitude;

        if (Map.position && typeof Map.position === 'object' && 'coords' in Map.position) {
          latitude = Map.position.coords.latitude;
          longitude = Map.position.coords.longitude;
        } else {
          latitude = 0;
          longitude = 0;
        }

        Map.$options.map = new google.maps.Map(document.querySelector('.map-canvas'), {
          center: new google.maps.LatLng(latitude, longitude),
          zoom: Map.zoomLevel
        });
      });
    },
    methods: {
      setMapCenter: function (position, zoom) {
        if (this.$options.map) {
          this.$options.map.setCenter(new google.maps.LatLng(position.latitude, position.longitude));
          this.$options.map.setZoom(zoom);
        }
      },
      setUserLocationMarker: function (position) {
        if (this.$options.map) {
          if (this.markers.user) {
            this.markers.user.setMap(null);
            this.markers.user = null;
          }

          this.markers.user = new google.maps.Marker({
            position: new google.maps.LatLng(position.latitude, position.longitude),
            map: this.$options.map,
            icon: {
              size: new google.maps.Size(80, 80),
              scaledSize: new google.maps.Size(30, 30),
              anchor: new google.maps.Point(15, 15),
              url: '/images/map/user-location-marker.png'
            },
            title: 'Me'
          });
        }
      },
      displayFurnituresMarkers: function () {
        var markersLength = this.markers.furnitures.length;

        while (markersLength--) {
          this.markers.furnitures[markersLength].setMap(null);
          this.markers.furnitures.splice(markersLength, 1);
        }

        this.furnitures.forEach(function (furniture) {
          this.addFurnitureMarker(furniture);
        }, this);
      },
      addFurnitureMarker: function (furniture) {
        var Map = this;

        if (this.$options.map) {
          var marker, infoWindow;

          // Preload picture
          var picture = document.createElement('img');

          picture.onload = function (event) {
            marker = new google.maps.Marker({
              position: new google.maps.LatLng(furniture.latitude, furniture.longitude),
              map: Map.$options.map,
              icon: {
                size: new google.maps.Size(80, 80),
                scaledSize: new google.maps.Size(40, 40),
                anchor: new google.maps.Point(20, 20),
                url: '/images/map/furniture-location-marker.png'
              },
              title: 'Me'
            });

            Map.markers.furnitures.push(marker);

            infoWindow = new google.maps.InfoWindow({
              pixelOffset: new google.maps.Size(-20, 20),
              content: '<div class="furniture-overview">' +
                '<h4 class="furniture-title">' + furniture.title + '</h4>' +
                '<img class="furniture-picture img-responsive" src="' + furniture.picture + '" alt="' + furniture.title + '" />' +
                '<p class="furniture-date">' + furniture.tookAt + '</p>' +
                '<p>' + Map.calculateDistanceBetweenLocations(Map.position.coords, furniture) + '</>' +
              '</div>'
            });

            Map.infoWindows.furnitures.push(infoWindow);

            google.maps.event.addListener(marker, 'click', function () {
              if (Map.currentInfoWindow) {
                Map.currentInfoWindow.close();
              }

              Map.currentInfoWindow = infoWindow;
              infoWindow.open(Map.$options.map,marker);
            });
          }

          picture.onerror = function (event) {
            // Picture cannot be loaded (probably does not exists)
          }

          picture.src = furniture.picture;
        }
      }
    }
  });

  module.exports = Map;
</script>