<template>
  <div class="top-actions">
  <div class="dropdown" v-can="actionPerm + ':C'">
    <button id="dLabel" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-sm bg-green">
      <i class="fa fa-plus"></i> Nuevo
      <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" aria-labelledby="dLabel">
        <li><a @click.prevent="doNew('H')">Humano</a></li>
        <li><a @click.prevent="doNew('J')">Jurídico</a></li>
    </ul>
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
    removeSelected: {
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
    doNew (personeria) {
      this.$emit('vuetable:create',personeria)
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