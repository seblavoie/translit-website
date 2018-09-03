<template>
  <span>
    <a href="#" class="nav-link" @click="requestFullScreen"  v-b-tooltip.hover title="Fullscreen mode">
      <span class="fas fa-arrows-alt fa-2x" data-fa-transform="rotate-45"></span>
    </a>
  </span>
</template>

<script>
  import fscreen from 'fscreen';

  export default {
    name: "fullscreen",

    data() {
      return {
      }
    },

    mounted() {

    },

    props: [

    ],

    methods: {

      /**
       * Request full screen
       *
       * @return {[type]} [description]
       */
       requestFullScreen() {
        var _this = this;
        var handler = function handler() {
          setTimeout(() => { _this.$parent.resizeCanvas() }, 100)
        }

        if (fscreen.fullscreenEnabled) {
          fscreen.addEventListener('fullscreenchange', handler, false);
          fscreen.requestFullscreen(canvas);
        }
      },
    }
  }

</script>