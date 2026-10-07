<template>
	<div>
		<div v-if="!amModal.show">
			<div class="box" v-if="hasAnyPerm(actionPerm+':R')">
				<div class="box-header">
					<h3 class="box-title">{{ title }}</h3>
					<div class="box-tools">
						<div class="dropdown" v-can="[actionPerm+':U',actionPerm+':C']">
							<button id="dLabel" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-sm bg-green">
								<i class="fa fa-plus"></i> Nuevo
								<span class="caret"></span>
							</button>
							<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dLabel">
									<li v-for="area in authUser.areas">
										<a @click.prevent="create(area.area_id)">{{ area.area.nombre }}</a>
									</li>
							</ul>
						</div>  
						<button-type type="refresh" @click="getList()"></button-type>            	
					</div>
				</div>
				<div class="box-body no-padding">
					<div class="table-responsive">
						<table class="table table-striped">
							<tbody>
								<tr>
									<th style="width: 50px">Nro. Req.</th>
									<th>Area</th>
									<th>Responsables</th>
									<th>T. Tramite</th>
									<th>Autos</th>
									<th>Estado</th>
									<th>Recomienda</th>
									<th></th>
								</tr>
								<tr v-if="!list.loading && list.data.length > 0" v-for="(value,index) in list.data">
									<td>{{ value.id }}</td>
									<td v-html="$options.filters.areaLabel(value.area_id)"></td>
									<td v-html="responsables(value.responsables)"></td>
									<td>{{ value.tipo_tramite.nombre }}</td>
									<td>{{ value.autos }}</td>
									<td>{{ value.estado.nombre }}</td>
									<td>{{ value.recomendado }}</td>
									<td style="text-align: right;" nowrap="">
										<button-type v-can="[actionPerm+':R']" type="view-list" @click="onAction('view', value, index)"/>
										<button-type v-can="[actionPerm+':U',actionPerm+':C']" type="edit-list" @click="onAction('edit', value, index)"/>
										<button-type v-can="[actionPerm+':D']" type="remove-list" @click="onAction('remove',value, index)"/>
									</td>
								</tr>
								<tr v-if="list.data.length < 1 && !list.loading">
									<td colspan="7">El cliente no tiene requerimientos asignados.</td>
								</tr>
								<tr v-if="list.loading">
									<td colspan="7">
										<pulse-loader :loading="list.loading"></pulse-loader>
									</td>
								</tr>			            	
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="box" v-else>
				<div class="box-body">
					<unauthorized-alert/>
				</div>
			</div>	
			<div class="row">
				<div class="col-xs-12 text-right">
					<button-type type="back" @click="back()"/>        
				</div>
			</div>
		</div>
		<form id="frm" v-if="amModal.show" @submit.prevent="save()" :data-vv-scope="'requerimientos'">
			
			<template v-if="amModal.loaded">
				<div class="row">
					<div class="col-xs-12">
						<modal-errors :messages="amModal.errors"/>
					</div>
					<div class="col-xs-12 text-right">
						<button-group v-model="amModal.section" type="default">
							<radio selected-value="G">General</radio>
							<radio selected-value="D">Documentación</radio>
						</button-group>			            				
					</div>	
				</div>		
				<div class="clearfix"></div>
				<fieldset class="transparente" :disabled="!amModal.canSave">	
				<div class="row" v-show="amModal.section === 'G'">
						<div class="col-xs-12">
							<div class="box box-default">
											
											<div class="box-header with-border">
												<h3 class="box-title">General</h3>
											</div>
											<!-- /.box-header -->
											<div class="box-body">
												
												<div class="row">
													<div class="form-group col-sm-6" :class="{'has-error': errors.has('requerimientos.tipo_tramite_id')}">
														<label for="tipo_tramite_id">Tipo de Trámite</label>
														<v-select
															v-model="selectedItem.tipo_tramite"
															:options="info.tipo_tramites"
															:on-change="onChangeTipoTramite"
															placeholder="Ingresa el tipo de trámite"
															label="nombre"
															:disabled="selectedItem.tramite_id > 0 ? true : false"
														>
														</v-select>
														<span class="help-block" v-show="errors.has('requerimientos.tipo_tramite_id')">{{ errors.first('requerimientos.tipo_tramite_id') }}</span>
													</div>
													<div class="form-group col-sm-6" :class="{'has-error': errors.has('requerimientos.estado_req_id')}">
														<label for="estado_req_id">Estado</label>
														<v-select
															v-model="selectedItem.estado"
															:options="info.estado_req"
															:on-change="onChangeEstadoReq"
															placeholder="Ingresa el estado"
															label="nombre"
															:disabled="!amModal.canSave"
														>
														</v-select>
														<span class="help-block" v-show="errors.has('requerimientos.estado_req_id')">{{ errors.first('requerimientos.estado_req_id') }}</span>
													</div>
												</div>
												<div class="row" v-if="mostrarAutosParte()">
													<div class="form-group col-sm-8" :class="{'has-error': errors.has('requerimientos.autos')}">
														<label for="autos">Autos</label>
														<input type="text" name="autos" v-model="selectedItem.autos" class="form-control"  data-vv-validate-on="none">
														<span class="help-block" v-show="errors.has('requerimientos.autos')">{{ errors.first('requerimientos.autos') }}</span>
													</div>
													<div class="form-group col-sm-4" :class="{'has-error': errors.has('requerimientos.parte')}">
														<label for="parte">Parte</label>
														<select v-model="selectedItem.parte" class="form-control" name="parte"  data-vv-validate-on="none">
															<!--option v-bind:value="null">Seleccione</option-->
															<option v-for="option in info.partes" v-bind:value="option.id">
																{{ option.nombre }}
															</option>
														</select>													
														<span class="help-block" v-show="errors.has('requerimientos.parte')">{{ errors.first('requerimientos.parte') }}</span>
													</div>												
												</div>
												<div class="row">
													<div class="form-group col-sm-6">
														<label for="colega_id">Colega</label>
														<v-select
															v-model="selectedItem.colega"
															:clearSearchOnSelect="true"
															:debounce="5000"
															:on-search="getColegas"
															:options="info.colegas"
															:on-change="onChangeColega"
															placeholder="Ingresa el nombre del colega"
															label="nombre"
															:disabled="!amModal.canSave"
														>
														</v-select>							
													</div>	
													<div class="form-group col-sm-6">
														<label for="recomendado">Recomendado</label>
														<input type="text" name="recomendado" v-model="selectedItem.recomendado" class="form-control">
													</div>
												</div>
												
											</div>
											<!-- /.box-body -->
									</div>
						</div>
						<div class="col-xs-12" v-if="esPension()">
							<div class="box box-default">
											
											<div class="box-header with-border">

												<h3 class="box-title">Causante (Pensión)</h3>
											</div>
											<!-- /.box-header -->
											<div class="box-body">
											<div class="row">
											<div class="form-group col-sm-9" :class="{'has-error': errors.has('requerimientos.nombre_causante')}">
												<label for="nombre_causante">Nombre y Apellido</label>
												<input type="text" name="nombre_causante" v-model="selectedItem.nombre_causante" class="form-control" v-validate="'required'" data-vv-validate-on="none">
												<span class="help-block" v-show="errors.has('requerimientos.nombre_causante')">{{ errors.first('requerimientos.nombre_causante') }}</span>
											</div>
											<div class="clearfix"></div>
											<div class="form-group col-sm-3" :class="{'has-error': errors.has('requerimientos.tipo_doc_id_causante')}">
												<label for="tipo_doc_id_causante">Tipo Doc.</label>
												<select v-model="selectedItem.tipo_doc_id_causante" class="form-control" name="tipo_doc_id_causante" v-validate="'required'" data-vv-validate-on="none">
													<option v-for="option in info.tipo_doc" v-bind:value="option.id">
														{{ option.nombre }}
													</option>
												</select>
												<span class="help-block" v-show="errors.has('requerimientos.tipo_doc_id_causante')">{{ errors.first('requerimientos.tipo_doc_id_causante') }}</span>
											</div>
											<div class="form-group col-sm-3" :class="{'has-error': errors.has('requerimientos.nro_doc_causante')}">
												<label for="nro_doc_causante">Nro. Doc.</label>
												<input type="text" name="nro_doc_causante" v-model="selectedItem.nro_doc_causante" class="form-control" v-validate="'required'" data-vv-validate-on="none">
												<span class="help-block" v-show="errors.has('requerimientos.nro_doc_causante')">{{ errors.first('requerimientos.nro_doc_causante') }}</span>
											</div>
											<div class="clearfix"></div>
											<div class="form-group col-sm-9">
												<label for="domicilio_causante">Domicilio</label>
												<textarea name="domicilio_causante" v-model="selectedItem.domicilio_causante" class="form-control"></textarea>
												<!--span class="help-block" v-show="errors.has('requerimientos.domicilio_causante')">{{ errors.first('requerimientos.domicilio_causante') }}</span-->
											</div>									
											<div class="clearfix"></div>
											<div class="form-group col-sm-6">
												<label for="fecha_fallecimiento_causante">Fecha Fallecimiento</label><br>
												<datepicker name="fecha_fallecimiento_causante" v-model="selectedItem.fecha_fallecimiento_causante" :format="'dd/MM/yyyy'" :clear-button="true" placeholder="DD/MM/AAAA"></datepicker>
											</div>
											<div class="clearfix"></div>
											<div class="form-group col-sm-3" :class="{'has-error': errors.has('requerimientos.estado_civil')}">
												<label for="estado_civil">Estado Civil</label>
												<select v-model="selectedItem.estado_civil" class="form-control" name="estado_civil" v-validate="'required'" data-vv-validate-on="none">
													<option v-for="option in info.estado_civil" v-bind:value="option.value">
														{{ option.name }}
													</option>
												</select>
												<span class="help-block" v-show="errors.has('requerimientos.estado_civil')">{{ errors.first('requerimientos.estado_civil') }}</span>
											</div>	
											<div v-if="selectedItem.estado_civil == 'CA'">
												<div class="form-group col-sm-3" :class="{'has-error': errors.has('requerimientos.fecha_mat_causante')}">
													<label for="fecha_mat_causante">Fecha Matrimonio</label><br>
													<datepicker name="fecha_mat_causante" v-model="selectedItem.fecha_mat_causante" :format="'dd/MM/yyyy'" :clear-button="true" placeholder="DD/MM/AAAA"></datepicker>
													<span class="help-block" v-show="errors.has('requerimientos.fecha_mat_causante')">{{ errors.first('requerimientos.fecha_mat_causante') }}</span>
												</div>											
											</div>
											<div v-if="selectedItem.estado_civil == 'CO'">
												<div class="form-group col-sm-3" :class="{'has-error': errors.has('requerimientos.fecha_conv_causante')}">
													<label for="fecha_conv_causante">Fecha Convivencia</label>
													<input type="text" name="fecha_conv_causante" v-model="selectedItem.fecha_conv_causante" class="form-control" v-mask="['##/####']"  placeholder="MM/AAAA" v-validate="'required|date_format:MM/yyyy'" data-vv-validate-on="none">
													<span class="help-block" v-show="errors.has('requerimientos.fecha_conv_causante')">{{ errors.first('requerimientos.fecha_conv_causante') }}</span>
												</div>	
												<div class="form-group col-sm-3" :class="{'has-error': errors.has('requerimientos.hijos')}">
													<label for="hijos">Hijos</label>
													<select v-model="selectedItem.hijos" class="form-control" name="hijos" v-validate="'required'" data-vv-validate-on="none">
														<option v-for="option in info.hijos" v-bind:value="option.value">
															{{ option.name }}
														</option>
													</select>
													<span class="help-block" v-show="errors.has('requerimientos.hijos')">{{ errors.first('requerimientos.hijos') }}</span>
												</div>																													
											</div>																	
										</div>
											</div>
											<!-- /.box-body -->
									</div>
						</div>
						<div class="clearfix"></div>
						<div class="col-xs-12" v-if="selectedItem.req_padre">
							<div class="box box-default">
											
											<div class="box-header with-border">

												<h3 class="box-title">Causante (Pensión)</h3>
											</div>
											<!-- /.box-header -->
											<div class="box-body">
											<div class="row">
											<div class="form-group col-sm-9">
												<label>Nombre y Apellido</label><br>
												<input type="text" v-model="selectedItem.req_padre.nombre_causante" class="form-control" :disabled="true">
											</div>
											<div class="clearfix"></div>
											<div class="form-group col-sm-3">
												<label>Tipo Doc.</label>
												<select v-model="selectedItem.req_padre.tipo_doc_id_causante" class="form-control" name="tipo_doc_id_causante" :disabled="true">
													<option v-for="option in info.tipo_doc" v-bind:value="option.id">
														{{ option.nombre }}
													</option>
												</select>
											</div>
											<div class="form-group col-sm-3">
												<label>Nro. Doc.</label>
												<input type="text" v-model="selectedItem.req_padre.nro_doc_causante" class="form-control" :disabled="true">
											</div>
											<div class="clearfix"></div>
											<div class="form-group col-sm-9">
												<label>Domicilio</label>
												<textarea name="domicilio_causante" v-model="selectedItem.req_padre.domicilio_causante" class="form-control" :disabled="true"></textarea>
											</div>									
											<div class="clearfix"></div>
											<div class="form-group col-sm-6">
												<label>Fecha Fallecimiento</label><br>
												<input type="text" class="form-control" v-model="selectedItem.req_padre.fecha_fallecimiento_causante" :disabled="true">
											</div>
											<div class="clearfix"></div>
											<div class="form-group col-sm-3">
												<label>Estado Civil</label>
												<select v-model="selectedItem.req_padre.estado_civil" class="form-control" :disabled="true">
													<option v-for="option in info.estado_civil" v-bind:value="option.value">
														{{ option.name }}
													</option>
												</select>
											</div>	
											<div v-if="selectedItem.req_padre.estado_civil == 'CA'">
												<div class="form-group col-sm-3">
													<label>Fecha Matrimonio</label><br>
													<input type="text" class="form-control" v-model="selectedItem.req_padre.fecha_mat_causante" :disabled="true">
													
												</div>											
											</div>
											<div v-if="selectedItem.req_padre.estado_civil == 'CO'">
												<div class="form-group col-sm-3">
													<label>Fecha Convivencia</label>
													<input type="text" v-model="selectedItem.req_padre.fecha_conv_causante" class="form-control" v-mask="['##/####']" :disabled="true">
												</div>	
												<div class="form-group col-sm-3">
													<label>Hijos</label>
													<select v-model="selectedItem.req_padre.hijos" class="form-control" :disabled="true">
														<option v-for="option in info.hijos" v-bind:value="option.value">
															{{ option.name }}
														</option>
													</select>
												</div>																													
											</div>																	
										</div>
											</div>
											<!-- /.box-body -->
									</div>
						</div>
						<div class="clearfix"></div>
						<div class="col-sm-6">
							<div class="box" :class="{'box-danger has-error':errors.has('requerimientos.responsables'),'box-default':!errors.has('requerimientos.responsables')}">
											<div class="box-header with-border">
												<h3 class="box-title">Responsables</h3>
											</div>
											<!-- /.box-header -->
											<div class="box-body">
									<div class="row">
										<div class="col-xs-10">
											<v-select
												:value="info.responsables.selected"
												:debounce="5000"
												:options="info.responsables.data"
												:on-change="onChangeResponsable"
												placeholder="Ingresa el nombre del abogado responsable"
												label="name"
												:disabled="!amModal.canSave"
											>
											</v-select>				
										</div>
										<div class="col-xs-2">
											<button-type type="add" @click="addResponsable()" v-show="this.info.responsables.selected"/>
										</div>
										<div class="clearfix"></div>
										<div class="col-xs-12">
														<draggable :list="selectedItem.responsables" element="ul" class="list-group" style="margin-top:20px;">
																<li class="list-group-item" v-for="(item, index) in selectedItem.responsables">
													<button-type v-can="[actionPerm+':U',actionPerm+':C']" type="remove-list" @click="removeResponsable(index)"/>						                	
																	<i class="fa fa-arrows handle pull-left"></i>
																	<span class="badge pull-left">{{ (index == 0 ? 'Ppal.' : 'Sec.') }}</span>
																	{{ item.user.name }}
																</li>
														 </draggable>				
										</div>
									</div>					  	
											</div>
											<span class="help-block" v-show="errors.has('requerimientos.responsables')">{{ errors.first('requerimientos.responsables') }}</span>
									</div>					
						</div>
						<!--div class="clearfix"></div-->
						<div class="col-sm-6" v-if="esPendienteTurno(selectedItem.estado_req_id)">
							<div class="box box-default">
											<div class="box-header with-border">
												<h3 class="box-title">Turno</h3>
											</div>
											<div class="box-body">
									<div class="row">
										<div class="form-group col-sm-6" :class="{'has-error': errors.has('requerimientos.fecha_turno')}">
											<label for="fecha_turno">Fecha</label><br>
											<datepicker name="fecha_turno" v-model="selectedItem.fecha_turno" :format="'dd/MM/yyyy'" :clear-button="true"  placeholder="DD/MM/AAAA"></datepicker>
											<span class="help-block" v-show="errors.has('requerimientos.fecha_turno')">{{ errors.first('requerimientos.fecha_turno') }}</span>
										</div>
										<div class="form-group col-sm-6" :class="{'has-error': errors.has('requerimientos.hora_turno')}">
											<label for="hora_turno">Hora</label>
											<input type="text" name="hora_turno" v-model="selectedItem.hora_turno" class="form-control" v-mask="['##:##:##']" placeholder="HH:MM:SS" v-validate="'required|date_format:HH:mm:ss'" data-vv-validate-on="none" >
											<span class="help-block" v-show="errors.has('requerimientos.hora_turno')">{{ errors.first('requerimientos.hora_turno') }}</span>
										</div>																					

										<div class="clearfix"></div>
										<div class="form-group col-sm-12" :class="{'has-error': errors.has('requerimientos.rep_origen_id')}">
											<label for="rep_origen_id">Repartición Origen</label>
											<v-select
												v-model="selectedItem.rep_origen"
												:options="info.rep_origen"
												:on-change="onChangeRepOrigen"
												placeholder="Ingresa la rep. de origen"
												label="nombre"
											>
											</v-select>
											<span class="help-block" v-show="errors.has('requerimientos.rep_origen_id')">{{ errors.first('requerimientos.rep_origen_id') }}</span>
										</div>
									</div>					  	
											</div>
									</div>					
						</div>				
						<div class="clearfix"></div>
				</div>
				<div class="row" v-show="amModal.section === 'D'">
						<div class="col-xs-12">
							<template v-if="selectedItem.area_id === 5">
								<div class="box box-default" v-for="item in amModal.documentacion.area_id_5">
									<div class="box-header with-border">
										<h3 class="box-title">{{ tituloDoc(item) }}</h3>
									</div>
									<div class="box-body">
										<vue-editor :id="item.type" v-model="item.value"></vue-editor>
										<!--textarea class="form-control" v-model="item.value"></textarea-->
									</div>
								</div>
							</template>

							<div class="box box-default">
								<div class="box-header with-border">
									<h3 class="box-title">Documentación</h3>
								</div>
								<div class="box-body">
									<div v-for="(item,index) in amModal.documentacion.docs" class="box box-solid">
										<div class="box-header with-border">
											<h3 class="box-title">{{ tituloDoc(item) }}</h3>
											<button-type v-can="[actionPerm+':U',actionPerm+':C']" type="remove-list" @click="removeDoc(index)"/>
										</div>
										<div class="box-body">
											<dl>
												<template v-for="doc in item.docs">
													<dt>{{ doc.nombre }}</dt>
													<dd>{{ doc.descripcion }}</dd>
												</template>
											</dl>
										</div>
									</div>
								</div>
								<div class="box-body text-right">
									<a class="btn btn-app" @click="addDocTT()">
										<i class="fa fa-plus"></i> Tipo Tramite
									</a>			            		
									<a v-if="selectedItem.area_id === 1" class="btn btn-app" @click="addDocTC()">
										<i class="fa fa-plus"></i> Tipo Cliente
									</a>

									<a class="btn btn-app" v-show="selectedItem.documentacion && selectedItem.documentacion.length > 0" :href="urlPrint()" target="_blank">
										<i class="fa fa-print"></i> Imprimir
									</a>
									<button-type type="enviar-mail-app" :promise="sendDoc"  v-show="selectedItem.documentacion && selectedItem.documentacion.length > 0"/>	
												              	
								</div>
							</div>
						</div>					
				</div>
				</fieldset>
				<div class="row">
					<div class="clearfix"></div>
						<div slot="modal-footer" class="modal-footer">
							<button-type type="close" @click="closeAmModal()" />
							<button-type type="save" :submit="true" :disabled="amModal.saving" v-if="amModal.canSave" />
							<button-type type="inic-tramite" @click="iniciarTramite()" v-show="btnInicTramite" v-if="hasAnyPerm('tramites:R')"/>
							<button-type type="ver-tramite" @click="verTramite()" v-show="btnVerTramite"  v-if="hasAnyPerm('tramites:R') && selectedItem.tramite_id" :caption="selectedItem.tramite_id"/>
					</div>								
				</div>
			</template>
			<pulse-loader :loading="true" v-else></pulse-loader>

		</form>

		<modal v-model="docModal.show"  class="themis-modal" effect="fade" :backdrop="false" :large="true">
			<div slot="modal-header" class="modal-header">
				<h4 class="modal-title">
					{{ docModal.title }} <br class="visible-xs"><span class="hidden-xs"> | </span>{{ cliente.nombre_completo }} - {{ cliente.cuit }}
				</h4>
			</div>
			<div slot="modal-body" class="modal-body" @keyup.enter="addDoc()" @keyup.esc="closeDocModal()">
				<div class="row" v-if="docModal.type === 'TC'">
				<div class="form-group col-xs-12">
					<label for="tipo_aporte">Tipo aporte</label>
					<select v-model="docModal.data.tipo_aporte" class="form-control" name="tipo_aporte" @change="onChangeTipoAporte()">
						<option v-for="option in info.tipos_aporte" v-bind:value="option">
							{{ option.nombre }}
						</option>
					</select>
				</div>				

				<div class="form-group col-xs-12" v-if="docModal.data.tipo_aporte && docModal.data.tipo_aporte.id == 37">
					<label for="empresa">Empresa</label>
					<input type="text" name="empresa" v-model="docModal.data.empresa" class="form-control">
				</div>
			</div>
				<div class="row" v-if="docModal.showItems">
				<div class="col-xs-12">
					<div class="table-responsive">
						<table class="table table-striped">
							<tbody>
								<tr>
									<th style="width:30px;">#</th>
									<th style="width:200px;">Tipo Documento</th>
									<th>Descripción</th>
								</tr>
								<tr v-show="docModal.loading">
												<td colspan="3">
													<pulse-loader :loading="docModal.loading"></pulse-loader>
												</td>								
								</tr>
								<tr v-show="!docModal.loading && docModal.docs.length === 0">
												<td colspan="3">No hay documentos asociados.</td>								
								</tr>							
								<tr v-show="!docModal.loading" v-for="item in docModal.docs">
									<td><input type="checkbox" :value="item.doc" v-model="docModal.data.docs"></td>
									<td>{{ item.doc.nombre }}</td>
									<td>{{ item.doc.descripcion }}</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			</div>
			<div slot="modal-footer" class="modal-footer">
				<button-type type="close" @click="closeDocModal()"/>
				<button-type type="save" v-show="docModal.docs.length > 0" @click="addDoc()"/>
			</div>
		</modal>

		<modal v-model="traModal.show" class="themis-modal" effect="fade" :backdrop="false" :large="true">
			<div slot="modal-header" class="modal-header">
				<h4 class="modal-title">{{ traModal.title }} <br class="visible-xs"><span class="hidden-xs"> | </span>{{ cliente.nombre_completo }} - {{ cliente.cuit }}</h4>
			</div>
			<template v-if="traModal.loaded">			
				<c-u-tramite v-if="traModal.show && traModal.loaded" :baseUri="baseUri" :actionPerm="'tramites'" :info="infoTramites" :selectedItem="traModal.data" @clientes:tramites-close="closeTramiteModal" @clientes:tramites-saved="onTramiteGuardado" :isModal="true" :canSave="traModal.canSave" ></c-u-tramite>
			</template>
			<pulse-loader :loading="true" v-else></pulse-loader>
			<div slot="modal-footer" class="modal-footer"></div>
		</modal>

		<form id="frmImpDoc" method="post"></form>
	</div>
</template>
<script>
import moment from 'moment'
import { mapGetters, mapState } from 'vuex'
import Vue from 'vue'
import { modal,datepicker,buttonGroup,radio } from 'vue-strap'
import config from '../../../../config'
import Api from '../../../../api'
import vSelect from "vue-select"
import draggable from 'vuedraggable'
import CUTramite from './CUTramite'
import store from '../../../../store/index'
import { VueEditor } from 'vue2-editor'
//import PulseLoader from 'vue-spinner/src/PulseLoader.vue'

export default {
	name: 'SolapaRequerimientos',
	components: {
		modal,
		VueEditor,
		vSelect,
		draggable,
		datepicker,
		buttonGroup,
		radio,
		CUTramite
		//PulseLoader
	},
	props: {
		baseUri: {
			type: String,
			required: true
		},
		clienteId: {
			type: Number,
			required: true
		},
		cliente: {
			type: Object,
			required: true
		},
		actionPerm: {
			type: String,
			required: true
		},
		cuit: {
			type: String,
			default () {
				return "00000000000"
			}
		},
	  	active: {
	  		type: Boolean,
	  		default: function() {
	  			return false;
	  		}
	  	}		
	},
	data () {
		return {
			title: 'Requerimientos',
			firstLoad: true,
			btnInicTramite: false,
			btnVerTramite: false,
			list: {
				loading: true,
				data: []
			},
			info: {
				colegas: [],
				responsables: {
					selected: null,
					data: []
				},
				tipos_aporte: [],
				partes: []
			},
			infoTramites: {
			},		
			selectedItem: null,
			selectedIndex: -1,
			amModal: {
				title: 'Nuevo requerimiento',
				canSave: true,
				section: 'G',
				submited: false,
				show: false,
				loaded: false,
				errors: '',
				saving: false,
				documentacion: {
					area_id_1: [],
					area_id_2: [],
					area_id_3: [],
					area_id_4: [],
					area_id_5: [],
					docs: []
				}
			},
			docModal: {
				show: false,
				title: '',
				type: 'TT',
				loading: false,
				showItems: false,
				docs: [],
				data: {
					type: 'TT',
					docs: []
				}
			},
			traModal: {
				title: 'Nuevo tramite',
				show: false,
				loaded: false,
				canSave: false,
				data: {}
			},
			messages: '', 
			apiUrl: '',
			uri: this.baseUri.concat(this.clienteId).concat('/requerimientos/')
		}
	},  
	mounted () {
		this.apiUrl = config.serverURI + this.uri;
		//this.getList();	
	}, 
	computed: {
		...mapGetters([
			'hasAnyPerm'
		]),
	    ...mapState([
	      'authUser'
	    ])
	},   
	methods: {
		onAction (action, data, index) {
			this.$store.dispatch('hideSuccessNotification')
			switch(action) {
				case 'edit':
					this.selectedIndex = index;
					this.reset(_.clone(data, true));
					this.amModal.canSave = true;
					break;
			case 'view':
					this.selectedIndex = index;
					this.reset(_.clone(data, true));
					this.amModal.canSave = false;
					break;
				case 'remove':
					this.remove(data,index);
					break;      		
			}
		},  
		getList () {
			if (this.clienteId && this.hasAnyPerm(this.actionPerm+':R')) {
				this.list.loading = true;
				Api.get(this.uri).then((result) => {
					this.list.data.length = 0;
					this.list.data = result.data;
					this.list.loading = false;
					this.firstLoad = false;
				})
			}
		},
		create (area_id) {
			this.reset({area_id: area_id});
		},  
		reset (item) {
			this.amModal.section = 'G';
			this.amModal.show = true;
			Api.combos('solapa-requerimientos/' + item.area_id).then(resp => {

					this.info = resp.data;

					this.selectedItem = _.assign({
						id: 0,
						cliente_id: this.clienteId,
						tipo_tramite: null,
						responsables: [],
						fecha_mat_causante: '',
						fecha_fallecimiento_causante: '',
						fecha_turno: '',
						estado: null,
						documentacion: [],
						tramite: null,
						parte: null,
						nombre_parte: null,
						autos: null,
						}, item)

					this.clearErrors()   	

					let _area_id_5_SP = {
						type: 'A5SP',
						//title: 'I.-SITUACIÓN PLANTEADA:',
						value: ''
					};
					let _area_id_5_GH = {
						type: 'A5GH',
						//title: 'II.-PRESUPUESTO DE GASTOS Y HONORARIOS:',
						value: ''
					};

					for (let j = 0; j < this.selectedItem.documentacion.length; j++) {
						let _item = this.selectedItem.documentacion[j];
						switch(_item.type) {
							case 'A5SP':
								_area_id_5_SP = _.clone(_item);
								break;
							case 'A5GH':
								_area_id_5_GH = _.clone(_item);
								break;
							default:
								this.amModal.documentacion.docs.push(_item);
								break;								
						}
					}

					switch(this.selectedItem.area_id) {
						case 5:
							this.amModal.documentacion.area_id_5.push(_area_id_5_SP,_area_id_5_GH);
							break;
					}
					//console.debug(this.amModal.documentacion.area_id_5);
					this.btnInicTramite = this.puedeInicTramite();
					this.btnVerTramite = this.selectedItem.tramite_id !== null && this.selectedItem.tramite_id > 0;
					this.amModal.loaded = true;
								   
			})
		},
		clearErrors() {
			this.amModal.errors = ''
			this.$validator.reset()    	
		},    
		closeAmModal () {
			this.getList();
			this.amModal = _.assign(this.amModal,{
				show: false,
				loaded: false,
				submited: false,
				documentacion: {
					area_id_1: [],
					area_id_2: [],
					area_id_3: [],
					area_id_4: [],
					area_id_5: [],
					docs: []
				}
			})
			this.selectedIndex = -1;
			this.selectedItem = {};
			this.clearErrors()
		},
		save() {
			if (this.amModal.saving) {
				return false;
			}
			this.errors.clear('requerimientos');

			if (!this.selectedItem.tipo_tramite_id) {
				this.addError('tipo_tramite_id', 'Campo requerido','server','requerimientos');
			}
			if (this.esPension(this.selectedItem.tipo_tramite_id) && this.selectedItem.estado_civil !== '') {
				if (this.selectedItem.estado_civil === 'CA' && this.selectedItem.fecha_mat_causante === '') {
					this.addError('fecha_mat_causante', 'Campo requerido','server','requerimientos');	
				}/* else {
					if (this.selectedItem.estado_civil === 'CO' && this.selectedItem.fecha_conv_causante === '') {
						this.addError('fecha_conv_causante', 'Campo requerido','server','requerimientos');	
					}    			
				}*/
			}

			if (!this.selectedItem.estado_req_id) {
				this.addError('estado_req_id', 'Campo requerido','server','requerimientos');
			}
			else
			{
				if (this.esPendienteTurno(this.selectedItem.estado_req_id)) {
					if (this.selectedItem.fecha_turno == '') 
						this.addError('fecha_turno', 'Campo requerido','server','requerimientos');	

					if (!this.selectedItem.rep_origen_id)
						this.addError('rep_origen_id', 'Campo requerido','server','requerimientos');	
				}    		
			}

			if (this.selectedItem.responsables.length < 1) {
				this.addError('responsables', 'Campo requerido','server','requerimientos');	
			}

			this.$validator.validateAll('requerimientos').then((result) => {
				if (result && this.errors.items.length < 1) {
					this.amModal.saving = true;
					switch(this.selectedItem.area_id) {
						case 1: 
						case 2:
						case 3:
						case 4: 
							this.selectedItem.documentacion =  _.union(this.amModal.documentacion.docs,[]);
							break;
						case 5:
							this.selectedItem.documentacion =  _.union(this.amModal.documentacion.area_id_5,this.amModal.documentacion.docs);	 
							break;
					}
					
					Api.store(this.uri,this.selectedItem)
					.then((result) => {
							this.$store.dispatch('showSuccessNotification',result.data.message)
							this.amModal.saving = false;
							
							if (this.selectedItem.id < 1 || this.amModal.section === 'G') {
								this.closeAmModal();	
							}
							//
					}, (resp) => {
						this.amModal.saving = false;
						this.message = resp.message
						this.$store.dispatch('showErrorNotification',this.message)

					});	        	
				}
			}); 
		},
		remove (item,index) {
			this.$dialog.confirm('¿Deséa continuar con la eliminación del registro?',{loader: true})
			.then((dialog) => {
						dialog.loading(true)
						Api.delete(this.uri,item)
							.then((result) => {
								dialog.close()
								this.$store.dispatch('showSuccessNotification',result.data.message)
							this.getList()
							},(resp) => {
								this.$store.dispatch('showErrorNotification',resp.message)
								dialog.close()
							})  			
			})    	
		},    
		back() {
			this.$emit('clientes:back','requerimientos')
		},
		responsables(list) {
			let _resp = [];
			for(let i = 0; i < list.length; i++) {
				_resp.push(list[i].user.name);
			}
			return _resp.join('<br>');
		},
		getColegas(search, loading) {
			loading(true)
			Api.combos('colegas?search=' + search).then(resp => {
				 this.info.colegas = resp.data
				 loading(false)
			})
		},
		onChangeColega(item) {
			this.selectedItem.colega = item
			this.selectedItem.colega_id = (item ? item.id : null)	
		},  

		getTipoTramites(search, loading) {
			loading(true)
			Api.combos('tipo-tramites?search=' + search).then(resp => {
				 this.info.tipo_tramites = resp.data
				 loading(false)
			})
		},
		onChangeTipoTramite(item) {
			this.selectedItem.tipo_tramite = item
			this.selectedItem.tipo_tramite_id = (item ? item.id : null)
		},
		getResponsables(search, loading) {
			loading(true)
			Api.combos('responsables/'+this.selectedItem.area_id+'/?search=' + search).then(resp => {
				 this.info.responsables.data = resp.data
				 loading(false)
			})
		},
		addResponsable() {
			if (this.info.responsables.selected) {
				this.selectedItem.responsables.push({
					fecha_asignacion: moment().format('DD/MM/YYYY'),
					requerimiento_id: this.selectedItem.id,
					user: {
						id: this.info.responsables.selected.id,
						name:this.info.responsables.selected.name
					},
					user_id: this.info.responsables.selected.id
				})
				this.info.responsables.selected = null
				//this.info.responsables.data = []
			}
		},
		removeResponsable(index) {
			this.$dialog.confirm('¿Deséa continuar con la eliminación del registro?',{loader: true})
				.then((dialog) => {
							dialog.loading(true)
							this.selectedItem.responsables.splice(index,1)
							dialog.close()
				})  		
		},
		onChangeResponsable(item) {
			this.info.responsables.selected = item
		},
		onChangeEstadoReq(item) {
			this.selectedItem.estado = item
			this.selectedItem.estado_req_id = (item ? item.id : null)	
		},	
		onChangeRepOrigen(item) {
			this.selectedItem.rep_origen = item
			this.selectedItem.rep_origen_id = (item ? item.id : null)	
		},		
		esPension() {
			return this.selectedItem.tipo_tramite && this.selectedItem.tipo_tramite.tratamiento === 'PENSION';
		},
		esPendienteTurno(id) {
			return (id == 524 || id == 53)
		},
		puedeInicTramite () {
			
			return this.selectedItem.id > 0 && !this.selectedItem.tramite_id && this.selectedItem.tipo_tramite.inicia_tramite && (!this.selectedItem.req_nec || (this.selectedItem.req_nec && this.selectedItem.req_nec.tramite_id > 0 ));
		},
		iniciarTramite () {
			this.openTraModal(false);
		},
		verTramite () {
			if (!this.selectedItem.tramite) {
				Api.get('tramites/' + this.selectedItem.tramite_id).then(resp => {
					this.selectedItem.tramite = resp.data;
					this.openTraModal(_.clone(resp.data));
				});
			} else {
				this.openTraModal(_.clone(this.selectedItem.tramite));
			};
		},
		openTraModal (tramite) {
			this.traModal.show = true;
			Api.combos('solapa-tramites/' + this.selectedItem.area_id).then(resp => {
					this.infoTramites = _.assign(resp.data,{
					})

					let _cuit = this.cuit ? this.cuit.replace(new RegExp('-', 'g'), '') : '00000000000';
					if (!tramite) {
						
						let _exp = "";

						if (this.selectedItem.area_id == 1) {
							_exp = "024".concat(_cuit).concat("000").concat("000000");
						} else {
							_exp = null;
						}

						this.traModal.title = 'Iniciar trámite';
						this.traModal.data = {
							id: 0,
							area_id: this.selectedItem.area_id,
							requerimiento_id: this.selectedItem.id,
							archivar: false,
							cliente_id: this.clienteId,
							cliente: this.selectedItem.cliente,

							cuit: _cuit,
							expediente: _exp,
							fecha_inicio: moment().format('DD/MM/YYYY'),
							fecha_beneficio: '',
							fecha_vto: '',
							beneficios: [],
							estado_tramite_id: null,
							estado: null,
							requerimiento: {
								tipo_tramite_id: this.selectedItem.tipo_tramite_id,
								tipo_tramite: this.selectedItem.tipo_tramite,
								responsables: this.selectedItem.responsables,
								autos: this.selectedItem.autos,
								parte: this.selectedItem.parte,
								nombre_parte: this.selectedItem.nombre_parte
							},
							exp_judicial: null,
							resolucion: false,
							tipo_resolucion: 2
						};
					} else {
						this.traModal.title = 'Trámite N° ' + this.selectedItem.tramite_id;
						//this.traModal.canSave = this.amModal.canSave;
						this.traModal.data = tramite;
						this.traModal.data.area_id = this.selectedItem.area_id;
						this.traModal.data.cliente_id = this.selectedItem.cliente_id;
						this.traModal.data.cuit = _cuit;
						this.traModal.data.requerimiento.responsables = this.selectedItem.responsables;

					}
				this.traModal.loaded = true;
				this.traModal.canSave = this.amModal.canSave && this.hasAnyPerm(['tramites:U','tramites:C'])
			})
		},
		addDocTC () {
			this.docModal.type = 'TC';
			this.docModal.showItems = false;
			this.docModal.title = 'Documentación: Tipo de cliente';
			this.docModal.loading = false;
			this.docModal.show = true;
		},
		addDocTT () {
			this.docModal.type = 'TT';
			this.docModal.showItems = true;
			this.docModal.title = 'Documentación: Tipo de trámite.';
			this.docModal.loading = true;

			Api.combos('doc-requerida-tt/' + this.selectedItem.tipo_tramite_id).then(resp => {
				 this.docModal.docs.length = 0;
				 this.docModal.docs = resp.data
				 this.docModal.loading = false;
			},err => {
				this.docModal.loading = false;
				this.docModal.docs.length = 0;
			})
					
			this.docModal.show = true;
		},
		closeDocModal () {
			this.docModal.showItems = false;
			this.docModal.show = false;
			this.docModal.loading = false;
			this.docModal.docs.length = 0;
			this.docModal.docs = [];
			this.docModal.data = {
				docs: []
			}
			this.docModal.data.docs.length = 0;
		},
		onChangeTipoAporte () {
			this.docModal.showItems = true;
			this.docModal.loading = true;
			Api.combos('doc-requerida-ta/' + this.docModal.data.tipo_aporte.id).then(resp => {
				 this.docModal.docs.length = 0;
				 this.docModal.docs = resp.data
				 this.docModal.loading = false;
			},err => {
				this.docModal.loading = false;
				this.docModal.docs.length = 0;
			})		
		},
		addDoc () {
			if (this.docModal.type === 'TT') {
				if (this.amModal.documentacion.docs.length > 0 && this.amModal.documentacion.docs[0].type === 'TT') {
					this.amModal.documentacion.docs.shift();
				}
				this.amModal.documentacion.docs.unshift({
					type: this.docModal.type,
					docs: _.clone(this.docModal.data.docs)
				});
			} else {
				this.amModal.documentacion.docs.push({
					type: this.docModal.type,
					docs: _.clone(this.docModal.data.docs),
					empresa: this.docModal.data.empresa,
					tipo_aporte: this.docModal.data.tipo_aporte.nombre
				});
			}
			
			this.closeDocModal()
		},
		removeDoc (index) {
			this.$dialog.confirm('¿Deséa continuar con la eliminación del registro?',{loader: true})
				.then((dialog) => {
							dialog.loading(true)
							this.selectedItem.documentacion.splice(index,1)
							dialog.close()
				})  		
		},
		urlPrint () {
			return Laravel.webDomain.concat('/documentacion-req/').concat(this.selectedItem.id);
		},
		sendDoc () {
			return Api.get(this.uri.concat(this.selectedItem.id).concat('/enviar-doc-email')).then(r => {
				console.debug(r);
			});
			//document.location = Laravel.webDomain.concat('/documentacion-req/').concat(this.selectedItem.id);
			/*let _formData = new FormData();
			_formData.append('docs')*/
		},	
		tituloDoc (doc) {
			let titulo = '';
			switch(doc.type) {
				case 'A5SP':
					titulo = 'I.-SITUACIÓN PLANTEADA:';
					break;
				case 'A5GH':
					titulo = 'II.-PRESUPUESTO DE GASTOS Y HONORARIOS:';
					break;	
				case 'TT':
					titulo = 'Tipo de tramite'
					break;
				default:
					if (doc.empresa) {
						titulo = doc.empresa;
					} else {
						titulo = doc.tipo_aporte;	
					}
					break;
			}
			return titulo
		},
		closeTramiteModal () {
			this.traModal.loaded = false;
			this.traModal.show = false;
		},
		onTramiteGuardado(tramite) {
			this.selectedItem.tramite = tramite;
			if (!this.selectedItem.tramite_id) {
				this.selectedItem.tramite_id = tramite.id;
				this.selectedItem.estado = tramite.requerimiento.estado;
				this.selectedItem.estado_req_id = tramite.requerimiento.estado.id;
				this.$store.dispatch('showSuccessNotification','Tramite asignado al requerimiento');
			} else {
				this.selectedItem.estado = tramite.requerimiento.estado;
				this.selectedItem.estado_req_id = tramite.requerimiento.estado.id;
			}
			this.btnInicTramite = false;
			this.btnVerTramite = true;
			this.closeTramiteModal();	
			this.closeAmModal();
		},
		mostrarAutosParte() {
			return this.selectedItem && (this.selectedItem.area_id === 2 || this.selectedItem.area_id === 3 || this.selectedItem.area_id === 4);
		}
	},
	watch: {
	    active(newVal) {
	      if (newVal && this.firstLoad) {
	      	this.getList();
	      }
	    }
    }	
};		
</script>
<style scoped>
	#frm .btn-remove-list{
		float: right;
	}
	.progress-description.del{
		text-align: center;
	}
	.progress-description.del a{
		color: #ffffff!important;
	}
	.progress-description.del a i{
		display: block;
	}
</style>