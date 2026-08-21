<template>
  <div class="top-actions">
    <button v-if="showButton && cliente" class="btn btn-sm bg-green" @click.prevent="doNew">
      <i class="fa fa-plus"></i> Nuevo
    </button>
  </div>

    <!--button v-can="actionPerm + ':D'" v-if="removeSelected" class="btn btn-sm bg-red" @click.prevent="doRemoveSelected">
      <i class="fa fa-close"></i> Eliminar selección
    </button-->    
  </div>
</template>

<script>
import { dropdown } from 'vue-strap'
export default {
  components: {
    dropdown
  },
  props: {
    showButton: {
      type: Boolean,
      required: true
    },
    cliente: {
      type: Object,
      default: function() {
        return null
      }
    }    
  },
  methods: {
    doNew () {
      this.$emit('time-referencias:create')
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