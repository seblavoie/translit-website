<template>
  <span>
    <a href="#" class="nav-link" :class="{'active': usingMicrophone}" @click="requestMicAudio()" id="popoverMicrophone"  v-b-tooltip.hover title="Connect microphone">
      <i class="fas fa-microphone-alt fa-2x"></i>
    </a>
  </span>
</template>

<script>
  // record via https://developers.google.com/web/updates/2016/10/capture-stream and https://developers.google.com/web/updates/2016/01/mediarecorder
  export default {
    name: "microphone",

    data() {
      return {
        devices: [],
        devicesMenuVisible: false,
        selectedDeviceIndex: 0,
        usingMicrophone: false
      }
    },

    mounted() {
      this.storeAudioDevices();
    },

    computed: {
      activeDevice() {
        if(this.devices.length > 0) {
          return this.devices[this.selectedDeviceIndex]
        } else {
          return { label: "" }
        }
      }
    },

    props: [

    ],

    methods: {
      /**
       * Setup devices
       *
       * @return {void}
       */
      storeAudioDevices() {
        this.devices = [];
        navigator.mediaDevices.enumerateDevices()
        .then((deviceInfos) => {
          for (var i = 0; i !== deviceInfos.length; ++i) {
            if(deviceInfos[i].kind == "audioinput") {
              this.devices.push(deviceInfos[i])
              console.log(this.devices)
            }
          }
        })
        .catch(() => {
          console.log("error with listing devices")
        });
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
        // var preciseDeviceConstraint = { deviceId: {exact: this.activeDevice.deviceId } }
        // var constraints = (this.activeDevice.label == "" ? {audio: true} : preciseDeviceConstraint)

        if(this.activeDevice.label == "") {

        } else {
            // should fix here!
        }
        this.connectUserMedia()
      },

      connectUserMedia() {
        navigator.getUserMedia({
          audio: true
         }, (stream) => {
          var micSourceNode = this.$parent.audioContext.createMediaStreamSource(stream);
          this.connectMicAudio(micSourceNode, this.$parent.audioContext);
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
        console.log("connectMicAudio")
        this.usingMicrophone = true;
        this.$parent.audioContext.resume();
        var gainNode = this.$parent.audioContext.createGain();
        gainNode.gain.value = 1.25;
        sourceNode.connect(gainNode);
        this.$parent.visualizer.connectAudio(gainNode);
        this.$parent.startRenderer();
      },
    }
  }

</script>
