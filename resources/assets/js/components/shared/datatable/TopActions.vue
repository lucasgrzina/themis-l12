<template>
  <div class="top-actions">
    <button v-can="actionPerm + ':C'" v-if="showNew" class="btn btn-sm bg-green" @click.prevent="doNew">
      <i class="fa fa-plus"></i> Nuevo
    </button>
    <button v-can="actionPerm + ':D'" v-if="removeSelected" class="btn btn-sm bg-red" @click.prevent="doRemoveSelected">
      <i class="fa fa-close"></i> Eliminar selección
    </button>    
  </div>
</template>

<script>

export default {
  props: {
    removeSelected: {
      type: Boolean,
      default() {
        return true;
      }
    },
    showNew: {
      type: Boolean,
      default() {
        return true;
      }
    },    
    actionPerm: {
      type: String,
      required: true
    }    
  },
  methods: {
    doNew () {
      this.$emit('vuetable:create')
    },
    doRemoveSelected () {
      this.$emit('vuetable:remove-selected')
      //this.$events.fire('dt-rem-sel')
    }
  }
}
</script>
<style>
  .top-actions{
    display:inline-block;
    float:right;
  }
</style>