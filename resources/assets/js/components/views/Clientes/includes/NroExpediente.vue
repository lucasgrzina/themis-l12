<template>
	<div>
		<div class="" v-if="areaId == 1">
			<label>Nro. Expediente</label><br>
			<div class="part-1">
				<input type="text" ref="part1" :value="splitArea1.part_1" @change="updateNro()">
			</div>
			<div class="part-2">
				<input type="text" ref="part2" :value="splitArea1.part_2" @change="updateNro()">
			</div>
			<div class="part-3">
				<input type="text" ref="part3" :value="splitArea1.part_3" @change="updateNro()">
			</div>
			<div class="part-4">
				<input type="text" ref="part4" :value="splitArea1.part_4" @change="updateNro()">
			</div>
		</div>
		<div v-else>
			<label>Nro. Trámite</label><br>
			<div class="part-total">
				<input type="text" class="form-control" ref="partTotal" :value="splitAreaGral" @change="updateNro()">
			</div>			
		</div>
	</div>
</template>
<script>
export default {
	name: 'NroExpediente',
	props: ['areaId','value','cuit','type'],
	mounted () {
		console.log(this.cuit);
		console.log(this.type);
		console.log(this.value);
	},
  	computed: {
    	splitArea1() {
    		let _cuit_len = this.cuit.length;
			return {
				part_1: this.value.substring(0, 3),
				part_2: this.value.substring(3,_cuit_len + 3),
				part_3: this.value.substring(_cuit_len + 3,_cuit_len + 6),
				part_4: this.value.substring(_cuit_len + 6,_cuit_len + 12),
			}
	  	},
	  	splitAreaGral() {
	  		return this.value;
	  	},
	  	formattedCuit () {
	  		return (this.cuit ? this.cuit.replace(new RegExp('-', 'g'), '') : '00000000000')
	  	}
    },	
	methods: {

		updateNro() {
			switch(this.areaId) {
				case 1:
					this.$refs.part1.value = (this.$refs.part1.value.length < 3 ? ("000"+this.$refs.part1.value).slice(-3) : this.$refs.part1.value)
					this.$refs.part2.value = (this.$refs.part2.value.length === 0 ? this.formattedCuit : this.$refs.part2.value)
					this.$refs.part3.value = (this.$refs.part3.value.length < 3 ? ("000"+this.$refs.part3.value).slice(-3)  : this.$refs.part3.value)
					this.$refs.part4.value = (this.$refs.part4.value.length === 0 ? "000000"  : this.$refs.part4.value)
					
				  	this.$emit('input', this.$refs.part1.value.concat(this.$refs.part2.value).concat(this.$refs.part3.value).concat(this.$refs.part4.value))
					break;
				default:
					this.$emit('input', this.$refs.partTotal.value);
					break;
			}
		}
	}	
};
</script>
<style scoped>
	.part-1 {
		width: 30px;
	}
	.part-2 {
		width: 90px;
	}
	.part-3 {
		width: 30px;
	}
	.part-4 {
		width: 60px;
	}

	.part-1 input, .part-2 input, .part-3 input, .part-4 input {
		width: 100%;
		text-align: center;
	}
	.part-1, .part-2, .part-3, .part-4 {
		margin-right: 5px;
		display: inline-block;
	}


</style>