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
        usingMicrophone: false
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
    }
  }

</script>