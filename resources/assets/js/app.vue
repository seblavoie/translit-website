<template>
  <div>
    <div class="container-fluid"> 
      <div class="row">
        <div class="col-12">
          <canvas id="canvas" :width="baseWidth" :height="baseHeight"></canvas>
        </div> 
      </div>
    </div>
    <div class="container">
      <div class="row">
        <div class="col-6">
          <form action="form-horizontal">
            <div class="form-group row">
              <input class="form-control-file" type="file" accept="audio/*" multiple id="fileInput" @change="updateFileList">
            </div>

            <presets></presets>

          </form>
          <p>Song index : {{ index }}</p>
          <p>Preset index : {{ presetIndex }}</p>
          <ol>
            <li v-for="(file, i) in files" v-bind:class="{ bold : (i == index)}">
              {{ file.name }}
            </li>
          </ol>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  // app.visualizer.audioNode.context.currentTime
  // record via https://developers.google.com/web/updates/2016/10/capture-stream and https://developers.google.com/web/updates/2016/01/mediarecorder
  export default {
    data() {
      return {
        baseWidth: 2880,
        baseHeight: 1620,
        visualizer: null,
        rendering: false,
        audioContext: null,
        sourceNode: null,
        delayedAudible: null,
        cycleInterval: null,
        presets: {},
        presetKeys: [],
        presetIndex: 0,
        presetCycle: true,
        presetCycleLength: 15,
        presetRandom: true,
        canvas: document.getElementById('canvas'),
        files: [],
        index: 0,
        blendTime: 5
      }
    },

    mounted() {
      this.setupPresets();
      this.setEventListeners();
      this.initPlayer();
      // this.startRec();
    },

    watch: {
      presetIndex: function(val) {
        this.visualizer.loadPreset(this.presets[this.presetKeys[this.presetIndex]], this.blendTime);
      }
    },

    components: {
      PresetsComponent: require("./presets.vue")
    },

    methods: {
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
       * Start rec
       *
       * @return {void}
       */
      // startRec() {
      //   var capturer = new CCapture( { format: 'png' } );
      //   capturer.start();
      // },

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
       * Update file list
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
        console.log(reader)
        reader.onload = (event) => {
          _this.audioContext.decodeAudioData(
            event.target.result,
            (buf) => {
              _this.playBufferSource(buf);
              console.log("second")
              setTimeout(() => {
                if (_this.files.length > _this.index + 1) {
                              // goes to the next song
                              this.loadLocalFiles(_this.index + 1);
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
       * Setup presets
       *
       * @return {void}
       */
       setupPresets() {
        this.presets = {};

        if (window.butterchurnPresets) {
          Object.assign(this.presets, butterchurnPresets.getPresets());
        }

        if (window.butterchurnPresetsExtra) {
          Object.assign(this.presets, butterchurnPresetsExtra.getPresets());
        }

        this.presets = _(this.presets).toPairs().sortBy(([k, v]) => k.toLowerCase()).fromPairs().value();
        this.presetKeys = _.keys(this.presets);
        this.presetIndex = Math.floor(Math.random() * this.presetKeys.length);
      },

      /**
       * Initialize the player
       *
       * @return {void}
       */
       initPlayer() {
        var _this = this;
        this.setupPresets();
        this.audioContext = new AudioContext();

        this.visualizer = butterchurn.createVisualizer(this.audioContext, canvas , {
          width: this.baseWidth,
          height: this.baseHeight,
          pixelRatio: window.devicePixelRatio || 1,
          textureRatio: 1,
        });

        this.cycleInterval = setInterval(function() {
          _this.nextPreset(2.7)
        }, _this.presetCycleLength * 1000
        );
      },

      /**
       * Next preset
       *
       * @param  {Number} blendTime
       *
       * @return {void}
       */
       nextPreset() {
        if (this.presetRandom) {
          this.presetIndex = Math.floor(Math.random() * this.presetKeys.length);
        } else {
          this.presetIndex = (this.presetIndex + 1) % this.presetKeys.length;
        }
      },

      /**
       * Prev preset
       *
       * @param  {Number} blendTime
       *
       * @return {void}
       */
       prevPreset() {
        var numPresets = this.presetKeys.length;
        this.presetIndex = ((this.presetIndex - 1) + numPresets) % numPresets;
      },

      /**
       * Restarts the interval
       *
       * @return {void}
       */
       restartCycleInterval() {
        if (this.cycleInterval) {
          clearInterval(this.cycleInterval);
          this.cycleInterval = null;
        }

        if (this.presetCycle) {
          this.cycleInterval = setInterval(() => this.nextPreset(2.7), this.presetCycleLength * 1000);
        }
      },

      /**
       * Set event listeners
       */
       setEventListeners() {
        var _this = this;
        $(document).keydown((e) => {
          if (e.which === 32 || e.which === 39) {
            _this.nextPreset();
          } else if (e.which === 8 || e.which === 37) {
            _this.prevPreset();
          } else if (e.which === 72) {
            _this.nextPreset(0);
          }
        });
      }
    }
  }

</script>