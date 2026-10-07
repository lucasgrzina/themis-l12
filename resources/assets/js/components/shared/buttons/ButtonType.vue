<!--template>
	<button type="button" @click="clickButton" :class="theClass">
		<i :class="[{'fa fa-spinner fa-spin': loading},icon]" aria-hidden="true"></i>{{ text }}
	</button>
</template-->
<script>
	export default {
		props: {
			type: {
			  type: String,
			  required: true
			},
			submit: {
				type: Boolean,
				default: false
			},
			promise: {
			  type: Function,
			  required: false
			},
			caption: {
				
			}			
		},
		render(h) {
			return h (
				'button',
				{
					attrs: {
						type: this.submit ? 'submit' : 'button',	
					},
					class: this.theClass,
					on: this.events
				},
				[
					h (
						'i',
						{
							class: [{'fa fa-spinner fa-spin': this.loading},this.icon]
						}
					),
					this.text
				]
			)
		},
		data() {
			return {
				theClass: 'btn btn-sm',
				icon: 'fa fa-',
				text: '',
				loading: false,
				events: { click: this.clickButton }
			}
		},
		mounted() {
			switch(this.type) {
				case 'save':
					this.theClass+= ' bg-green';
					this.icon+= 'floppy-o';
					this.text = ' Guardar';
					break;
				case 'refresh':
					this.theClass+= ' btn-default';
					this.icon= '';
					this.text = 'Refrescar';
					break;					
				case 'close':
					this.theClass+= ' bg-navy';
					this.icon+= 'times';
					this.text = ' Cerrar';
					break;
				case 'add-list':
					this.theClass= 'btn btn-xs bg-green';
					this.icon+= 'plus';
					break;					
				case 'edit-list':
					this.theClass= 'btn btn-xs bg-purple';
					this.icon+= 'pencil';
					break;		
				case 'view-list':
					this.theClass= 'btn btn-xs bg-gray';
					this.icon+= 'eye';
					break;						
				case 'remove-list':
					this.theClass = 'btn btn-xs bg-red';
					this.icon += 'trash-o';
					break;
				case 'download-list':
					this.theClass = 'btn btn-xs bg-teal';
					this.icon += 'download';
					break;
				case 'close-list':
					this.theClass = 'btn btn-xs bg-navy';
					this.icon+= 'times';
					break;		
				case 'descartar-list':
					this.theClass = 'btn btn-xs bg-navy';
					this.icon+= 'times';
					break;													
				case 'login':
				case 'forgot':
					this.theClass+= ' btn-primary btn-block'
					this.icon += (this.type === 'login' ? 'sign-in' : 'arrow-circle-o-right')
					this.text = (this.type === 'login' ? ' Acceder' : ' Recuperar')
					break;
				case 'add':
					this.theClass+= ' bg-purple';
					this.icon+= 'plus';
					this.text = ' Agregar';
					break;
				case 'back':
					this.theClass+= ' bg-purple';
					this.icon+= 'arrow-left';
					this.text = ' Volver';
					break;
				case 'new':
					this.theClass+= ' bg-green';
					this.icon+= 'plus';
					this.text = ' Nuevo';
					break;	
				case 'inic-tramite':
					this.theClass+= ' bg-aqua-active';
					this.icon+= 'file';
					this.text = ' Iniciar Tramite';
					break;		
				case 'ver-tramite':
					this.theClass+= ' bg-aqua-active';
					this.icon+= 'file';
					this.text = ' Ver Tramite N° ' + this.caption;
					break;														
				case 'historicos':
					this.theClass+= ' bg-orange-active';
					this.icon+= 'folder';
					this.text = ' Ver Historicos';
					break;
				case 'tramites':
					this.theClass+= ' bg-orange';
					this.icon+= 'folder-open';
					this.text = ' Ver Trámites';
					break;	
				case 'filter':
					this.theClass+= ' bg-purple';
					this.icon+= 'search';
					this.text = ' Filtrar';
					break;	
				case 'enviar-mail-app':
					this.theClass+= ' btn-app';
					this.icon+= 'envelope';
					this.text = ' Enviar por e-mail';

					break;
				case 'print':
					this.theClass+= ' btn-default';
					this.icon+= 'print';
					this.text = '';
					break;																	
			}	

			this.theClass+= ' btn-' + this.type
		},
		methods: {
			clickButton() {
				if (this.promise && !this.loading) {
					this.loading = true;
					return this.promise()
						.then((response) => {
							this.loading = false
							return response
						},(error) => {
							this.loading = false
							return error								
						})

				} else {
					this.$emit('click',this);
				}
			}
		}
	}
</script>