<template>
<div class="input-group radio-btn-group">
	<div class="btn-group">
		<a v-for="opt in options" class="btn btn-sm" :class="{'active bg-purple': opt.value == value,'bg-gray':opt.value != value}" @click="updateValue(opt.value)">{{ opt.name }}</a>
	</div>
</div>
</template>
<script>
export default {
  props: {
  	value: {
  		required: true
  	},
  	options: {
  		type: Array,
  		required: false,
  		default() {
  			return [{
  				name: 'SI',
  				value: true
  			},{
  				name: 'NO',
  				value: false
  			}]
  		}
  	},
  	selectedValue: {
  		required: false
  	}
  },
  mounted() {
  },
  methods: {
    updateValue(value) {
    	if (value === 'true' || value === 'false') {
    		value = value === 'true'
    	}
      	this.$emit('input', value)
    }
  }
};	
</script>
<style>
	
</style>