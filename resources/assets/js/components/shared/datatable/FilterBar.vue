<template>
  <div class="filter-bar">
      <form class="form-inline">
          <div class="input-group input-group-sm" style="max-width: 250px;">
            <input type="text" v-model="filterText" class="form-control input-sm pull-right" @keyup.enter="doFilter">
            <div class="input-group-btn">
              <button class="btn btn-xs btn-flat bg-purple" @click.prevent="doFilter"><i class="fa fa-search"></i></button>
              <button class="btn btn-xs btn-flat btn-default" @click.prevent="resetFilter"><i class="fa fa-close"></i></button>
              
            </div>
          </div>        
          
      </form>    
  </div>
</template>

<script>
export default {
  data () {
    return {
      filterText: ''
    }
  },
  methods: {
    doFilter () {
      this.$emit('vuetable:filter-set',this.filterText)
      //this.$events.fire('filter-set', this.filterText)
    },
    resetFilter () {
      this.filterText = ''
      this.$emit('vuetable:filter-reset')
      //this.$events.fire('filter-reset')
    }
  }
}
</script>
<style>
  .filter-bar {
    display: inline-block;
  }
</style>