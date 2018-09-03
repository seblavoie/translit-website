<template>
  <span>

    <a href="#" class="nav-link" :class="{'active': popoverSettingsVisible}" @click="" id="popoverSettings"><i class="fas fa-2x fa-sliders-h"></i></a>

    <b-popover target="popoverSettings" triggers="focus" placement="topleft">
      <form action="">
        <div class="form-group row">
          <div class="col">
            <select id="presetSelect" name="preset" class="form-control" v-model="presetIndex"  @change="requestPreset">
              <option v-for="(choice, index) in presetKeys" :value="index">{{ choice }}</option>
            </select>
          </div>
        </div>
        <div class="form-group row">
          <div class="col">
            <div class="form-check form-check-special">
              <input class="form-check-input" type="checkbox" id="presetCycle" v-model="presetCycle" @change="restartCycleInterval">
              <label class="form-check-label" for="cycle" v-model="presetCycle" >Cycle</label>
              <span  v-if="presetCycle"> for
                <input class="form-control col-md-3 form-control-tight" type="number" id="presetCycleLength"step="1" v-model="presetCycleLength" min="1" @change="restartCycleInterval">
                seconds
              </span>
            </div>
          </div>
        </div>

        <div class="form-group row">
          <div class="col">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="presetRandom" v-model="presetRandom">
              <label class="form-check-label" for="random">Random</label>
            </div>
          </div>
        </div>
      </form>
    </b-popover>


  </span>

   <!--
   </div> -->
 </template>

 <script>
  // app.visualizer.audioNode.context.currentTime
  export default {
    name: "presets",

    data() {
      return {
        cycleInterval: null,
        presetCycle: true,
        presetCycleLength: 15,
        presetRandom: false,
        presets: {},
        presetKeys: [],
        presetIndex: 0,
        popoverSettingsVisible: false
      }
    },

    mounted() {
      this.setEventListeners();
    },

    props: [

    ],

    methods: {

      goToPreset(index) {
        this.presetIndex = index;
        var preset = this.presets[this.presetKeys[this.presetIndex]];
        this.$emit('preset', preset);
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
          this.goToPreset(Math.floor(Math.random() * this.presetKeys.length));
        } else {
          this.goToPreset((this.presetIndex + 1) % this.presetKeys.length);
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
        this.goToPreset(((this.presetIndex - 1) + numPresets) % numPresets);
      },

      requestPreset() {
        this.goToPreset(this.presetIndex)
        this.restartCycleInterval()
      },

      /**
       * Restarts the interval
       *
       * @return {void}
       */
       restartCycleInterval() {
        clearInterval(this.cycleInterval);
        if (this.cycleInterval) {
          this.cycleInterval = null;
        }

        if (this.presetCycle) {
          this.cycleInterval = setInterval(() => this.nextPreset(2.7), this.presetCycleLength * 1000);
        }
      },

      /**
       * Setup presets
       *
       * @return {void}
       */
       setupPresets() {
        var _this = this;
        this.presets = {};
        if (window.butterchurnPresets) {
          Object.assign(this.presets, butterchurnPresets.getPresets());
        }

        if (window.butterchurnPresetsExtra) {
          Object.assign(this.presets, butterchurnPresetsExtra.getPresets());
        }

        this.presets = _(this.presets).toPairs().sortBy(([k, v]) => k.toLowerCase()).fromPairs().value();
        this.presetKeys = _.keys(this.presets);
        this.goToPreset(Math.floor(Math.random() * this.presetKeys.length));

        if(this.presetCycle) {
          this.cycleInterval = setInterval(function() {
            _this.nextPreset(2.7)
          }, _this.presetCycleLength * 1000
          );
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