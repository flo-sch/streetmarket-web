<template>
  <section class='furnitures container'>
    <h1>Bons plans gratuits :)</h1>
    <div class='row'>
      <article class='furniture col-xs-6 col-sm-4 col-md-3' v-repeat='furniture: furnitures'>
        <img class='furniture-picture img-responsive' v-attr='src: "/" + furniture.picture' alt='{{ furniture.title }}' />
        <h4 class='furniture-title'>{{ furniture.title }}</h4>
        <p class='furniture-date'>{{ furniture.tookAt }}</p>
      </article>
    </div>
  </section>
</template>

<script>
  var Vue = require('vue');

  var FurnituresList = Vue.extend({
    replace: true,
    inherit: true
  });

  module.exports = FurnituresList;
</script>