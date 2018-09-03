<template>
  <span>
    <a href="#" class="nav-link" :class="{'active': usingMicrophone}" @click="requestMicAudio" id="popoverMicrophone"><i class="fas fa-microphone-alt fa-2x"></i>{{ selectedDevice }}</a>

    <b-popover target="popoverMicrophone" triggers="focus">
       <ul class="nav flex-column nav-pills">
         <li class="nav-item" v-for="(device, index) in devices">
           <a href="#" class="nav-link" :class="{'active': selectedDevice == index}" @click="changeDevice(index)">{{ device.label }}</a>
         </li>
       </ul>
    </b-popover>
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
        selectedDevice: 0,
        usingMicrophone: false
      }
    },

    mounted() {
      this.setupDevices();
      this.setupPopover();
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

      changeDevice(index) {
        alert(index)
        this.selectedDevice = index
      },

      /**
       * Sets up the popover menu
       *
       * @return {void}
       */
      setupPopover() {
        $(function () {
          $('[data-toggle="popover"]').each(function() {
            console.log("popover")
          })
          $('[data-toggle="popover"]').popover({
            html: true,
            container: "body",
            // trigger: "hover",
            content: function() {
              var content = $(this).attr("data-popover-content");
              return $(content).html();
            },
            title: function() {
              var title = $(this).attr("data-popover-heading");
              return $(title).children(".popover-heading").html();
            }

          }).on('show.bs.popover', function() {
            $('#popper-content').addClass('show')
          })
        })
      },

      /**
       * Setup devices
       *
       * @return {void}
       */
      setupDevices() {
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
        var selectedDevice = this.devices[this.selectedDevice]
        var preciseDeviceConstraint = { deviceId: {exact: this.activeDevice.deviceId } }
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