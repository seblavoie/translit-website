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
            <presets @preset="updatePreset" ref="presets"></presets>

          </form>
          <p>Song index : {{ index }}</p>
          <ol>
            <li v-for="(file, i) in files" v-bind:class="{ bold : (i == index)}">
              {{ file.name }}
            </li>
          </ol>
        </div>
        <div class="col-md-4 offset-md-2">
          <ul class="list-group">
            <a href="#" class="list-group-item" :class="{'active': usingMicrophone}" @click="requestMicAudio">Connect mic</a>
            <a href="#" class="list-group-item" @click="requestFullScreen">Fullscreen</a>
            <recorder></recorder>
          </ul>
          <ul>
            <select name="devices" v-model="selectedDevice" id="">
              <option v-for="(device, index) in devices" :value="index">{{ device.label }}</option>
            </select>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<script>
  // record via https://developers.google.com/web/updates/2016/10/capture-stream and https://developers.google.com/web/updates/2016/01/mediarecorder

  import fscreen from 'fscreen';

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
        blendTime: 5,
        usingMicrophone: false,
        devices: [],
        selectedDevice: 1
      }
    },

    mounted() {
      this.initPlayer();
      this.setupDevices();
    },


    components: {
      Presets: require("./presets.vue"),
      Recorder: require("./recorder.vue")
    },

    methods: {

      /**
       * Setup devices
       *
       * @return {void}
       */
      setupDevices() {
        navigator.mediaDevices.enumerateDevices()
        .then((deviceInfos) => {
          for (var i = 0; i !== deviceInfos.length; ++i) {
            console.log(deviceInfos[i])
            this.devices.push(deviceInfos[i])
          }
        })
        .catch(() => {
          console.log("error with listing devices")
        });
      },

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
          setTimeout(() => {
            _this.visualizer.renderer.width = _this.baseWidth = $( window ).width()
            _this.visualizer.renderer.height = _this.baseHeight = $( window ).height()
            console.log(_this.baseWidth)
          }, 500)
        } else {
          this.visualizer.renderer.width = this.baseWidth = 1920
          this.visualizer.renderer.height = this.baseHeight = 1080
        }
        }


        if (fscreen.fullscreenEnabled) {
          fscreen.addEventListener('fullscreenchange', handler, false);
          fscreen.requestFullscreen(canvas);
        }
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
        reader.onload = function(event) {
          _this.audioContext.decodeAudioData(
            event.target.result,
            (buf) => {
              _this.playBufferSource(buf);
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
       * Request mic
       *
       * @param  {[type]} sourceNode
       * @param  {[type]} audioContext
       *
       * @return {}
       */
       requestMicAudio() {
        var selectedDevice = this.devices[this.selectedDevice]
        console.log(selectedDevice)

        navigator.getUserMedia({
          audio: { deviceId: {exact: selectedDevice.deviceId } }
         }, (stream) => {
          console.log(stream.getVideoTracks)
          this.usingMicrophone = true;
          var micSourceNode = this.audioContext.createMediaStreamSource(stream);
          this.connectMicAudio(micSourceNode, this.audioContext);
        }, (err) => {
          console.log('Error getting audio stream from getUserMedia');
        });
      },

      /**
       * Connect microphone
       *
       * @param  {[type]} sourceNode   [description]
       * @param  {[type]} audioContext [description]
       *
       * @return {[type]}              [description]
       */
       connectMicAudio(sourceNode, audioContext) {
        console.log(sourceNode)
        this.audioContext.resume();
        var gainNode = audioContext.createGain();
        gainNode.gain.value = 1.25;
        sourceNode.connect(gainNode);
        this.visualizer.connectAudio(gainNode);
        this.startRenderer();
      },


      /**
       * Initialize the player
       *
       * @return {[type]} [description]
       */
       initPlayer() {
        var audioContext = new AudioContext();
        this.audioContext = audioContext;
        this.sendContextToViz()
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