<template>
  <div>
    <a href="#" class="list-group-item" :class="{'active': usingMicrophone}" @click="requestMicAudio">Connect mic</a>
    <ul>
      <select name="devices" v-model="selectedDevice" id="">
        <option v-for="(device, index) in devices" :value="index">{{ device.label }}</option>
      </select>
    </ul>
  </div>
</template>

<script>
  // record via https://developers.google.com/web/updates/2016/10/capture-stream and https://developers.google.com/web/updates/2016/01/mediarecorder
  export default {
    name: "recorder",

    data() {
      return {
        devices: [],
        selectedDevice: 1,
        usingMicrophone: false,
        aduioContext: null
      }
    },

    mounted() {
      this.setupDevices();
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
        console.log(selectedDevice)
        var preciseDeviceConstraint = { deviceId: {exact: selectedDevice.deviceId } }
        var constraints = (selectedDevice.label == "" ? {audio: true} : preciseDeviceConstraint)
        console.log(constraints)
        console.log(this.$parent.audioContext)
        navigator.getUserMedia({
          audio: constraints
         }, (stream) => {
          this.usingMicrophone = true;
          var micSourceNode = this.$parent.audioContext.createMediaStreamSource(stream);
          console.log("audiocontext : ")
          console.log(this.$parent.audioContext)
          this.connectMicAudio(micSourceNode, this.$parent.audioContext);
          console.log(stream)
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