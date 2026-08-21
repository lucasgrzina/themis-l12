<template>
    <li class="dropdown notifications-menu">
      <router-link tag="a" class="pageLink" to="/avisos" v-if="type === 'avisos'">
          <i class="fa fa-bell-o"></i>
          <span class="label label-warning" v-if="authUser.cant_avisos > 0">{{ authUser.cant_avisos }}</span>
      </router-link>
      <router-link tag="a" class="pageLink" to="/vencimientos" v-if="type === 'vencimientos'">
          <i class="fa fa-calendar"></i>
          <span class="label label-danger" v-if="authUser.vencimientos.length > 0">{{ authUser.vencimientos | count }}</span>
      </router-link>
    </li>
</template>
<script>
	import { mapState } from 'vuex'

	export default {
		name: 'TopMenuAvisos',
		props: {
			type: {
				type: String,
				required: true
			},
		},
		data () {
			return {
				interval: {
					avisos: null,
					vencimientos: null
				}
			}
		},
		created () {
			var _this = this;
				
				switch(this.type) {
					case 'avisos':
						/*_this.$store.dispatch('getAvisos');  
						this.interval.avisos = setInterval(function(){
							_this.$store.dispatch('getAvisos');  
						},(60 * 1000 * 60));*/
						break;
					case 'vencimientos':
						/*_this.$store.dispatch('getVencimientos');  
						this.interval.vencimientos = setInterval(function(){
							_this.$store.dispatch('getVencimientos');  
						},(60 * 1000 * 60));*/
						break;						
				}
		},
		beforeDestroy () {
				var _this = this;
				//console.debug('unmounted');
				switch(this.type) {
					case 'avisos':
						//clearInterval(this.interval.avisos);
						break;
					case 'vencimientos':
						//clearInterval(this.interval.vencimientos);
						break;						
				}
		},		
  		computed: {
	    	...mapState([
	      		'authUser'
	    	])
	    }				
	};
</script>