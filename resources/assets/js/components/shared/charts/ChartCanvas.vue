<template>
	<div class="chart-wrap" :style="{ height: height + 'px' }">
		<canvas ref="canvas"></canvas>
	</div>
</template>
<script>
import {
	Chart, BarController, BarElement, LineController, LineElement, PointElement,
	DoughnutController, ArcElement, CategoryScale, LinearScale, Tooltip, Legend, Filler
} from 'chart.js'

Chart.register(
	BarController, BarElement, LineController, LineElement, PointElement,
	DoughnutController, ArcElement, CategoryScale, LinearScale, Tooltip, Legend, Filler
)

export default {
	name: 'ChartCanvas',
	props: {
		type: { type: String, required: true },
		data: { type: Object, required: true },
		options: { type: Object, default: () => ({}) },
		height: { type: Number, default: 280 }
	},
	data() {
		return { chart: null }
	},
	mounted() {
		this.render()
	},
	beforeDestroy() {
		if (this.chart) this.chart.destroy()
	},
	watch: {
		data: { handler() { this.render() }, deep: true },
		options: { handler() { this.render() }, deep: true }
	},
	methods: {
		render() {
			if (this.chart) this.chart.destroy()
			this.chart = new Chart(this.$refs.canvas, {
				type: this.type,
				data: JSON.parse(JSON.stringify(this.data)),
				options: Object.assign({
					responsive: true,
					maintainAspectRatio: false,
					onClick: (evt, els) => {
						if (els.length) this.$emit('select', els[0].index)
					},
					onHover: (evt, els) => {
						evt.native.target.style.cursor = els.length ? 'pointer' : 'default'
					}
				}, this.options)
			})
		}
	}
}
</script>
<style scoped>
	.chart-wrap { position: relative; width: 100%; }
</style>
