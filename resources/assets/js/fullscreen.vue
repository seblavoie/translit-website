<template>
  <div>
    <a href="#" class="list-group-item" @click="requestFullScreen">Fullscreen</a>
  </div>
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
        var _this = this
        var handler = function handler() {
         if (fscreen.fullscreenElement !== null) {
            console.log('Entered fullscreen mode');
         //    setTimeout(() => {
         //      _this.visualizer.renderer.width = _this.baseWidth = $( window ).width()
         //      _this.visualizer.renderer.height = _this.baseHeight = $( window ).height()
         //      console.log(_this.baseWidth)
         //    }, 500)
          } else {
         //    this.visualizer.renderer.width = this.baseWidth = 1920
         //    this.visualizer.renderer.height = this.baseHeight = 1080
          }
        }

        if (fscreen.fullscreenEnabled) {
          fscreen.addEventListener('fullscreenchange', handler, false);
          fscreen.requestFullscreen(canvas);
        }
      },
    }
  }

</script>