<template>
  <div>
    <a href="#" class="list-group-item" @click="startRecording" v-if="!recording">Start recording</a>
    <a href="#" class="list-group-item" @click="stopRecording" v-if="recording">Stop recording</a>
    <video playinline autoplay></video>
  </div>
</template>

<script>
  // record via https://developers.google.com/web/updates/2016/10/capture-stream and https://developers.google.com/web/updates/2016/01/mediarecorder
  export default {
    name: "recorder",

    data() {
      return {
        recordedChunks: [],
        recording: false
      }
    },

    mounted() {
      this.setupVideo();
    },

    props: [

    ],

    methods: {

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

      /**
       * Stop recording
       *
       * @param  {[type]} e
       * @return {[type]}
       */
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

        var FileSaver = require('file-saver');
        setTimeout(function() {
          FileSaver.saveAs(blob, "new-test.webm");

        }, 2000)
      }
    }
  }

</script>