<template>
	<div class="callout callout-danger modal-errors" v-show="hasErrors()">
        <template v-if="multipleErrors()">
	        <ul>
	    		<li v-for="error in messages">{{ err }}</li>
	        </ul>
        </template>
        <span v-else>{{ messages }}</span>
    </div>	
</template>
<script>
	export default {
	  props: {
	    messages: {
			type: [String,Array],
			default() {
				return '';
			}
	    }    
	  },

	  methods: {
	  	hasErrors() {
	  		return (typeof this.messages === 'string' && this.messages) || (typeof this.messages === 'array' && this.messages.lenght > 0)
	  	},
	  	multipleErrors() {
	  		return typeof this.messages === 'array'
	  	}
	  	
	  }
	}
</script>
