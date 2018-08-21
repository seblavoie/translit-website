<template>
  <span>
    <a href="#" class="nav-link" :class="{'active': usingMicrophone}" @click="requestMicAudio"><i class="fas fa-microphone-alt fa-2x"></i></a>
    <a href="#" v-show="usingMicrophone">{{ activeDevice.label }} test</a>
    <ul class="nav flex-column nav-pills">
      <select name="devices" v-model="selectedDevice" id="">
        <option v-for="(device, index) in devices" :value="index">{{ device.label }}</option>
      </select>
    </ul>
  </span>
</template>

<script>
  // record via https://developers.google.com/web/updates/2016/10/capture-stream and https://developers.google.com/web/updates/2016/01/mediarecorder
  export default {
    name: "microphone",

    data() {
      return {
        devices: [],
        selectedDevice: 1,
        usingMicrophone: false
      }
    },

    mounted() {
      this.setupDevices();
    },

    computed: {
      activeDevice() {
        if(this.devices.length > 0) {
          return this.devices[this.selectedDevice]
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
       * Request mic
       *
       * @param  {[type]} sourceNode
       * @param  {[type]} audioContext
       *
       * @return {}
       */
       requestMicAudio() {
        var selectedDevice = this.devices[this.selectedDevice]
        var preciseDeviceConstraint = { deviceId: {exact: selectedDevice.deviceId } }
        var constraints = (selectedDevice.label == "" ? {audio: true} : preciseDeviceConstraint)
        navigator.getUserMedia({
          audio: constraints
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
        console.log(sourceNode)
        console.log("connect mic audio")

        console.log("audiocontext : ")
        console.log(this.$parent.audioContext)
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