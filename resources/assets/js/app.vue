<template>
  <div>
    <div class="wrapper">
      <canvas id="canvas" :width="baseWidth" :height="baseHeight"></canvas>
      <div class="controls">
        <ul class="pull-left nav nav-pills">
          <li class="nav-item">
            <presets @preset="updatePreset" ref="presets"></presets>
          </li>
        </ul>
        <ul class="pull-right nav nav-pills">
          <li class="nav-item">
            <a href="#" class="nav-link" @click="triggerUpload" v-b-tooltip.hover title="Upload files">
              <span class="upcoming-files">
                <span  v-for="(file, i) in files" v-bind:class="{ current : (i == index)}">
                  “{{ file.name }}”,
                </span>
              </span>
              <i class="fas fa-file-upload fa-2x"></i>
            </a>
          </li>
          <li class="nav-item">
            <microphone></microphone>
          </li>
          <li clas="nav-item">
            <fullscreen></fullscreen>
          </li>
        </ul>
      </div>
    </div>
    <div class="container" style="display: none">
      <div class="row">
        <div class="col-6">
          <form action="form-horizontal">
            <div class="form-group row">
              <input class="form-control-file" type="file" accept="audio/*" multiple id="fileInput" @change="updateFileList">
            </div>
          </form>
          <p>Song index : {{ index }}</p>
        </div>
        <div class="col-md-4 offset-md-2">
          <ul class="list-group">
            <!-- <recorder></recorder> -->
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<script>
  // record via https://developers.google.com/web/updates/2016/10/capture-stream and https://developers.google.com/web/updates/2016/01/mediarecorder

  export default {
    data() {
      return {
        baseWidth: 1920,
        baseHeight: 1080,
        visualizer: null,
        rendering: false,
        audioContext: null,
        sourceNode: null,
        delayedAudible: null,
        files: [],
        index: 0,
        blendTime: 5
      }
    },

    mounted() {
      this.initPlayer();
      this.resizeCanvas();
    },


    components: {
      Presets: require("./presets.vue"),
      Recorder: require("./recorder.vue"),
      Fullscreen: require("./fullscreen.vue"),
      Microphone: require("./microphone.vue")
    },

    computed: {
      // currentFile
    },

    methods: {

      /**
       * Force resize the canvas
       *
       * @return {void}
       */
      resizeCanvas() {
        this.visualizer.renderer.width = this.baseWidth = $( window ).width()
        this.visualizer.renderer.height = this.baseHeight = $( window ).height()
      },

      /**
       * Updates preset
       *
       * @param  {Object} preset
       *
       * @return {void}
       */
       updatePreset(preset) {
        this.visualizer.loadPreset(preset, this.blendTime);
      },

      /**
       * Connect to audio analyser
       *
       * @param  {[type]} sourceNode [description]
       *
       * @return {[type]}            [description]
       */
       connectToAudioAnalyzer(sourceNode) {
        if(this.delayedAudible) {
          this.delayedAudible.disconnect();
        }

        this.delayedAudible = this.audioContext.createDelay();
        this.delayedAudible.delayTime.value = 0.1;

        this.sourceNode.connect(this.delayedAudible)
        this.delayedAudible.connect(this.audioContext.destination);

        this.visualizer.connectAudio(this.delayedAudible);
      },

      /**
      * [startRenderer description]
      *
      * @return {void}
      */
      startRenderer() {
        var _this = this;
        requestAnimationFrame(function() {
          _this.startRenderer()
        });
        this.visualizer.render();
      },

      triggerUpload() {
        $("#fileInput").trigger("click");
      },

      /**
       * Play buffer source
       *
       * @param  {} buffer
       *
       * @return {void}
       */
       playBufferSource(buffer) {
        if (!this.rendering) {
          this.rendering = true;
          this.startRenderer();
        }

        if (this.sourceNode) {
          this.sourceNode.disconnect();
        }

        this.sourceNode = this.audioContext.createBufferSource();
        this.sourceNode.buffer = buffer;
        this.connectToAudioAnalyzer(this.sourceNode);

        this.sourceNode.start(0);
      },

      /**
       * Update file list upon file upload
       *
       * @param  {event} event
       *
       * @return {void}
       */
       updateFileList(event) {
        for(let i = 0; i < event.target.files.length; i++){
          this.files.push(event.target.files[i])
        }
        this.loadLocalFiles();
      },

      /**
       * load local file
       *
       * @param  {array} files
       * @param  {Number} index [description]
       *
       * @return {void}
       */
       loadLocalFiles(index = 0) {
        var _this = this;
        this.audioContext.resume();

        var reader = new FileReader();
        reader.onload = function(event) {
          _this.audioContext.decodeAudioData(
            event.target.result,
            (buf) => {
              _this.playBufferSource(buf);
              setTimeout(() => {
                if (_this.files.length > _this.index + 1) {
                              // goes to the next song
                              _this.files.shift()
                              _this.loadLocalFiles(0);
                            } else {
                              // finished
                              _this.sourceNode.disconnect();
                              _this.sourceNode = null;
                            }
                          }, buf.duration * 1000);
            });
        };
        reader.readAsArrayBuffer(this.files[this.index]);
      },


      /**
       * Initialize the player
       *
       * @return {[type]} [description]
       */
       initPlayer() {
        this.audioContext = this.getAudioContext();
        this.sendContextToViz()
        var resizeTimer;
        $(window).on('resize', (e) => {
          clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
              this.resizeCanvas();
            }, 50);
        });
      },


      /**
       * Gets audio context or throws an error
       *
       * @return {AudioContext}
       */
      getAudioContext() {
        var AudioContext = window.AudioContext // Default
            || window.webkitAudioContext // Safari and old versions of Chrome
            || false;

        if (AudioContext) {
            // Do whatever you want using the Web Audio API
            return new AudioContext();
            // ...
        } else {
            // Web Audio API is not supported
            // Alert the user
            alert("Sorry, but the Web Audio API is not supported by your browser. Please, consider upgrading to the latest version or downloading Google Chrome or Mozilla Firefox");
        }
      },


      /**
       * Sets the vizualisation
       *
       * @return {void}
       */
       sendContextToViz() {
        var visualizer = butterchurn.createVisualizer(this.audioContext, document.getElementById('canvas'), {
          width: this.baseWidth,
          height: this.baseHeight,
          pixelRatio: window.devicePixelRatio || 1,
          textureRatio: 1,
        });
        this.visualizer = visualizer
        this.$refs.presets.setupPresets();
      }
    }
  }

  </script>