<template>
	<div ref="topWindows">
		<div class="row">
			<div class="form-group col-sm-2" v-if="selectedItem.id > 0">
				<label>ID</label>
				<input type="text" v-model="selectedItem.id" :disabled="true" class="form-control">
			</div>
			<div class="form-group col-sm-6" :class="{'has-error': errors.has('nombre')}">
				<label for="nombre" v-if="selectedItem.personeria === 'H'">Nombre Completo</label>
				<label for="nombre" v-if="selectedItem.personeria === 'J'">Razón Social</label>
				<input type="text" name="nombre" v-model="selectedItem.nombre_completo" class="form-control" v-validate="'required'" data-vv-validate-on="none">
				<span class="help-block" v-show="errors.has('nombre')">{{ errors.first('nombre') }}</span>
			</div>
			<div class="form-group col-sm-4" :class="{'has-error': errors.has('cuit')}">
				<label for="cuit">CUIL/CUIT</label>
				<input type="text" name="cuit" v-model="selectedItem.cuit" class="form-control" v-mask="['##-#######-#', '##-########-#']" v-validate="'required'" data-vv-validate-on="none">
				<span class="help-block" v-show="errors.has('cuit')">{{ errors.first('cuit') }}</span>
			</div>
		</div>
		<div class="row" v-if="selectedItem.personeria === 'H'">
			<div class="form-group col-sm-3" :class="{'has-error': errors.has('sexo')}">
				<label for="sexo">Sexo</label>
				<select v-model="selectedItem.sexo" class="form-control" name="sexo" v-validate="'required'" data-vv-validate-on="none">
				  <option v-for="option in info.sexo" v-bind:value="option.value">
				    {{ option.name }}
				  </option>
				</select>
				<span class="help-block" v-show="errors.has('sexo')">{{ errors.first('sexo') }}</span>
			</div>
			<div class="form-group col-sm-3" :class="{'has-error': errors.has('tipo_doc_id')}">
				<label for="tipo_doc_id">Tipo Doc.</label>
				<select v-model="selectedItem.tipo_doc_id" class="form-control" name="tipo_doc_id" v-validate="'required'" data-vv-validate-on="none">
				  <option v-for="option in info.tipo_doc" v-bind:value="option.id">
				    {{ option.nombre }}
				  </option>
				</select>
				<span class="help-block" v-show="errors.has('tipo_doc_id')">{{ errors.first('tipo_doc_id') }}</span>
			</div>
			<div class="form-group col-sm-3" :class="{'has-error': errors.has('nro_doc')}">
				<label for="nro_doc">Nro. Doc.</label>
				<input type="text" name="nro_doc" v-model="selectedItem.nro_doc" class="form-control" v-validate="'required'" data-vv-validate-on="none">
				<span class="help-block" v-show="errors.has('nro_doc')">{{ errors.first('nro_doc') }}</span>
			</div>
			<div class="form-group col-sm-3" :class="{'has-error': errors.has('fecha_nac')}">
				<label for="fecha_nac">Fecha Nac.</label>
				<datepicker name="fecha_nac" v-model="selectedItem.fecha_nac" :format="'dd/MM/yyyy'" :clear-button="true" v-validate="'required'" data-vv-validate-on="none" placeholder="DD/MM/YYYY"></datepicker>
				<span class="help-block" v-show="errors.has('fecha_nac')">{{ errors.first('fecha_nac') }}</span>
			</div>				
			<div class="clearfix"></div>
			<div class="form-group col-sm-3" :class="{'has-error': errors.has('nacionalidad')}">
				<label for="nacionalidad">Nacionalidad</label>
				<select v-model="selectedItem.nacionalidad" class="form-control" name="nacionalidad" v-validate="'required'" data-vv-validate-on="none">
				  <option v-for="option in info.nacionalidad" v-bind:value="option.value">
				    {{ option.name }}
				  </option>
				</select>
				<span class="help-block" v-show="errors.has('nacionalidad')">{{ errors.first('nacionalidad') }}</span>
			</div>
			<div class="form-group col-sm-3" :class="{'has-error': errors.has('fecha_ing_pais')}" v-if="selectedItem.nacionalidad !== 'A'">
				<label for="fecha_ing_pais">Fecha Ing.</label>
				<datepicker  name="fecha_ing_pais" v-model="selectedItem.fecha_ing_pais" :format="'dd/MM/yyyy'" :clear-button="true" v-validate="'required'" data-vv-validate-on="none" placeholder="Ingrese la fecha"></datepicker>
				<span class="help-block" v-show="errors.has('fecha_ing_pais')">{{ errors.first('fecha_ing_pais') }}</span>
			</div>				
			
			<div class="form-group col-sm-3" :class="{'has-error': errors.has('pais_id')}">
				<label for="pais_id">Pais</label>
				<select v-model="selectedItem.pais_id" class="form-control" name="pais_id" v-validate="'required'" data-vv-validate-on="none">
				  <option v-for="option in info.paises" v-bind:value="option.id">
				    {{ option.nombre }}
				  </option>
				</select>
				<span class="help-block" v-show="errors.has('pais_id')">{{ errors.first('pais_id') }}</span>
			</div>
			<div class="form-group col-sm-3" v-if="selectedItem.pais_id == 6" :class="{'has-error': errors.has('provincia_id')}">
				<label for="provincia_id">Provincia</label>
				<select v-model="selectedItem.provincia_id" class="form-control" name="provincia_id" v-validate="'required'" data-vv-validate-on="none">
				  <option v-for="option in info.provincias" v-bind:value="option.id">
				    {{ option.nombre }}
				  </option>
				</select>
				<span class="help-block" v-show="errors.has('provincia_id')">{{ errors.first('provincia_id') }}</span>
			</div>
			<div class="clearfix"></div>
			<div class="form-group col-sm-3" v-if="selectedItem.pais_id == 6" :class="{'has-error': errors.has('localidad')}">
				<label for="localidad">Localidad</label>
				<input type="text" name="localidad" v-model="selectedItem.localidad" class="form-control" v-validate="'required'" data-vv-validate-on="none">
				<span class="help-block" v-show="errors.has('localidad')">{{ errors.first('localidad') }}</span>
			</div>
			<div class="form-group col-sm-2" v-if="selectedItem.pais_id == 6" :class="{'has-error': errors.has('cp')}">
				<label for="cp">CP</label>
				<input type="text" name="cp" v-model="selectedItem.cp" class="form-control">
				<span class="help-block" v-show="errors.has('cp')">{{ errors.first('cp') }}</span>
			</div>
			<div class="form-group col-sm-12" v-if="selectedItem.pais_id != 6" :class="{'has-error': errors.has('clp_extranjero')}">
				<label for="clp_extranjero">Domicilio</label>
				<textarea name="clp_extranjero" v-model="selectedItem.clp_extranjero" class="form-control"></textarea>
				<span class="help-block" v-show="errors.has('clp_extranjero')">{{ errors.first('clp_extranjero') }}</span>
			</div>				
			<div class="form-group col-sm-7" v-if="selectedItem.pais_id == 6" :class="{'has-error': errors.has('direccion')}">
				<label for="direccion">Direccion</label>
				<input type="text" name="direccion" v-model="selectedItem.direccion" class="form-control" v-validate="'required'" data-vv-validate-on="none">
				<span class="help-block" v-show="errors.has('direccion')">{{ errors.first('direccion') }}</span>
			</div>				
			<div class="clearfix"></div>
			<div class="form-group col-sm-3" :class="{'has-error': errors.has('estado_civil')}">
				<label for="estado_civil">Estado Civil</label>
				<select v-model="selectedItem.estado_civil" class="form-control" name="estado_civil" v-validate="'required'" data-vv-validate-on="none">
				  <option v-for="option in info.estado_civil" v-bind:value="option.value">
				    {{ option.name }}
				  </option>
				</select>
				<span class="help-block" v-show="errors.has('estado_civil')">{{ errors.first('estado_civil') }}</span>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12">
				<fieldset v-if="selectedItem.estado_civil == 'CA' || selectedItem.estado_civil == 'CO'  || selectedItem.estado_civil == 'VI'"  style="margin-bottom:10px;">
					<legend>Datos Conyuge</legend>
					<div class="form-group col-sm-6" :class="{'has-error': errors.has('apellido_conyuge')}">
						<label for="apellido_conyuge">Apellido/s</label>
						<input type="text" name="apellido_conyuge" v-model="selectedItem.apellido_conyuge" class="form-control" v-validate="'required'" data-vv-validate-on="none">
						<span class="help-block" v-show="errors.has('apellido_conyuge')">{{ errors.first('apellido_conyuge') }}</span>
					</div>
					<div class="form-group col-sm-6" :class="{'has-error': errors.has('nombre_conyuge')}">
						<label for="nombre_conyuge">Nombre/s</label>
						<input type="text" name="nombre_conyuge" v-model="selectedItem.nombre_conyuge" class="form-control" v-validate="'required'" data-vv-validate-on="none">
						<span class="help-block" v-show="errors.has('nombre_conyuge')">{{ errors.first('nombre_conyuge') }}</span> 
					</div>
					<div class="form-group col-sm-6" :class="{'has-error': errors.has('tipo_doc_conyuge_id')}">
						<label for="tipo_doc_conyuge_id">Tipo Doc.</label>
						<select v-model="selectedItem.tipo_doc_conyuge_id" class="form-control" name="tipo_doc_conyuge_id" v-validate="'required'" data-vv-validate-on="none">
						  <option v-for="option in info.tipo_doc" v-bind:value="option.id">
						    {{ option.nombre }}
						  </option>
						</select>
						<span class="help-block" v-show="errors.has('tipo_doc_conyuge_id')">{{ errors.first('tipo_doc_conyuge_id') }}</span>
					</div>
					<div class="form-group col-sm-6" :class="{'has-error': errors.has('nro_doc_conyuge')}">
						<label for="nro_doc_conyuge">Nro. Doc.</label>
						<input type="text" name="nro_doc_conyuge" v-model="selectedItem.nro_doc_conyuge" class="form-control" v-validate="'required'" data-vv-validate-on="none">
						<span class="help-block" v-show="errors.has('nro_doc_conyuge')">{{ errors.first('nro_doc_conyuge') }}</span>
					</div>
					<div class="clearfix"></div>
					<div class="form-group col-sm-4" :class="{'has-error': errors.has('fecha_casamiento')}" v-if="selectedItem.estado_civil == 'CA' || selectedItem.estado_civil == 'VI'">
						<label for="fecha_casamiento">Fecha casamiento</label><br>
						<datepicker  name="fecha_casamiento" v-model="selectedItem.fecha_casamiento" :format="'dd/MM/yyyy'" :clear-button="true" ></datepicker>
						<span class="help-block" v-show="errors.has('fecha_casamiento')">{{ errors.first('fecha_casamiento') }}</span>
					</div>	
					<div class="form-group col-sm-4" :class="{'has-error': errors.has('anios_convivencia')}" v-if="selectedItem.estado_civil == 'CO'">
						<label for="anios_convivencia">Años de convivencia</label>
						<input type="text" name="anios_convivencia" v-model="selectedItem.anios_convivencia" class="form-control"  v-mask="['##']">
						<span class="help-block" v-show="errors.has('anios_convivencia')">{{ errors.first('anios_convivencia') }}</span>
					</div>					
					<div class="form-group col-sm-4" :class="{'has-error': errors.has('fecha_enviudez')}">
						<label for="fecha_enviudez">Fecha enviudez</label><br>
						<datepicker  name="fecha_enviudez" v-model="selectedItem.fecha_enviudez" :format="'dd/MM/yyyy'" :clear-button="true" placeholder="Ingrese la fecha"></datepicker>
						<span class="help-block" v-show="errors.has('fecha_enviudez')">{{ errors.first('fecha_enviudez') }}</span>
					</div>	
					<div class="form-group col-sm-4" :class="{'has-error': errors.has('hijos_comun')}">
						<label for="hijos_comun">Hijos en común</label>
						<input type="text" name="hijos_comun" v-model="selectedItem.hijos_comun" class="form-control" v-mask="['##']">
						<span class="help-block" v-show="errors.has('hijos_comun')">{{ errors.first('hijos_comun') }}</span>
					</div>	
					<div class="clearfix"></div>
					<div class="form-group col-sm-6" :class="{'has-error': errors.has('telefono_conyuge')}">
						<label for="telefono_conyuge">Teléfono</label>
						<input type="text" name="telefono_conyuge" v-model="selectedItem.telefono_conyuge" class="form-control">
						<!--span class="help-block" v-show="errors.has('tel_conyuge')">{{ errors.first('tel_conyuge') }}</span-->
					</div>	
					<div class="form-group col-sm-6" :class="{'has-error': errors.has('email_conyuge')}">
						<label for="email_conyuge">Email</label>
						<input type="email" name="email_conyuge" v-model="selectedItem.email_conyuge" class="form-control" v-validate="'email'" data-vv-validate-on="none">
						<!--span class="help-block" v-show="errors.has('tel_conyuge')">{{ errors.first('tel_conyuge') }}</span-->
					</div>																			
				</fieldset>
			</div>
		</div>
		<div class="row" v-if="selectedItem.personeria === 'H'">
			<div class="form-group col-sm-4" :class="{'has-error': errors.has('categoria')}">
				<label for="categoria">Categoria</label>
				<select v-model="selectedItem.categoria" class="form-control" name="categoria"  v-validate="'required'" data-vv-validate-on="none">
				  <option v-for="option in info.categorias" v-bind:value="option.id">
				    {{ option.nombre }}
				  </option>
				</select>
				<span class="help-block" v-show="errors.has('categoria')">{{ errors.first('categoria') }}</span>
			</div>
			<div v-if="selectedItem.categoria === 'E'" class="form-group col-sm-6"  :class="{'has-error': errors.has('empresa_referencia_id')}">
				<label for="empresa_referencia_id">Empresa de referencia</label>
				<select v-model="selectedItem.empresa_referencia_id" class="form-control" name="empresa_referencia_id"  v-validate="'required'" data-vv-validate-on="none">
				  <option v-for="option in info.empresas_referencia" v-bind:value="option.id">
				    {{ option.razon_social }}
				  </option>
				</select>
				<span class="help-block" v-show="errors.has('empresa_referencia_id')">{{ errors.first('empresa_referencia_id') }}</span>
			</div>
			<div class="form-group col-sm-2"  :class="{'has-error': errors.has('tipo_aporte_id')}">
				<label for="tipo_aporte_id">Tipo aporte</label>
				<select v-model="selectedItem.tipo_aporte_id" class="form-control" name="tipo_aporte_id"  v-validate="'required'" data-vv-validate-on="none">
				  <option v-for="option in info.tipos_aporte" v-bind:value="option.id">
				    {{ option.nombre }}
				  </option>
				</select>
				<span class="help-block" v-show="errors.has('tipo_aporte_id')">{{ errors.first('tipo_aporte_id') }}</span>
			</div>				
			<div class="clearfix"></div>
		</div>
		
		<div class="row" v-if="selectedItem.personeria === 'J'">
			<div class="form-group col-sm-4" :class="{'has-error': errors.has('tipo_sociedad_id')}">
				<label for="tipo_sociedad_id">Tipo Soc.</label>
				<select v-model="selectedItem.tipo_sociedad_id" class="form-control" name="tipo_sociedad_id" v-validate="'required'" data-vv-validate-on="none">
				  <option v-for="option in info.tipos_soc" v-bind:value="option.id">
				    {{ option.nombre }}
				  </option>
				</select>
				<span class="help-block" v-show="errors.has('tipo_sociedad_id')">{{ errors.first('tipo_sociedad_id') }}</span>
			</div>		
			<div class="form-group col-sm-4" :class="{'has-error': errors.has('cond_iva_id')}">
				<label for="cond_iva_id">Cond.Iva</label>
				<select v-model="selectedItem.cond_iva_id" class="form-control" name="cond_iva_id" >
				  <option v-for="option in info.cond_iva" v-bind:value="option.id">
				    {{ option.nombre }}
				  </option>
				</select>
				<span class="help-block" v-show="errors.has('cond_iva_id')">{{ errors.first('cond_iva_id') }}</span>
			</div>	
			<div class="form-group col-sm-4" :class="{'has-error': errors.has('libros_estudio')}">
				<label for="libros_estudio">Libros en el estudio</label>
				<select v-model="selectedItem.libros_estudio" class="form-control" name="libros_estudio" >
				  <option v-for="option in info.libros_estudio" v-bind:value="option.value">
				    {{ option.name }}
				  </option>
				</select>
				<span class="help-block" v-show="errors.has('libros_estudio')">{{ errors.first('libros_estudio') }}</span>
			</div>
			<div class="clearfix"></div>
			<div class="form-group col-sm-6" :class="{'has-error': errors.has('nombre_rep_legal')}">
				<label for="nombre_rep_legal">Rep. Legal</label>
				<input type="text" name="nombre_rep_legal" v-model="selectedItem.nombre_rep_legal" class="form-control" >
				<span class="help-block" v-show="errors.has('nombre_rep_legal')">{{ errors.first('nombre_rep_legal') }}</span>
			</div>
			<div class="clearfix"></div>
			<div class="form-group col-sm-5" :class="{'has-error': errors.has('sede_social')}">
				<label for="sede_social">Sede Social</label>
				<textarea name="sede_social" v-model="selectedItem.sede_social" class="form-control" ></textarea>
				<span class="help-block" v-show="errors.has('sede_social')">{{ errors.first('sede_social') }}</span>
			</div>						
			<div class="form-group col-sm-4" :class="{'has-error': errors.has('directorio')}">
				<label for="directorio">Autoridades / Directorio</label>
				<textarea name="directorio" v-model="selectedItem.directorio" class="form-control" ></textarea>
				<span class="help-block" v-show="errors.has('directorio')">{{ errors.first('directorio') }}</span>
			</div>			
			<div class="form-group col-sm-3" :class="{'has-error': errors.has('fecha_vto_directorio')}">
				<label for="fecha_vto_directorio">Vto. Directorio</label>
				<datepicker  name="fecha_vto_directorio" v-model="selectedItem.fecha_vto_directorio" :format="'dd/MM/yyyy'" :clear-button="true"  placeholder="Ingrese la fecha"></datepicker>
				<span class="help-block" v-show="errors.has('fecha_vto_directorio')">{{ errors.first('fecha_vto_directorio') }}</span>
			</div>				
			<div class="clearfix"></div>
			<div class="form-group col-sm-6" :class="{'has-error': errors.has('dom_fiscal')}">
				<label for="dom_fiscal">Domicilio Fiscal</label>
				<textarea name="dom_fiscal" v-model="selectedItem.dom_fiscal" class="form-control" ></textarea>
				<span class="help-block" v-show="errors.has('dom_fiscal')">{{ errors.first('dom_fiscal') }}</span>
			</div>						
			<!--div class="form-group col-sm-6" :class="{'has-error': errors.has('dom_legal')}">
				<label for="dom_legal">Domicilio Legal</label>
				<textarea name="dom_legal" v-model="selectedItem.dom_legal" class="form-control" ></textarea>
				<span class="help-block" v-show="errors.has('dom_legal')">{{ errors.first('dom_legal') }}</span>
			</div-->			
			<div class="clearfix"></div>

		<div class="box" style="border-top: 1px solid #d2d6de;border-radius:0;">
            <div class="box-header">
              <h3 class="box-title">Domocilio Postal</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">

				<div class="form-group col-sm-3" :class="{'has-error': errors.has('pais_id')}">
					<label for="pais_id">Pais</label>
					<select v-model="selectedItem.pais_id" class="form-control" name="pais_id" v-validate="'required'" data-vv-validate-on="none">
					  <option v-for="option in info.paises" v-bind:value="option.id">
					    {{ option.nombre }}
					  </option>
					</select>
					<span class="help-block" v-show="errors.has('pais_id')">{{ errors.first('pais_id') }}</span>
				</div>
				<div class="form-group col-sm-3" v-if="selectedItem.pais_id == 6" :class="{'has-error': errors.has('provincia_id')}">
					<label for="provincia_id">Provincia</label>
					<select v-model="selectedItem.provincia_id" class="form-control" name="provincia_id" data-vv-validate-on="none">
					  <option v-for="option in info.provincias" v-bind:value="option.id">
					    {{ option.nombre }}
					  </option>
					</select>
					<span class="help-block" v-show="errors.has('provincia_id')">{{ errors.first('provincia_id') }}</span>
				</div>
				<div class="clearfix"></div>
				<div class="form-group col-sm-3" v-if="selectedItem.pais_id == 6" :class="{'has-error': errors.has('localidad')}">
					<label for="localidad">Localidad</label>
					<input type="text" name="localidad" v-model="selectedItem.localidad" class="form-control"  data-vv-validate-on="none">
					<span class="help-block" v-show="errors.has('localidad')">{{ errors.first('localidad') }}</span>
				</div>
				<div class="form-group col-sm-2" v-if="selectedItem.pais_id == 6" :class="{'has-error': errors.has('cp')}">
					<label for="cp">CP</label>
					<input type="text" name="cp" v-model="selectedItem.cp" class="form-control">
					<span class="help-block" v-show="errors.has('cp')">{{ errors.first('cp') }}</span>
				</div>
				<div class="form-group col-sm-12" v-if="selectedItem.pais_id != 6" :class="{'has-error': errors.has('clp_extranjero')}">
					<label for="clp_extranjero">Domicilio</label>
					<textarea name="clp_extranjero" v-model="selectedItem.clp_extranjero" class="form-control"></textarea>
					<span class="help-block" v-show="errors.has('clp_extranjero')">{{ errors.first('clp_extranjero') }}</span>
				</div>				
				<div class="form-group col-sm-7" v-if="selectedItem.pais_id == 6" :class="{'has-error': errors.has('direccion')}">
					<label for="direccion">Direccion</label>
					<input type="text" name="direccion" v-model="selectedItem.direccion" class="form-control" data-vv-validate-on="none">
					<span class="help-block" v-show="errors.has('direccion')">{{ errors.first('direccion') }}</span>
				</div>		
            </div>
        </div>


			<div class="clearfix"></div>			
			<div class="form-group col-sm-6" :class="{'has-error': errors.has('actividad')}">
				<label for="actividad">Actividad que presta</label>
				<textarea name="actividad" v-model="selectedItem.actividad" class="form-control" ></textarea>
				<span class="help-block" v-show="errors.has('actividad')">{{ errors.first('actividad') }}</span>
			</div>						
			<div class="form-group col-sm-6" :class="{'has-error': errors.has('facultades')}">
				<label for="facultades">Facultades para absolver posiciones</label>
				<textarea name="facultades" v-model="selectedItem.facultades" class="form-control" ></textarea>
				<span class="help-block" v-show="errors.has('facultades')">{{ errors.first('facultades') }}</span>
			</div>		
		</div>

		<div class="row">	
			<div class="form-group col-sm-3" :class="{'has-error': errors.has('fecha_entrevista')}">
				<label for="fecha_entrevista">Fecha 1ra Ent.</label>
				<datepicker   v-model="selectedItem.fecha_entrevista" :format="'dd/MM/yyyy'" :clear-button="true" v-validate="'required'" data-vv-validate-on="none" placeholder="Ingrese la fecha"></datepicker>
				<span class="help-block" v-show="errors.has('fecha_entrevista')">{{ errors.first('fecha_entrevista') }}</span>
			</div>
			<div class="form-group col-sm-6" :class="{'has-error': errors.has('ubicacion_carpeta')}">
				<label for="ubicacion_carpeta">Ubicación carpeta</label>
				<input type="text" name="ubicacion_carpeta" v-model="selectedItem.ubicacion_carpeta" class="form-control" >
				<span class="help-block" v-show="errors.has('ubicacion_carpeta')">{{ errors.first('ubicacion_carpeta') }}</span>
			</div>	
			<div class="form-group col-sm-3" :class="{'has-error': errors.has('nro_correlativo')}">
				<label for="nro_correlativo">Nro. Correlativo</label>
				<input type="text" name="nro_correlativo" v-model="selectedItem.nro_correlativo" class="form-control" v-mask="['#########']" >
				<span class="help-block" v-show="errors.has('nro_correlativo')">{{ errors.first('nro_correlativo') }}</span>
			</div>	
			<div class="form-group col-sm-3" :class="{'has-error': errors.has('clave_seguridad_social')}">
				<label for="clave_seguridad_social">Clave Seg. Social</label>
				<input type="text" name="clave_seguridad_social" v-model="selectedItem.clave_seguridad_social" class="form-control">
				<span class="help-block" v-show="errors.has('clave_seguridad_social')">{{ errors.first('clave_seguridad_social') }}</span>
			</div>
			<div class="form-group col-sm-3" :class="{'has-error': errors.has('clave_fiscal')}">
				<label for="clave_fiscal">Clave Fiscal</label>
				<input type="text" name="clave_fiscal" v-model="selectedItem.clave_fiscal" class="form-control">
				<span class="help-block" v-show="errors.has('clave_fiscal')">{{ errors.first('clave_fiscal') }}</span>
			</div>												
			<div class="clearfix"></div>
			<div class="form-group col-sm-6">
				<tel-email :items="selectedItem.telefonos" :type="'T'" :can-edit="true"></tel-email>
			</div>
			<div class="form-group col-sm-6">
				<tel-email :items="selectedItem.emails" :type="'E'"  :can-edit="true"></tel-email>
			</div>
		</div>

		<div class="row">
			<div class="col-xs-12 text-right">
		    	<button-type type="back" @click="back()"/>        
		    	<button-type v-can="[actionPerm+':U',actionPerm+':C']" type="save" :promise="save"/>
			</div>
		</div>
	</div>
</template>
<script>
import Vue from 'vue'
import { datepicker } from 'vue-strap'
import config from '../../../../config'
import Api from '../../../../api'
import TelEmail from './TelEmail'

export default {
  name: 'SolapaGeneral',
  components: {
    datepicker,
    TelEmail
  },
  props: {
  	selectedItem: {
  		type: Object,
  		require: true
  	},
  	info: {
  		type: Object,
  		require: true
  	},
  	actionPerm: {
  		type: String,
  		required: true,
  		default: 'clientes'
  	},

  },
  data () {
	return {

		title: 'Crear/Editar Cliente',
		submited: false,
		show: false,
		messages: '', 
		uri: 'clientes/',
		apiUrl: '',
	}
  },  
  mounted () {
  	this.apiUrl = config.serverURI + this.uri;
  	if (this.selectedItem.personeria == 'J') {
  		this.selectedItem.pais_id = 6;
  	}
  	/*Api.combos('am-cliente').then((resp) => {
  		this.info = resp.data;
	});	*/
  },  
  methods: {
  	addTelefono () {
  		this.selectedItem.telefonos.push(this.selectedPhone);
  		this.resetTelefono();
  	},
  	editTelefono (item) {
  		this.selectedPhone = _.clone(item);
  	},
  	delTelefono (index) {
  		this.selectedItem.telefonos.splice(index,1);
  	},

  	resetTelefono () {
  		this.selectedPhone = {
  			num: null,
  			desc: null
  		}
  	},
  	addEmail () {
  		this.selectedItem.emails.push(this.selectedEmail);
  		this.resetEmail();
  	},
  	editEmail (item) {
  		this.selectedEmail = _.clone(item);
  	},
  	delEmail (index) {
  		this.selectedItem.emails.splice(index,1);
  	},

  	resetEmail () {
  		this.selectedEmail = {
  			email: null,
  			desc: null
  		}
  	},

    save () {
    	return new Promise((resolve, reject) => {
	    	let errors = [];
	    	//this.errors = ''
			this.$validator.validateAll().then((result) => {
		        if (result) {
		        	if (this.selectedItem.telefonos.length < 1) {
	        			errors.push('Ingrese al menos un teléfono')
		        	}
		        	if (this.selectedItem.emails.length < 1) {
	        			errors.push('Ingrese al menos un e-mail')
		        	}
		        	if (errors.length > 0) {
		        		this.$store.dispatch('showErrorNotification',errors)
				        this.$nextTick(() => {
				            this.$refs.topWindows.scrollTop = 0;
				            reject();
				        });	
		        	} else {
				    	Api.store(this.uri,this.selectedItem)
				    		.then((result) => {
				    				this.$store.dispatch('showSuccessNotification',result.data.message)
        		        			this.$emit('clientes:save','general',result.data.data)
		        					resolve();

				    		}, (resp) => {
				    			let errors = [];
				    			if (resp.fields) {
				    				for(var key in resp.fields) {
										this.addError(key, resp.fields[key][0], 'server'); 								    	
										errors.push(resp.fields[key][0]);
								   	}	        				
				    			}
			          			this.$store.dispatch('showErrorNotification',errors)
				          		reject();
				    		});		        		
		        	}
		        	
		          	return;
		        } else {
		        	reject();
		        }
	      	})
      	})
    },

    back() {
    	this.$emit('clientes:back','general')
    }
  }
}	
</script>