<template>
  <div>

    <div class="form-group row">
      <label class="col-md-2 col-form-label" for="preset">Preset: </label>
      <div class="col-md-4">
        <select id="presetSelect" name="preset" class="form-control" v-model="presetIndex">
          <option v-for="(choice, index) in presetKeys" :value="index">{{ choice }}</option> 
        </select>
      </div>
    </div>

    <div class="form-check form-check-special row">
      <input class="form-check-input" type="checkbox" id="presetCycle" v-model="presetCycle">
      <label class="form-check-label" for="cycle">Cycle</label>
      <span  v-if="presetCycle"> for 
        <input class="form-control col-md-2" type="number" id="presetCycleLength"step="1" v-model="presetCycleLength" min="1" @change="restartCycleInterval"> 
        seconds
      </span>
    </div>

    <div class="form-check row">
      <input class="form-check-input" type="checkbox" id="presetRandom" v-model="presetRandom">
      <label class="form-check-label" for="random">Random</label>
    </div>
    
  </div>
</template>

<script>
  // app.visualizer.audioNode.context.currentTime
  // record via https://developers.google.com/web/updates/2016/10/capture-stream and https://developers.google.com/web/updates/2016/01/mediarecorder
  export default {
    name: "presets",



  }

</script>