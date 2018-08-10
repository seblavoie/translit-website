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
            <div class="form-check row">
              <input class="form-check-input" type="checkbox" id="presetRandom" v-model="usingMicrophone">
              <label class="form-check-label" for="random">Use microphone</label>
            </div>
            <div class="form-group row">
            </div>
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
        <div class="col-md-6">
          <div class="pull-right">
            <button class="btn btn-outline-primary" @click="startRecording" v-if="!recording">Start recording</button>
            <button class="btn btn-outline-primary" @click="stopRecording" v-if="recording">Stop recording</button>
            <a href="#" @click="requestFullScreen">fullscreen</a>
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
        recordedChunks: [],
        usingMicrophone: false,
        recording: false
      }
    },

    mounted() {
      this.initPlayer();
      
      this.setupVideo();
    },


    components: {
      Presets: require("./presets.vue")
    },

    methods: {

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
       * Starts the recording
       *
       * @return {[type]} [description]
       */
       startRecording(e) {
        e.preventDefault();
        var _this = this
        this.recording = true
        mediaRecorder.start();

        mediaRecorder.ondataavailable = function handleDataAvailable(event) {
          if (event.data.size > 0) {
            _this.recordedChunks.push(event.data); 
            _this.download();
          } else {
          }
        };
      },

      stopRecording(e) {

        e.preventDefault();

        this.recording = false
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
        var options = {
          mimeType: 'video/webm;codecs=vp9',
          videoBitsPerSecond: 24024000
        };
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


      // test() {
      //   var handleSuccess = function(stream) {
      //       var context = new AudioContext();
      //       var source = context.createMediaStreamSource(stream);
      //       var processor = context.createScriptProcessor(1024, 1, 1);

      //       source.connect(processor);
      //       processor.connect(context.destination);

      //       processor.onaudioprocess = function(e) {
      //         // Do something with the data, i.e Convert this to WAV
      //         console.log(e.inputBuffer);
      //       };
      //     };

      //     navigator.mediaDevices.getUserMedia({ audio: true, video: false })
      //         .then(handleSuccess);
      // },

      initPlayer() {
        if(this.usingMicrophone) { 
          this.initPlayerForMicrophone()
        } else {
          this.initPlayerForFiles()
        }
      },


      /**
       * Initialize the player
       *
       * @return {void}
       */
       initPlayerForMicrophone() {
        var handleSuccess = (stream) => {
          var audioContext = new AudioContext();
          this.audioContext = audioContext;

          var source = audioContext.createMediaStreamSource(stream);
          var processor = audioContext.createScriptProcessor(1024, 1, 1);

          source.connect(processor);
          processor.connect(audioContext.destination);

          processor.onaudioprocess = function(e) {
              // Do something with the data, i.e Convert this to WAV
              // console.log(e.inputBuffer);
            };            
          };

          navigator.mediaDevices.getUserMedia({ audio: true, video: false })
            .then(handleSuccess);
        },


      /**
       * Initialize the player with a song
       *
       * @return {void}
       */
       initPlayerForFiles() {
        var audioContext = new AudioContext();
        this.audioContext = audioContext;
        this.sendContextToViz()
      },


      /**
       * Sets the vizualisation
       *
       * @return {void} 
       */
      sendContextToViz(audioContext) {
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