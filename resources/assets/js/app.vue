<template>
  <div>
    <div class="container-fluid"> 
      <div class="row">
        <div class="col-12">
          <canvas id="canvas" :width="baseWidth" :height="baseHeight"></canvas>
          <video playinline autoplay></video>
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
            <button @click="startRecording">record</button>
            <presets @preset="updatePreset" ref="presets"></presets>

          </form>
          <p>Song index : {{ index }}</p>
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
        blendTime: 5,
        recordedChunks: []
      }
    },

    mounted() {
      this.initPlayer();
      this.$refs.presets.setupPresets();
      this.setupVideo();
    },


    components: {
      Presets: require("./presets.vue")
    },

    methods: {

      /**
       * Starts the recording
       *
       * @return {[type]} [description]
       */
      startRecording(e) {
        e.preventDefault();
        var _this = this
        
        mediaRecorder.start();

        mediaRecorder.ondataavailable = function handleDataAvailable(event) {
          console.log("pushing data")
          console.log(event.data)
          if (event.data.size > 0) {
            _this.recordedChunks.push(event.data); 
            _this.download();
          } else {
          }
        };

        setTimeout(function() {
          mediaRecorder.stop()
        }, 5000)
      },

      /**
       * Sets up the video player
       *
       * @return {[type]} [description]
       */
      setupVideo() {
        var canvas = document.querySelector('canvas');
        var video = document.querySelector('video');
        var stream = canvas.captureStream(30)
        video.srcObject = stream;
        var options = {mimeType: 'video/webm;codecs=vp9'};
        var mediaRecorder = new MediaRecorder(stream, options);
        window.mediaRecorder = mediaRecorder
      },

      /**
       * Download the file
       *
       * @return {[type]} [description]
       */
      download() {
        var blob = new Blob(this.recordedChunks, {
          type: 'video/webm'
        });

        console.log(blob)
        var FileSaver = require('file-saver');
        setTimeout(function() {
          FileSaver.saveAs(blob, "new-test.webm");
          
        }, 2000)
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
       * Initialize the player
       *
       * @return {void}
       */
       initPlayer() {
        var _this = this;
        var audioContext = new AudioContext();
        this.audioContext = audioContext;
        var canvas = document.getElementById('canvas')
        var visualizer = butterchurn.createVisualizer(audioContext, canvas, {
          width: this.baseWidth,
          height: this.baseHeight,
          pixelRatio: window.devicePixelRatio || 1,
          textureRatio: 1,
        });

        this.visualizer = visualizer
      } 
    }
  }

  </script>