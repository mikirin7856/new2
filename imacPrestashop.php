<?php
	/*
	Plugin Name: imacPrestashop
	Plugin URI: https://imacreste.com/productos-prestashop-en-wordpress/
	Description: Con este plugin podrás incluir en tus entradas, páginas y Widgets los productos de tu tienda prestashop.
	Author: imacreste
	Version: 2.0.19
	Author URI: https://imacreste.com
	============================================================================================================
	Copyright 2025 imacreste (email: imacreste@gmail.com).
	
	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	For full license details see license.txt.
		
	============================================================================================================
	*/
	
	if ( ! defined( 'ABSPATH' ) ) {
		die('Error de acceso.');
	}
	
	add_action( 'wp_enqueue_scripts','cargar_css_js');
	function cargar_css_js(){
		wp_register_style('imacPrestashop_css', plugins_url('/css/style.css',__FILE__ ));
		wp_enqueue_style('imacPrestashop_css');		
	}	
	
	add_action( 'admin_enqueue_scripts','cargar_css_js_admin');
	function cargar_css_js_admin(){
		wp_register_style('imacPrestashop_css', plugins_url('/css/admin_style.css',__FILE__ ));
		wp_enqueue_style('imacPrestashop_css');		
	}	
	
	add_action('admin_menu','imacPrestashop_menus');
	function imacPrestashop_menus(){				
		add_options_page('Configuraciones Prestashop WebService','imacprestashop', 'manage_options', 'imacprestashop', 'imacprestashop_fc');		
		add_submenu_page(NULL,'Configuraciones Prestashop Base de datos (OPCIÓN ANTIGUA)','Configuraciones Prestashop Base de datos (OPCIÓN ANTIGUA)','manage_options','imacprestashopbd','imacprestashopbd_fc');
		add_submenu_page(NULL,'Ayuda WebService','Ayuda WebService','manage_options','imacprestashop_help','imacprestashop_help_fc');
		add_submenu_page(NULL,'Ayuda Base de datos','Ayuda Base de datos','manage_options','imacPrestashopbd_help','imacprestashopbd_help_fc');
		add_action('admin_init', 'imacPrestashop_settings');
	}
	
	function imacPrestashop_settings(){		
		register_setting('imacPrestashop-grupo-config', 'imacPrestashop_options', 'imacPrestashop_sanitize');		
	}
	
	function plugin_add_settings_link( $links ) {
    $settings_link = '<a href="options-general.php?page=imacprestashop">' . __( 'Settings' ) . '</a>';
    array_push( $links, $settings_link );
  	return $links;
	}
	$plugin = plugin_basename( __FILE__ );
	add_filter( "plugin_action_links_$plugin", 'plugin_add_settings_link' );
	
	function imacprestashop_fc(){								
	?>
		<script type="text/javascript">						
			jQuery(document).ready(function($) {
				$('.probar_conexion').click(function(){
					$('#probando_bd').show();
				});		
			});		
		</script>
		<div class="wrap">								
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab nav-tab-active" href="?page=imacprestashop"><?php _e("Configuración de Prestashop WebService", "imacprestashop");?></a>
				<a class="nav-tab" href="?page=imacprestashop_help"><?php _e("Ayuda WebService", "imacprestashop");?></a>
				<!--
				<a class="nav-tab" href="?page=imacprestashopbd"><?php _e("Configuración de Prestashop base de Datos", "imacprestashop");?></a>
				<a class="nav-tab" href="?page=imacPrestashopbd_help"><?php _e("Ayuda base de Datos", "imacprestashop");?></a>
				-->
			</h2>
			<?php 
					settings_fields('imacprestashop-grupo-config');
					$imacprestashop_options=get_option('imacprestashop_options');
					
					if (isset($_POST['option_pass_ws'])){
						check_admin_referer('guardar_imac_settings','imacprestashop_guardar_settings');
						$imacprestashop_options['option_url_ws']=sanitize_text_field($_POST['option_url_ws']);
						$imacprestashop_options['option_pass_ws']=imacprestashop_encriptacion::encriptar($_POST['option_pass_ws']);
						$imacprestashop_options['option_idioma_ws']=sanitize_text_field($_POST['option_idioma_ws']);
						$imacprestashop_options['option_iva_ws']=sanitize_text_field($_POST['option_iva_ws']);
						$imacprestashop_options['show_idioma']=sanitize_text_field($_POST['show_idioma']);
						$imacprestashop_options['option_urlP_ws']=sanitize_text_field($_POST['option_urlP_ws']);
						$imacprestashop_options['option_productos_ws']=sanitize_text_field($_POST['option_productos_ws']);
						$imacprestashop_options['option_categoria_ws']=sanitize_text_field($_POST['option_categoria_ws']);												
						$imacprestashop_options['option_moneda_ws']=sanitize_text_field($_POST['option_moneda_ws']);
						$imacprestashop_options['option_nofollow_ws']=0;
						$imacprestashop_options['option_ofertas']=0;
						if (isset($_POST['option_nofollow_ws']))$imacprestashop_options['option_nofollow_ws']=sanitize_text_field($_POST['option_nofollow_ws']);
						if (isset($_POST['option_ofertas']))$imacprestashop_options['option_ofertas']=sanitize_text_field($_POST['option_ofertas']);									
						
						update_option( 'imacprestashop_options', $imacprestashop_options );						
					}								
			?>
			<form method="post" action="options-general.php?page=imacprestashop">
				<?php wp_nonce_field('guardar_imac_settings','imacprestashop_guardar_settings');?>
				
				<p><br><em class="clave"><?php _e("<strong>Hay 2 formas de usar el plugin:<ol><li>Usando WebService (recomendado).</li><li>Usando base de datos (recomendado: Si no puedes activar WebService, puedes acceder <a href='?page=imacprestashopbd'>aquí</a>).</li></ol></strong><hr><strong>La contraseña se guarda encriptada en la base de datos</strong>. <br>Si no logras configurarlo correctamente, puedes escribirme un <a href='mailto:imacreste@gmail.com'>mail</a> facilitándome la mayor información posible.", "imacprestashop");?></em></p>	
				<table class="form-table">																				
					<tr valign="top">
						<th scope="row">
							<?php _e("Url de la tienda Prestashop:","imacprestashop");?>
						</th>
						<td>							
							<input type="text" name="option_url_ws" required value="<?php echo (isset($imacprestashop_options['option_url_ws'])) ? esc_attr($imacprestashop_options['option_url_ws']) : '';?>" />
							<em><?php _e("<br>Incluir http o https, ejemplo: https://imacreste.com o http://www.orainbai.es (sin / final).", "imacprestashop");?></em>								
						</td>
					</tr>	
					<tr valign="top">
						<th scope="row">
							<?php _e("Contraseña/clave webservice:", "imacprestashop");?>
						</th>
						<td>
							<input type="text" name="option_pass_ws" required value="<?php echo (isset($imacprestashop_options['option_pass_ws'])) ? imacprestashop_encriptacion::desencriptar($imacprestashop_options['option_pass_ws']) : '';?>" />			
							<em><?php _e("<br>Es la contraseña que se genera al activar webservice en Prestashop, desde el Admin de Prestashop -> Parámetros avanzados -> webservice => Activar el servicio Web. <br><a href='options-general.php?page=imacprestashop_help'>En ayuda tienes más información</a>.", "imacprestashop");?></em>					
						</td>
					</tr>	
					<tr valign="top">
						<th scope="row">
							<?php _e("Permisos que debes activar:", "imacprestashop");?>
						</th>
						<td>					
							<?php _e("categories, languages, images, price_ranges, products, specific_prices, stock_availables, taxes, tax_rules, tax_rule_groups.", "imacprestashop");?>
							<em><?php _e("<br>Estos permisos se deben checkear desde la misma página que se genera la contraseña/clave. Solo debes checkear la columna de VER (GET). <br><a href='options-general.php?page=imacprestashop_help'>En ayuda tienes más información</a>.", "imacprestashop");?></em>					
						</td>
					</tr>			
					<tr valign="top">
						<th scope="row">
							<?php _e("ID idioma:", "imacprestashop");?>
						</th>
						<td>							
							<input type="number" name="option_idioma_ws" required value="<?php echo (isset($imacprestashop_options['option_idioma_ws'])) ? esc_attr($imacprestashop_options['option_idioma_ws']) : 0;?>" />	
							<em><?php _e("<br>Tiene que ser un valor numérico. Este ID lo puedes ver desde tu Prestashop admin -> Internacional (Prestashop 1.7) -> Localización -> Idiomas. Y se usará por defecto a la hora de mostrar el nombre del producto. <br><strong>IMPORTANTE</strong>: Si solo usas un idioma el valor debe ser 0.", "imacprestashop");?></em>						
						</td>
					</tr>	
					<tr valign="top">
						<th scope="row">
							<?php _e("IVA por defecto:", "imacprestashop");?>
						</th>
						<td>							
							<input type="number" name="option_iva_ws" min="0" max="100" step="0.01" required value="<?php echo (isset($imacprestashop_options['option_iva_ws'])) ? esc_attr($imacprestashop_options['option_iva_ws']) : 0;?>" />													
						</td>
					</tr>					
					<tr valign="top">
						<th scope="row">
							<?php _e("Mostrar idioma  en la URL:", "imacprestashop");?>
						</th>
						<td>							
							<input type="number" name="show_idioma" value="<?php echo (isset($imacprestashop_options['show_idioma'])) ? esc_attr($imacprestashop_options['show_idioma']) : '';?>" />	
							<em><?php _e("<br>¿Por defecto es necesario mostrar en la URL el idioma? Si es que si pon un 1 en este campo, sino dejalo en blanco.", "imacprestashop");?></em>						
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<?php _e("Símbolo de la moneda:", "imacprestashop");?>
						</th>
						<td>							
							<input type="text" name="option_moneda_ws" required value="<?php echo (isset($imacprestashop_options['option_moneda_ws'])) ? esc_attr($imacprestashop_options['option_moneda_ws']) : '€';?>" />	
							<em><?php _e("<br>Símbolo de la moneda.", "imacprestashop");?></em>						
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<?php _e("Productos base:","imacprestashop");?>
						</th>
						<td>							
							<input type="text" name="option_productos_ws" required value="<?php echo (isset($imacprestashop_options['option_productos_ws'])) ? esc_attr($imacprestashop_options['option_productos_ws']) : 1;?>" />
							<em><?php _e("<br>Introduce ID productos (Desde el admin de Prestashop -> Catalogo -> Productos puedes verlos) que se mostrarán por defecto. Separados por ,. Ejemplo: 1,2,1234,73,45678,503.", "imacprestashop");?></em>								
						</td>
					</tr>						
					<tr valign="top">
						<th scope="row">
							<?php _e("Categoría base:","imacprestashop");?>
						</th>
						<td>							
							<input type="text" name="option_categoria_ws" required value="<?php echo (isset($imacprestashop_options['option_categoria_ws'])) ? esc_attr($imacprestashop_options['option_categoria_ws']) : 1;?>" />
							<em><?php _e("<br>Introduce ID de la categoría que se usará por defecto. Ejemplo: 1", "imacprestashop");?></em>								
						</td>
					</tr>	
					<tr valign="top">
						<th scope="row">
							<?php _e("Enlaces Nofollow:", "imacprestashop");?>
						</th>
						<td>							
							<input type="checkbox" name="option_nofollow_ws" value="1" <?php checked( $imacprestashop_options['option_nofollow_ws'], '1' ); ?> />	
							<em><?php _e("<br>Si está seleccionada la casilla, los enlaces serán nofollow, es un atributo SEO. <a href='https://support.google.com/webmasters/answer/96569?hl=es' target='_blank'>Más Información</a>.", "imacprestashop");?></em>						
						</td>
					</tr>	
					<tr valign="top">
						<th scope="row">
							<?php _e("Ocultar Ofertas:", "imacPrestashop");?>
						</th>
						<td>							
							<input type="checkbox" name="option_ofertas" value="1" <?php checked( $imacprestashop_options['option_ofertas'], '1' ); ?> />	
							<em><?php _e("<br>Si esta seleccionada la casilla, No se visualizarán las ofertas, solo el precio de los productos.", "imacPrestashop");?></em>						
						</td>
					</tr>							
					<tr valign="top">
						<th scope="row">
							<?php _e("URLs de producto:", "imacprestashop");?>
						</th>
						<td>																					
							<select name="option_urlP_ws">
								<option value='0' <?php selected($imacprestashop_options['option_urlP_ws'],0);?>><?php _e("ID-name_product.html", "imacprestashop");?></option>
								<option value='1' <?php selected($imacprestashop_options['option_urlP_ws'],1);?>><?php _e("name_product-ID.html", "imacprestashop");?></option>
								<option value='2' <?php selected($imacprestashop_options['option_urlP_ws'],2);?>><?php _e("category/ID-name_product.html", "imacprestashop");?></option>
								<option value='3' <?php selected($imacprestashop_options['option_urlP_ws'],3);?>><?php _e("category/name_product-ID.html", "imacprestashop");?></option>
								<option value='4' <?php selected($imacprestashop_options['option_urlP_ws'],4);?>><?php _e("name_product.html", "imacprestashop");?></option>
								<option value='5' <?php selected($imacprestashop_options['option_urlP_ws'],5);?>><?php _e("category/name_product.html", "imacprestashop");?></option>
							</select>
							<em><?php _e("<br>Las URLS de Prestashop se pueden configurar desde:<br> Parámetros de la tienda -> Tráfico y SEO -> Ruta a los productos<br> o en versiones de Prestashop antiguas en: Preferencias -> SEO + URLs -> Ruta a los productos. <br><br>Esto permite múltiples combinaciones. Nos hemos basado en <strong>2 alternativas</strong><br>1º) por defecto, basada en: <strong>id-nombre_producto.html (2-blusa.html)</strong><br>2º) <strong>nombre_producto-id.html (blusa-2.html)</strong><br>3º) <strong>category/ID-name_product.html (ropa/2-blusa.html)</strong><br>4º) <strong>category/name_product-ID.html (ropa/blusa-2.html)</strong><br>5º) <strong>name_product.html (blusa.html - SIN ID)</strong><br>6º) <strong>category/name_product.html (ropa/blusa.html - SIN ID)</strong><br><em>Si abres un producto puedes verlo en la URL, ¿Dónde se pone el ID producto? Antes o después del nombre del producto.</em>", "imacprestashop");?></em>
						</td>
					</tr>									 
				</table>
				<p class="submit"><input type="submit" class="button-primary" value="Guardar" /> &nbsp; <a class="button-primary probar_conexion"><?php _e("Prueba la Conexión (Si haces cambios guarda primero)", "imacprestashop");?></a></p>				
				<div id="probando_bd" style="display:none;">
					<?php																		
						_e("Deberías ver los productos base:", "imacprestashop");																																				
						echo do_shortcode('[imacprestashop_productos_ws]');																
					?>
				</div>
			
			</form>
		</div>
	<?php
	}			
	
	function imacprestashop_shortcode_productos_ws($atts) {		        
		$imacprestashop_options=get_option('imacprestashop_options');	
				
		$desencriptar_pass=imacprestashop_encriptacion::desencriptar($imacprestashop_options['option_pass_ws']);					
		
		$db_pass=$desencriptar_pass;								
		$url_tienda=esc_attr($imacprestashop_options['option_url_ws']);    		
		$productos_base=esc_attr($imacprestashop_options['option_productos_ws']);
		$idioma_base=esc_attr($imacprestashop_options['option_idioma_ws']);
		$iva_base=esc_attr($imacprestashop_options['option_iva_ws']);
		$moneda_base=(isset($imacprestashop_options['option_moneda_ws'])) ? esc_attr($imacprestashop_options['option_moneda_ws']) : '€';		
		$show_idioma=esc_attr($imacprestashop_options['show_idioma']);
		$show_iva=esc_attr($imacprestashop_options['show_iva']);
		$url_base=(isset($imacprestashop_options['option_urlP_ws']) ? $imacprestashop_options['option_urlP_ws'] : 0);
		$nofollow=$imacprestashop_options['option_nofollow_ws'];		
		$txt_nofollow='';
		if ($nofollow==1){
			$txt_nofollow='rel="nofollow"';	
		}	
		$ofertas=$imacprestashop_options['option_ofertas'];	
		$datos = shortcode_atts( array(                   
        'idioma' => $idioma_base,
        'show_idioma' => $show_idioma,
        'iva' => $show_iva,
        'productos' => $productos_base,
   	), $atts );
								
		$id_idioma=(int)$datos["idioma"];
		if ($id_idioma=='')(int)$id_idioma=0;
		$show_idioma=$datos["show_idioma"];
		$show_iva=$datos["iva"];
		$productos=$datos["productos"];
		$productos = str_replace(",", "|", $productos);				
		
		require_once('webservice.php');			
		$webService = new imacPWS_webservice();													
		$xml = $webService->imacPWS_getProducts($url_tienda,$desencriptar_pass,$productos);		
			
		$resources = $xml['products'];		
		//echo "<pre>";
		//var_dump($resources);				
		$content='<ul class="short-products">';
		if (isset($resources))
		{		
			$i = 1;						
			foreach ($resources as $resource)		
			{		
				if ($id_idioma==0){					
					$nombre=$resource['name'];
					$enlace=$resource['link_rewrite'];
					$id_category_default=$resource['id_category_default'];					
					$name_category=$webService->imacPWS_getCategoria($url_tienda,$desencriptar_pass,$id_category_default);					
					$name_category=$name_category['category']['link_rewrite'];
					$tax=$resource['id_tax_rules_group'];
					$tax_id=$resource['id_tax_rules_group'];
				}else{
					$cod_idioma=$id_idioma-1;
					$nombre=$resource['name'][$cod_idioma]['value'];									
					$enlace=$resource["link_rewrite"][$cod_idioma]["value"];					
					$id_category_default=$resource['id_category_default'];
					$name_category=$webService->imacPWS_getCategoria($url_tienda,$desencriptar_pass,$id_category_default);					
					$name_category=$name_category['category']['link_rewrite'][$cod_idioma]['value'];	
					$tax=$resource['id_tax_rules_group'];							
				}	
											
				$precio_redondeado=round((float)$resource['price'],2);	
				if ($show_iva!=''){	
					$precio_iva=round((($precio_redondeado*$show_iva)/100),2);
					$precio_final=$precio_redondeado+$precio_iva;
				}else{													
					if ($iva_base!=''){
						//$tax_rate = (float)$webService->imacPWS_getTax($url_tienda,$desencriptar_pass,$tax);												
						$precio_iva=round((($precio_redondeado*$iva_base)/100),2);
						$precio_final=$precio_redondeado+$precio_iva;												
					}else{					
						$precio_final=$precio_redondeado;						
					}
				}	
				
				switch($url_base){
					case 0:
						$ver_idioma='';
						if ($show_idioma!='')$ver_idioma='/'.$webService->imacPWS_getLanguage($url_tienda,$desencriptar_pass,$id_idioma);
						$url=$url_tienda.$ver_idioma.'/'.$resource['id'].'-'.$enlace.'.html';
					break;
					case 1:
						$ver_idioma='';
						if ($show_idioma!='')$ver_idioma='/'.$webService->imacPWS_getLanguage($url_tienda,$desencriptar_pass,$id_idioma);						
						$url=$url_tienda.$ver_idioma.'/'.$enlace.'-'.$resource['id'].'.html';
					break;
					case 2:
						$ver_idioma='';
						if ($show_idioma!='')$ver_idioma='/'.$webService->imacPWS_getLanguage($url_tienda,$desencriptar_pass,$id_idioma);
						$url=$url_tienda.$ver_idioma.'/'.$name_category.'/'.$resource['id'].'-'.$enlace.'.html';														
					break;
					case 3:
						$ver_idioma='';
						if ($show_idioma!='')$ver_idioma='/'.$webService->imacPWS_getLanguage($url_tienda,$desencriptar_pass,$id_idioma);
						$url=$url_tienda.$ver_idioma.'/'.$name_category.'/'.$enlace.'-'.$resource['id'].'.html';												
					break;
					case 4:
						$ver_idioma='';
						if ($show_idioma!='')$ver_idioma='/'.$webService->imacPWS_getLanguage($url_tienda,$desencriptar_pass,$id_idioma);
						$url=$url_tienda.$ver_idioma.'/'.$enlace.'.html';												
					break;
					case 5:
						$ver_idioma='';
						if ($show_idioma!='')$ver_idioma='/'.$webService->imacPWS_getLanguage($url_tienda,$desencriptar_pass,$id_idioma);
						$url=$url_tienda.$ver_idioma.'/'.$name_category.'/'.$enlace.'.html';												
					break;
				}				
				$stock=$webService->imacPWS_getStock($url_tienda,$desencriptar_pass,$resource['associations']['stock_availables'][0]['id']);								
				$datos=array();
				$datos=$webService->imacPWS_getOffer($url_tienda,$desencriptar_pass,$resource['id']);				
				if ((isset($datos)) && ($datos!='')){
					$dto=(float)$datos[1];
					$dto_type=$datos[0];				
					$hasta=$datos[2];						
				}else{
					$dto=0;
					$dto_type=0;
				}
				if ($dto_type=='percentage'){
					$total_dto=0;
					if ($dto!=''){					
						$precio_dto=($precio_final*$dto);
						$precio_con_final=($precio_final-$precio_dto);
						$txt_dto=number_format($dto*100, 2, ',', '')." %";
						$txt_fecha="";if ($hasta!='')$txt_fecha="<br>".$hasta;
					}
				}else{
					$total_dto=0;
					if ($dto!=''){
						$precio_con_final=($precio_final-$dto);
						$txt_dto=number_format($dto, 2, ',', '')." ".$moneda_base;
						$txt_fecha="";if ($hasta!='')$txt_fecha="<br>".$hasta;
					}
				}											
				$resto = ($i % 3);										
				$content.="<li>";					
				$content.="<a ".$txt_nofollow." title='".$nombre."' href='".$url."' target='blank_'>";																					
				$content.="<img style='border-color:#F90' src='data:image/jpg;base64,".$webService->imacPWS_getImage($url_tienda,$desencriptar_pass,$resource['id'],$resource['id_default_image'],$desencriptar_pass)."' alt='".$nombre."' /></a>";						
				$content.="<br><a ".$txt_nofollow." class='product_name' title='".$nombre."' href='".$url."' target='blank_'>";				
				$content.=$nombre;
				$content.="</a><br>";																			
				if ($dto==''){
					$content.="<span class='price'>".number_format($precio_final,2,',','.')." ".$moneda_base."</span><br>";									
				}else{
					if ($ofertas!=1){		
						$content.="<span class='offer'>".number_format($precio_final, 2, ',', '')." ".$moneda_base."</span><span class='price'>".number_format($precio_con_final,2,',','.')." ".$moneda_base."</span> <span class='dto'>(- ".$txt_dto.")</span><br>";
					}else{
						$content.="<span class='price'>".number_format($precio_final,2,',','.')." ".$moneda_base."</span><br>";
					}
				}				
				$content.="</li>";				
				$i++;				
			}	
			$content.="</ul><p class='important'><a href='https://imacreste.com' target='_blank'>Creado por imacreste</a></p>";
			
		}else{
			$content="";
		}			
	
		return $content;
	}
	add_shortcode('imacprestashop_productos_ws', 'imacprestashop_shortcode_productos_ws');

	function imacprestashop_shortcode_categorias_ws($atts) {		        
		$imacprestashop_options=get_option('imacprestashop_options');	
				
		$desencriptar_pass=imacprestashop_encriptacion::desencriptar($imacprestashop_options['option_pass_ws']);					
		
		$db_pass=$desencriptar_pass;								
		$url_tienda=esc_attr($imacprestashop_options['option_url_ws']);    		
		$productos_base=esc_attr($imacprestashop_options['option_productos_ws']);
		$categoria_base=esc_attr($imacprestashop_options['option_categoria_ws']);
		
		$num_productos=3;
		$idioma_base=esc_attr($imacprestashop_options['option_idioma_ws']);		
		$iva_base=esc_attr($imacprestashop_options['option_iva_ws']);
		$moneda_base=(isset($imacprestashop_options['option_moneda_ws'])) ? esc_attr($imacprestashop_options['option_moneda_ws']) : '€';				
		$show_idioma=esc_attr($imacprestashop_options['show_idioma']);
		$show_iva=esc_attr($imacprestashop_options['show_iva']);
		$url_base=(isset($imacprestashop_options['option_urlP_ws']) ? $imacprestashop_options['option_urlP_ws'] : 0);
		$nofollow=$imacprestashop_options['option_nofollow_ws'];		
		$txt_nofollow='';
		if ($nofollow==1){
			$txt_nofollow='rel="nofollow"';	
		}	
		$ofertas=$imacprestashop_options['option_ofertas'];		
		$datos = shortcode_atts( array(                   
        'idioma' => $idioma_base,
        'categoria_id' => $categoria_base,
        'show_idioma' => $show_idioma,
        'iva' => $show_iva,
        'num_productos' => $num_productos,
   	), $atts );
								
		$id_idioma=(int)$datos["idioma"];
		$categoria_id=(int)$datos["categoria_id"];
		$show_iva=$datos["iva"];
		$num_productos=(int)$datos["num_productos"];		
		if ($id_idioma=='')(int)$id_idioma=0;
		if (isset($datos["productos"])){
			$productos=$datos["productos"];				
		}else{
			$productos=0;
		}
		$productos = str_replace(",", "|", $productos);
		require_once('webservice.php');			
		$webService = new imacPWS_webservice();													
		$xml = $webService->imacPWS_getCategoria($url_tienda,$desencriptar_pass,$categoria_id);						
		$resources = $xml['category'];	
		$productos_categoria=$resources['associations']['products'];
		$productos='';
		$cont=0;
		foreach ($productos_categoria as $p_cat)		
		{	
			$productos.=$p_cat['id']."|";
			$cont++;
			if ($num_productos==$cont)break;
		}				
		$xml = $webService->imacPWS_getProducts($url_tienda,$desencriptar_pass,$productos);	
			
		$resources = $xml['products'];						
		$content='<ul class="short-products">';
		if (isset($resources))
		{		
			$i = 1;						
			foreach ($resources as $resource)		
			{		
				if ($id_idioma==0){					
					$nombre=$resource['name'];
					$enlace=$resource['link_rewrite'];
					$id_category_default=$resource['id_category_default'];					
					$name_category=$webService->imacPWS_getCategoria($url_tienda,$desencriptar_pass,$id_category_default);					
					$name_category=$name_category['category']['link_rewrite'];
					$tax=$resource['id_tax_rules_group'];
				}else{
					$cod_idioma=$id_idioma-1;
					$nombre=$resource['name'][$cod_idioma]['value'];
					$enlace=$resource['link_rewrite'][$cod_idioma]['value'];
					$id_category_default=$resource['id_category_default']; 										
					$name_category=$webService->imacPWS_getCategoria($url_tienda,$desencriptar_pass,$id_category_default);					
					$name_category=$name_category['category']['link_rewrite'][$cod_idioma]['value'];				
					$tax=$resource['id_tax_rules_group'];	
				}																		
				$precio_redondeado=round((float)$resource['price'],2);		
				if ($show_iva!=''){	
					$precio_iva=round((($precio_redondeado*$show_iva)/100),2);
					$precio_final=$precio_redondeado+$precio_iva;
				}else{													
					if ($iva_base!=''){
						//$tax_rate = (float)$webService->imacPWS_getTax($url_tienda,$desencriptar_pass,$tax);												
						$precio_iva=round((($precio_redondeado*$iva_base)/100),2);
						$precio_final=$precio_redondeado+$precio_iva;												
					}else{					
						$precio_final=$precio_redondeado;						
					}
				}	 											
								
				$stock=$webService->imacPWS_getStock($url_tienda,$desencriptar_pass,$resource['associations']['stock_availables'][0]['id']);								
				$datos=array();
				$datos=$webService->imacPWS_getOffer($url_tienda,$desencriptar_pass,$resource['id']);				
				if ((isset($datos)) && ($datos!='')){
					$dto=(float)$datos[1];
					$dto_type=$datos[0];
					$hasta=$datos[2];							
				}else{
					$dto=0;
					$dto_type=0;
				}		
				switch($url_base){
					case 0:
						$ver_idioma='';
						if ($show_idioma!='')$ver_idioma='/'.$webService->imacPWS_getLanguage($url_tienda,$desencriptar_pass,$id_idioma);
						$url=$url_tienda.$ver_idioma.'/'.$resource['id'].'-'.$enlace.'.html';
					break;
					case 1:
						$ver_idioma='';
						if ($show_idioma!='')$ver_idioma='/'.$webService->imacPWS_getLanguage($url_tienda,$desencriptar_pass,$id_idioma);
						$url=$url_tienda.$ver_idioma.'/'.$enlace.'-'.$resource['id'].'.html';
					break;
					case 2:
						$ver_idioma='';
						if ($show_idioma!='')$ver_idioma='/'.$webService->imacPWS_getLanguage($url_tienda,$desencriptar_pass,$id_idioma);
						$url=$url_tienda.$ver_idioma.'/'.$name_category.'/'.$resource['id'].'-'.$enlace.'.html';														
					break;
					case 3:
						$ver_idioma='';
						if ($show_idioma!='')$ver_idioma='/'.$webService->imacPWS_getLanguage($url_tienda,$desencriptar_pass,$id_idioma);
						$url=$url_tienda.$ver_idioma.'/'.$name_category.'/'.$enlace.'-'.$resource['id'].'.html';												
					break;
					case 4:
						$ver_idioma='';
						if ($show_idioma!='')$ver_idioma='/'.$webService->imacPWS_getLanguage($url_tienda,$desencriptar_pass,$id_idioma);
						$url=$url_tienda.$ver_idioma.'/'.$enlace.'.html';												
					break;
					case 5:
						$ver_idioma='';
						if ($show_idioma!='')$ver_idioma='/'.$webService->imacPWS_getLanguage($url_tienda,$desencriptar_pass,$id_idioma);
						$url=$url_tienda.$ver_idioma.'/'.$name_category.'/'.$enlace.'.html';												
					break;
				}
				if ($dto_type=='percentage'){
					$total_dto=0;
					if ($dto!=''){					
						$precio_dto=($precio_final*$dto);
						$precio_con_final=($precio_final-$precio_dto);
						$txt_dto=number_format($dto*100, 2, ',', '')." %";
						//$txt_fecha="";if ($hasta!='')$txt_fecha="<br><em>".$hasta."</em>";
					}
				}else{
					$total_dto=0;
					if ($dto!=''){
						$precio_con_final=($precio_final-$dto);
						$txt_dto=number_format($dto, 2, ',', '')." ".$moneda_base;
						//$txt_fecha="";if ($hasta!='')$txt_fecha="<br>".$hasta;
					}
				}									
				$resto = ($i % 3);										
				$content.="<li>";					
				$content.="<a ".$txt_nofollow." title='".$nombre."' href='".$url."' target='blank_'>";																					
				$content.="<img style='border-color:#F90' src='data:image/jpg;base64,".$webService->imacPWS_getImage($url_tienda,$desencriptar_pass,$resource['id'],$resource['id_default_image'],$desencriptar_pass)."' alt='".$nombre."' /></a>";						
				$content.="<br><a ".$txt_nofollow." class='product_name' title='".$nombre."' href='".$url."' target='blank_'>";				
				$content.=$nombre;
				$content.="</a><br>";																	
				if ($dto==''){
					$content.="<span class='price'>".number_format($precio_final,2,',','.')." ".$moneda_base."</span><br>";									
				}else{
					if ($ofertas!=1){		
						$content.="<span class='offer'>".number_format($precio_final, 2, ',', '')." ".$moneda_base."</span><span class='price'>".number_format($precio_con_final,2,',','.')." ".$moneda_base."</span> <span class='dto'>(- ".$txt_dto.")</span><br>";
					}else{
						$content.="<span class='price'>".number_format($precio_final,2,',','.')." ".$moneda_base."</span><br>";									
					}
				}
				$content.="</li>";				
				$i++;				
			}	
				$content.="</ul><p class='important'><a href='https://imacreste.com' target='_blank'>Creado por imacreste</a></p>";
			
		}else{
			$content="";
		}			
	
		return $content;
	}
	add_shortcode('imacprestashop_categorias_ws', 'imacprestashop_shortcode_categorias_ws');
	
	add_action('widgets_init','imacPrestashop_widget_ws');
	function imacPrestashop_widget_ws() {	
		register_widget( 'imacPrestashop_1_widget_ws' );
		register_widget( 'imacPrestashop_cat_widget_ws' );	
	}	
	
	class imacPrestashop_1_widget_ws extends WP_Widget{
		function __construct(){
			$options = array(
				'classname' => 'imacPrestashop_class',
				'description' => 'Mostrar productos prestashop.'
			);
			parent::__construct('imacPrestashop_widget_webservice','Prestashop productos WS',$options);
		}
	
	  function form($instance){			
			$imacPrestashop_options=get_option('imacPrestashop_options');						
			$defaults= array(
				'title' => 'Productos',
				'idioma' => '0',
				'show_idioma' => '',
				'id_products' => ''
			);
			$instance=wp_parse_args((array) $instance, $defaults);

			$id_products=$instance['id_products'];
			$title=$instance['title'];
			$idioma=$instance['idioma'];
			$show_idioma=$instance['show_idioma'];
			?>			
				<p><?php _e("Título:", "imacPrestashop");?> <input type="text" class="widefat" name="<?php echo $this->get_field_name('title')?>" value="<?php echo esc_attr($title)?>" /></p>				
				<p><?php _e("Id productos:", "imacPrestashop");?> <input type="text" class="widefat" name="<?php echo $this->get_field_name('id_products')?>" value="<?php echo esc_attr($id_products)?>" /></p>
				<p><?php _e("ID idioma:", "imacPrestashop");?> <input type="text" class="widefat" name="<?php echo $this->get_field_name('idioma')?>" value="<?php echo esc_attr($idioma)?>" /></p>
				<p><?php _e("Mostrar idioma en la URL (1 => SI, En blanco => NO):", "imacPrestashop");?> <input type="text" class="widefat" name="<?php echo $this->get_field_name('show_idioma')?>" value="<?php echo esc_attr($show_idioma)?>" /></p>				
			<?php
		}

		function update($new_instance,$old_instance){
			global $file_prefix;
	    if ( function_exists( 'wp_cache_clean_cache' ) ) wp_cache_clean_cache( $file_prefix );	   
			$instance=$old_instance;			
			$instance['id_products']=sanitize_text_field($new_instance['id_products']);
			$instance['title']=sanitize_text_field($new_instance['title']);
			$instance['idioma']=sanitize_text_field($new_instance['idioma']);
			$instance['show_idioma']=sanitize_text_field($new_instance['show_idioma']);						
			return $instance;
		}
		
		function widget($args, $instance){
			extract($args);			
			echo $before_widget;								
			if ( function_exists( 'wp_cache_clean_cache' ) ) wp_cache_clean_cache( $file_prefix );		
			
			$imacprestashop_options=get_option('imacprestashop_options');		
			$id_products=(!empty($instance['id_products']) ? $instance['id_products'] : 6);
			$title=$instance['title'];			
			$idioma=(!empty($instance['idioma']) ? $instance['idioma'] : 0);
			$iva=(!empty($instance['iva']) ? $instance['iva'] : 0);			
			$show_idioma=(!empty($instance['show_idioma']) ? $instance['show_idioma'] : '');						
						
			$desencriptar_pass=imacprestashop_encriptacion::desencriptar($imacprestashop_options['option_pass_ws']);							
			$db_pass=$desencriptar_pass;								
			$url_tienda=esc_attr($imacprestashop_options['option_url_ws']);    	
			$moneda_base=(isset($imacprestashop_options['option_moneda_ws'])) ? esc_attr($imacprestashop_options['option_moneda_ws']) : '€';			
			$productos_base=$id_products;
			$idioma_base=$idioma;
			$iva_base=$iva;			
			$url_base=(isset($imacprestashop_options['option_urlP_ws']) ? $imacprestashop_options['option_urlP_ws'] : 0);
			$nofollow=$imacprestashop_options['option_nofollow_ws'];		
			$txt_nofollow='';
			if ($nofollow==1){
				$txt_nofollow='rel="nofollow"';	
			}		
			$ofertas=$imacprestashop_options['option_ofertas'];
			$datos = shortcode_atts( array(                   
	        'idioma' => $idioma_base,
	        'productos' => $productos_base,
	   	), $atts );
					
			$id_idioma=(int)$datos["idioma"];
			if ($id_idioma=='')(int)$id_idioma=0;
			$productos=$datos["productos"];				
			$productos = str_replace(",", "|", $productos);				
			
			require_once('webservice.php');			
			$webService = new imacPWS_webservice();													
			$xml = $webService->imacPWS_getProducts($url_tienda,$desencriptar_pass,$productos);						
			$resources = $xml['products'];						
			if ( !empty($title) ){echo $before_title.esc_html($title).$after_title;}
			$content='<ul class="short-products2">';
			if (isset($resources))
			{		
				$i = 1;						
				foreach ($resources as $resource)		
				{								
					if ($id_idioma==0){					
						$nombre=$resource['name'];
						$enlace=$resource['link_rewrite'];
						$id_category_default=$resource['id_category_default'];					
						$name_category=$webService->imacPWS_getCategoria($url_tienda,$desencriptar_pass,$id_category_default);					
						$name_category=$name_category['category']['link_rewrite'];
						$tax=$resource['id_tax_rules_group'];
					}else{
						$cod_idioma=$id_idioma-1;
						$nombre=$resource['name'][$cod_idioma]['value'];
						$enlace=$resource['link_rewrite'][$cod_idioma]['value'];
						$id_category_default=$resource['id_category_default']; 										
						$name_category=$webService->imacPWS_getCategoria($url_tienda,$desencriptar_pass,$id_category_default);					
						$name_category=$name_category['category']['link_rewrite'][$cod_idioma]['value'];
						$tax=$resource['id_tax_rules_group'];
					}
					$precio_redondeado=round((float)$resource['price'],2);		
					if ($iva_base){
						//$tax_rate = (float)$webService->imacPWS_getTax($url_tienda,$desencriptar_pass,$tax);												
						$precio_iva=round((($precio_redondeado*$iva_base)/100),2);
						$precio_final=$precio_redondeado+$precio_iva;												
					}else{					
						$precio_final=$precio_redondeado;						
					}													
					switch($url_base){
						case 0:
							$ver_idioma='';
							if ($show_idioma!='')$ver_idioma='/'.$webService->imacPWS_getLanguage($url_tienda,$desencriptar_pass,$id_idioma);
							$url=$url_tienda.$ver_idioma.'/'.$resource['id'].'-'.$enlace.'.html';
						break;
						case 1:
							$ver_idioma='';
							if ($show_idioma!='')$ver_idioma='/'.$webService->imacPWS_getLanguage($url_tienda,$desencriptar_pass,$id_idioma);
							$url=$url_tienda.$ver_idioma.'/'.$enlace.'-'.$resource['id'].'.html';
						break;
						case 2:
							$ver_idioma='';
							if ($show_idioma!='')$ver_idioma='/'.$webService->imacPWS_getLanguage($url_tienda,$desencriptar_pass,$id_idioma);
							$url=$url_tienda.$ver_idioma.'/'.$name_category.'/'.$resource['id'].'-'.$enlace.'.html';														
						break;
						case 3:
							$ver_idioma='';
							if ($show_idioma!='')$ver_idioma='/'.$webService->imacPWS_getLanguage($url_tienda,$desencriptar_pass,$id_idioma);
							$url=$url_tienda.$ver_idioma.'/'.$name_category.'/'.$enlace.'-'.$resource['id'].'.html';												
						break;
						case 4:
						$ver_idioma='';
						if ($show_idioma!='')$ver_idioma='/'.$webService->imacPWS_getLanguage($url_tienda,$desencriptar_pass,$id_idioma);
						$url=$url_tienda.$ver_idioma.'/'.$enlace.'.html';												
					break;
					case 5:
						$ver_idioma='';
						if ($show_idioma!='')$ver_idioma='/'.$webService->imacPWS_getLanguage($url_tienda,$desencriptar_pass,$id_idioma);
						$url=$url_tienda.$ver_idioma.'/'.$name_category.'/'.$enlace.'.html';												
					break;
					}						
					$stock=$webService->imacPWS_getStock($url_tienda,$desencriptar_pass,$resource['associations']['stock_availables'][0]['id']);								
					$datos=array();
					$datos=$webService->imacPWS_getOffer($url_tienda,$desencriptar_pass,$resource['id']);
					if ((isset($datos)) && ($datos!='')){
						$dto=(float)$datos[1];
						$dto_type=$datos[0];		
						$hasta=$datos[2];			
					}else{
						$dto=0;
						$dto_type=0;
					}
					if ($dto_type=='percentage'){
						$total_dto=0;
						if ($dto!=''){					
							$precio_dto=($precio_final*$dto);
							$precio_con_final=($precio_final-$precio_dto);
							$txt_dto=number_format($dto*100, 2, ',', '')." %";
							$txt_fecha="";if ($hasta!='')$txt_fecha="<br>".$hasta;
						}
					}else{
						$total_dto=0;
						if ($dto!=''){
							$precio_con_final=($precio_final-$dto);
							$txt_dto=number_format($dto, 2, ',', '')." ".$moneda_base;
							$txt_fecha="";if ($hasta!='')$txt_fecha="<br>".$hasta;
						}
					}									
					$resto = ($i % 3);										
					$content.="<li>";					
					$content.="<a ".$txt_nofollow." title='".$nombre."' href='".$url."' target='blank_'>";																					
					$content.="<img style='border-color:#F90' src='data:image/jpg;base64,".$webService->imacPWS_getImage($url_tienda,$desencriptar_pass,$resource['id'],$resource['id_default_image'],$desencriptar_pass)."' alt='".$nombre."' /></a>";						
					$content.="<br><a ".$txt_nofollow." class='product_name' title='".$nombre."' href='".$url."' target='blank_'>";				
					$content.=$nombre;
					$content.="</a><br>";																	
					if ($dto==''){
						$content.="<span class='price'>".number_format($precio_final,2,',','.')." ".$moneda_base."</span><br>";
					}else{
						if ($ofertas!=1){
							$content.="<span class='offer'>".number_format($precio_final, 2, ',', '')." ".$moneda_base."</span><span class='price'>".number_format($precio_con_final,2,',','.')." ".$moneda_base."</span> <span class='dto'>(- ".$txt_dto.")</span><br>";
						}else{
							$content.="<span class='price'>".number_format($precio_final,2,',','.')." ".$moneda_base."</span><br>";
						}
					}
					$content.="</li>";				
					$i++;				
				}	
				$content.="</ul><p class='important'><a href='https://imacreste.com' target='_blank'>Creado por imacreste</a></p>";
			}else{
				$content="";
			}			
											
			echo $content;	
			echo $after_widget;
		}
	}		
	
	class imacPrestashop_cat_widget_ws extends WP_Widget{
		function __construct(){
			$options = array(
				'classname' => 'imacPrestashop_class',
				'description' => 'Mostrar productos prestashop por categoría.'
			);
			parent::__construct('imacPrestashop_widget_webservice_cat','Prestashop categoría WS',$options);
		}
	
	  function form($instance){			
			$imacPrestashop_options=get_option('imacPrestashop_options');						
			$defaults= array(
				'title' => 'Productos',
				'idioma' => '0',
				'show_idioma' => '',
				'id_categoria' => ''
			);
			$instance=wp_parse_args((array) $instance, $defaults);

			$id_categoria=$instance['id_categoria'];
			$title=$instance['title'];
			$idioma=$instance['idioma'];
			$iva=$instance['iva'];
			$show_idioma=$instance['show_idioma'];
			?>			
				<p><?php _e("Título:", "imacPrestashop");?> <input type="text" class="widefat" name="<?php echo $this->get_field_name('title')?>" value="<?php echo esc_attr($title)?>" /></p>				
				<p><?php _e("Id categoría:", "imacPrestashop");?> <input type="text" class="widefat" name="<?php echo $this->get_field_name('id_categoria')?>" value="<?php echo esc_attr($id_categoria)?>" /></p>
				<p><?php _e("ID idioma:", "imacPrestashop");?> <input type="text" class="widefat" name="<?php echo $this->get_field_name('idioma')?>" value="<?php echo esc_attr($idioma)?>" /></p>
				<p><?php _e("IVA:", "imacPrestashop");?> <input type="text" class="widefat" name="<?php echo $this->get_field_name('iva')?>" value="<?php echo esc_attr($iva)?>" /></p>
				<p><?php _e("Mostrar idioma en la URL (1 => SI, En blanco => NO):", "imacPrestashop");?> <input type="text" class="widefat" name="<?php echo $this->get_field_name('show_idioma')?>" value="<?php echo esc_attr($show_idioma)?>" /></p>				
			<?php
		}

		function update($new_instance,$old_instance){
			global $file_prefix;
	    if ( function_exists( 'wp_cache_clean_cache' ) ) wp_cache_clean_cache( $file_prefix );	   
			$instance=$old_instance;			
			$instance['id_categoria']=sanitize_text_field($new_instance['id_categoria']);
			$instance['title']=sanitize_text_field($new_instance['title']);
			$instance['idioma']=sanitize_text_field($new_instance['idioma']);
			$instance['iva']=sanitize_text_field($new_instance['iva']);
			$instance['show_idioma']=sanitize_text_field($new_instance['show_idioma']);						
			return $instance;
		}
		
		function widget($args, $instance){
			extract($args);			
			echo $before_widget;								
			if ( function_exists( 'wp_cache_clean_cache' ) ) wp_cache_clean_cache( $file_prefix );		
			
			$imacprestashop_options=get_option('imacprestashop_options');		
			$id_categoria=(!empty($instance['id_categoria']) ? $instance['id_categoria'] : 6);
			$title=$instance['title'];			
			$id_idioma=(!empty($instance['idioma']) ? $instance['idioma'] : 0);
			$iva=(!empty($instance['iva']) ? $instance['iva'] : 0);
			$show_idioma=(!empty($instance['show_idioma']) ? $instance['show_idioma'] : '');						
						
			$desencriptar_pass=imacprestashop_encriptacion::desencriptar($imacprestashop_options['option_pass_ws']);							
			$db_pass=$desencriptar_pass;								
			$url_tienda=esc_attr($imacprestashop_options['option_url_ws']);    					
			$categoria_id=$id_categoria;			
			$url_base=(isset($imacprestashop_options['option_urlP_ws']) ? $imacprestashop_options['option_urlP_ws'] : 0);
			$nofollow=$imacprestashop_options['option_nofollow_ws'];		
			$moneda_base=(isset($imacprestashop_options['option_moneda_ws'])) ? esc_attr($imacprestashop_options['option_moneda_ws']) : '€';	
			$txt_nofollow='';
			if ($nofollow==1){
				$txt_nofollow='rel="nofollow"';	
			}
			$ofertas=$imacprestashop_options['option_ofertas'];					
			$num_productos=3;												
			if ($id_idioma=='')(int)$id_idioma=0;
			
			require_once('webservice.php');			
			$webService = new imacPWS_webservice();				
			$xml = $webService->imacPWS_getCategoria($url_tienda,$desencriptar_pass,$categoria_id);						
			$resources = $xml['category'];	
			$productos_categoria=$resources['associations']['products'];
			$productos='';
			$cont=0;
			foreach ($productos_categoria as $p_cat)		
			{	
				$productos.=$p_cat['id']."|";
				$cont++;
				if ($num_productos==$cont)break;
			}				
			$xml = $webService->imacPWS_getProducts($url_tienda,$desencriptar_pass,$productos);	
				
			$resources = $xml['products'];						
			$content='<ul class="short-products2">';
			if (isset($resources))
			{		
				$i = 1;						
				foreach ($resources as $resource)		
				{		
					if ($id_idioma==0){					
						$nombre=$resource['name'];
						$enlace=$resource['link_rewrite'];
						$id_category_default=$resource['id_category_default'];					
						$name_category=$webService->imacPWS_getCategoria($url_tienda,$desencriptar_pass,$id_category_default);					
						$name_category=$name_category['category']['link_rewrite'];
						$tax=$resource['id_tax_rules_group'];
					}else{
						$cod_idioma=$id_idioma-1;
						$nombre=$resource['name'][$cod_idioma]['value'];
						$enlace=$resource['link_rewrite'][$cod_idioma]['value'];
						$id_category_default=$resource['id_category_default']['value']; 										
						$name_category=$webService->imacPWS_getCategoria($url_tienda,$desencriptar_pass,$id_category_default);					
						$name_category=$name_category['category']['link_rewrite'][$cod_idioma]['value'];		
						$tax=$resource['id_tax_rules_group'];			
					}									
					$tax=$resource['id_tax_rules_group'];								
					$precio_redondeado=round((float)$resource['price'],2);		
					if ($tax==2){
						//$tax_rate = (float)$webService->imacPWS_getTax($url_tienda,$desencriptar_pass,$tax);												
						$precio_iva=round((($precio_redondeado*5.5)/100),2);
						$precio_final=$precio_redondeado+$precio_iva;												
					}else{
						if ($tax==1){
							//$tax_rate = (float)$webService->imacPWS_getTax($url_tienda,$desencriptar_pass,$tax);												
							$precio_iva=round((($precio_redondeado*20)/100),2);
							$precio_final=$precio_redondeado+$precio_iva;												
						}else{
							if ($tax==142){							
								$precio_iva=round((($precio_redondeado*21)/100),2);							
								$precio_final=$precio_redondeado+$precio_iva;
							}else{
								$precio_final=$precio_redondeado;
							}
						}	
					}									
									
					$stock=$webService->imacPWS_getStock($url_tienda,$desencriptar_pass,$resource['associations']['stock_availables'][0]['id']);								
					$datos=array();
					$datos=$webService->imacPWS_getOffer($url_tienda,$desencriptar_pass,$resource['id']);
					if ((isset($datos)) && ($datos!='')){
						$dto=(float)$datos[1];
						$dto_type=$datos[0];				
						$hasta=$datos[2];	
					}else{
						$dto=0;
						$dto_type=0;
					}			
					switch($url_base){
						case 0:
							$ver_idioma='';
							if ($show_idioma!='')$ver_idioma='/'.$webService->imacPWS_getLanguage($url_tienda,$desencriptar_pass,$id_idioma);
							$url=$url_tienda.$ver_idioma.'/'.$resource['id'].'-'.$enlace.'.html';
						break;
						case 1:
							$ver_idioma='';
							if ($show_idioma!='')$ver_idioma='/'.$webService->imacPWS_getLanguage($url_tienda,$desencriptar_pass,$id_idioma);
							$url=$url_tienda.$ver_idioma.'/'.$enlace.'-'.$resource['id'].'.html';
						break;
						case 2:
							$ver_idioma='';
							if ($show_idioma!='')$ver_idioma='/'.$webService->imacPWS_getLanguage($url_tienda,$desencriptar_pass,$id_idioma);
							$url=$url_tienda.$ver_idioma.'/'.$name_category.'/'.$resource['id'].'-'.$enlace.'.html';														
						break;
						case 3:
							$ver_idioma='';
							if ($show_idioma!='')$ver_idioma='/'.$webService->imacPWS_getLanguage($url_tienda,$desencriptar_pass,$id_idioma);
							$url=$url_tienda.$ver_idioma.'/'.$name_category.'/'.$enlace.'-'.$resource['id'].'.html';												
						break;
						case 4:
						$ver_idioma='';
						if ($show_idioma!='')$ver_idioma='/'.$webService->imacPWS_getLanguage($url_tienda,$desencriptar_pass,$id_idioma);
						$url=$url_tienda.$ver_idioma.'/'.$enlace.'.html';												
					break;
					case 5:
						$ver_idioma='';
						if ($show_idioma!='')$ver_idioma='/'.$webService->imacPWS_getLanguage($url_tienda,$desencriptar_pass,$id_idioma);
						$url=$url_tienda.$ver_idioma.'/'.$name_category.'/'.$enlace.'.html';												
					break;
					}
					if ($dto_type=='percentage'){
						$total_dto=0;
						if ($dto!=''){					
							$precio_dto=($precio_final*$dto);
							$precio_con_final=($precio_final-$precio_dto);
							$txt_dto=number_format($dto*100, 2, ',', '')." %";
							$txt_fecha="";if ($hasta!='')$txt_fecha="<br>".$hasta;
						}
					}else{
						$total_dto=0;
						if ($dto!=''){
							$precio_con_final=($precio_final-$dto);
							$txt_dto=number_format($dto, 2, ',', '')." ".$moneda_base;
							$txt_fecha="";if ($hasta!='')$txt_fecha="<br>".$hasta;
						}
					}									
					$resto = ($i % 3);										
					$content.="<li>";					
					$content.="<a ".$txt_nofollow." title='".$nombre."' href='".$url."' target='blank_'>";																					
					$content.="<img style='border-color:#F90' src='data:image/jpg;base64,".$webService->imacPWS_getImage($url_tienda,$desencriptar_pass,$resource['id'],$resource['id_default_image'],$desencriptar_pass)."' alt='".$nombre."' /></a>";						
					$content.="<br><a ".$txt_nofollow." class='product_name' title='".$nombre."' href='".$url."' target='blank_'>";				
					$content.=$nombre;
					$content.="</a><br>";																	
					if ($dto==''){
						$content.="<span class='price'>".number_format($precio_final,2,',','.')." ".$moneda_base."</span><br>";									
					}else{
						if ($ofertas!=1){
							$content.="<span class='offer'>".number_format($precio_final, 2, ',', '')." ".$moneda_base."</span><span class='price'>".number_format($precio_con_final,2,',','.')." ".$moneda_base."</span> <span class='dto'>(- ".$txt_dto.")</span><br>";
						}else{
							$content.="<span class='price'>".number_format($precio_final,2,',','.')." ".$moneda_base."</span><br>";									
						}
					}
					$content.="</li>";				
					$i++;				
				}	
					$content.="</ul><p class='important'><a href='https://imacreste.com' target='_blank'>Creado por imacreste/a></p>";
				
			}else{
				$content="";
			}										
											
			echo $content;	
			echo $after_widget;
		}
	}	
	

	/* BDs */
	function imacprestashop_help_fc(){			
	global $wpdb;		
	?>
		<div class="wrap">								
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab" href="?page=imacprestashop"><?php _e("Configuración de Prestashop WebService", "imacprestashop");?></a>
				<a class="nav-tab nav-tab-active" href="?page=imacprestashop_help"><?php _e("Ayuda WebService", "imacprestashop");?></a>
				<!--
				<a class="nav-tab" href="?page=imacprestashopbd"><?php _e("Configuración de Prestashop base de Datos", "imacprestashop");?></a>
				<a class="nav-tab" href="?page=imacPrestashopbd_help"><?php _e("Ayuda base de Datos", "imacprestashop");?></a>
				-->
			</h2>
			<div class="wrap imacreste-alerts">				
				<div class="imacreste-container imacreste-container__alert">
					<p class="donativo"><a><?php _e("Este plugin es gratuito y por eso incluye un enlace.", "imacprestashop");?></a></p>
					<p class="donativo"><a><?php _e("El objetivo del mismo es permitir que siga siendo así obteniendo beneficio de mi trabajo.", "imacprestashop");?></a></p>
					<p class="donativo"><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=P9DG5TCRGDYAW&lc=ES&item_name=imacreste&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHostedGuest" target="_blank"><?php _e("Realizar un donativo.", "imacprestashop");?></a></p>										
					<h3><?php _e("Seguridad", "imacprestashop");?></h3>
					<div id="imacreste-alerts">
						<p><?php _e("<strong>La contraseña se guarda encriptada en la base de datos</strong>.","imacprestashop");?></p>
						<p><?php _e("<strong>Imacreste no se hace responsable de un mal uso en la activación de WEBSERVICE y sobre todo de checkear casillas en la configuración de permisos de Prestashop como:<br>
							 Modificar (PUT), Añadir (POST), Borrar (DELETE)<br>
							 Para este plugin ningún servicio tiene que tener checkeadas esas casillas</strong>.","imacprestashop");?>
						</p>
					</div>
					<br>
					<h3><?php _e("Activar WEBSERVICE", "imacprestashop");?></h3>
					<div id="imacreste-alerts">
						<p><?php _e("Los servicios web (webservice), son formas de conectar nuestra tienda con otras plataformas. <br>En este caso, <strong>al dar solo acceso lectura, es 100% seguro</strong>... lo único que podrán es enlazarte (si les concedes acceso con tu contraseña), y eso estaría genial, ¿No?", "imacprestashop");?></p>
						<p><?php _e("Para poder conectarnos con Prestashop necesitamos que actives el servicio de WEBSERVICE desde el ADMIN de Prestashop. <br><strong>Para activarlo sigue estos pasos</strong>:", "imacprestashop");?></p>						
						<ol>
							<li><?php _e("Entra dentro de tu administrador Prestashop.", "imacprestashop");?></li>
							<li><?php _e("En el menú lateral vete a: Parámetros Avanzados -> Webservice.", "imacprestashop");?></li>
							<li><?php _e("En configuración Activa el servicio Web y, solo para algunos servidores, Activa modo CGI para PHP (Primero prueba sin checkearlo), y guarda.", "imacprestashop");?></li>
							<li><?php _e("En esa misma página arriba verás un botón que pone: Añadir una nueva clase webservice, entra dentro.", "imacprestashop");?></li>
							<li><?php _e("Pincha en el botón generar clave.", "imacprestashop");?></li>
							<li>
								<?php _e("Y en permisos tienes que activar solo la columna de VER (GET) para los siguientes recursos:", "imacprestashop");?>
								<ol>																		
									<li><?php _e("categories.", "imacprestashop");?></li>								
									<li><?php _e("languages.", "imacprestashop");?></li>
									<li><?php _e("images.", "imacprestashop");?></li>
									<li><?php _e("price_ranges.", "imacprestashop");?></li>
									<li><?php _e("products.", "imacprestashop");?></li>
									<li><?php _e("specific_prices.", "imacprestashop");?></li>
									<li><?php _e("stock_availables.", "imacprestashop");?></li>
									<li><?php _e("taxes.", "imacprestashop");?></li>		
									<li><?php _e("tax_rules.", "imacprestashop");?></li>		
									<li><?php _e("tax_rule_groups.", "imacprestashop");?></li>		
								</ol>	
							</li>							
						</ol>
						<br>
						<img src="<?php echo plugins_url('images/configuracion_imacprestashopws.png',__FILE__);?>" width="95%" height="95%" />
					</div>
					<br>
					<div id="imacreste-alerts">						
						<h3><?php _e("Configuración plugin", "imacprestashop");?></h3>						
						<ol>							
							<li><?php _e("<strong>Url de la tienda Prestashop:</strong> En este caso es necesario que sea con http, www, etc. para evitar errores copia la URL de la web. Ejemplos: https://miweb.com, https://tienda.miweb.com o http://www.miweb.com/mitienda", "imacprestashop");?></li>
							<li><?php _e("<strong>Contraseña webservice:</strong> Es la contraseña que se genera al activar webservice en Prestashop, desde el Admin de Prestashop -> Parámetros avanzados -> webservice => Añadir una nueva clase webservice. Ten cuidado con no poner espacios en blanco delante y detrás de la clave.", "imacprestashop");?></li>														
							<li><?php _e("<strong>ID idioma:</strong> Tiene que ser un valor numérico. Este ID lo puedes ver desde tu Prestashop admin -> Internacional (Prestashop 1.7) -> Localización -> Idiomas. Y se usará por defecto a la hora de mostrar el nombre del producto. IMPORTANTE: Si solo usas un idioma el valor debe ser 0. ATENTO: Prestashop por defecto instala 4 idiomas (Español, Catalán, Gallego y Euskera), que no los veas en tu web no quiere decir que no estén instalados.", "imacprestashop");?>
							</li>
							<li><?php _e("<strong>Mostrar idioma en la URL</strong>: ¿Por defecto es necesario mostrar en la URL el idioma? Si es que si pon un 1 en este campo, sino dejalo en blanco. 
							Ejemplos:
							<br>1. http://probak.es/prestashop/ca/women/2-9-brown-bear-printed-sweater.html
							<br>1. http://probak.es/prestashop/women/2-9-brown-bear-printed-sweater.html	", "imacprestashop");?>
							</li>
							<li><?php _e("<strong>Productos base</strong>: Introduce ID productos (Desde el admin de Prestashop -> Catalogo -> Productos puedes verlos) que se mostrarán por defecto. Separados por ,. Ejemplo: 1,2,1234,73,45678,503.", "imacprestashop");?>
							</li>
							<li><?php _e("<strong>Categoría base</strong>: Introduce ID de la categoría que se usará por defecto. Ejemplo: 1.", "imacprestashop");?></li>
							<li><?php _e("<strong>Enlaces Nofollow:</strong> Si esta seleccionada la casilla, los enlaces serán nofollow, es un atributo SEO. <a href='https://support.google.com/webmasters/answer/96569?hl=es' target='_blank'>Más Información</a>.", "imacprestashop");?></li>							
							<li><?php _e("<strong>Ocultar Ofertas:</strong> Si esta seleccionada la casilla, No se visualizarán las ofertas, solo el precio de los productos.", "imacPrestashop");?></li>
							<li><?php _e("<strong>URLs de producto:</strong> Las URLS de Prestashop se pueden configurar desde:
										<br>parámetros de la tienda -> Tráfico y SEO -> Ruta a los productos. 
										<br>o en versiones de Prestashop antiguas en: Preferencias -> SEO + URLs -> Ruta a los productos. 
										<br><br>Esto permite múltiples combinaciones. Nos hemos basado en 2 alternativas
										<br>1º) La que viene por defecto basada en: id-nombre_producto.html (2-blusa.html)
										<br>2º) nombre_producto-id.html (blusa-2.html)
										<br>3º) category/ID-name_product.html (ropa/2-blusa.html)
										<br>4º) category/name_product-ID.html (ropa/blusa-2.html)
										<br>5º) name_product.html (blusa.html - SIN ID)
										<br>6º) category/name_product.html (ropa/blusa.html - SIN ID)
										<br>Si abres un producto puedes verlo en la URL, ¿Dónde se pone el ID producto? Antes o después del nombre del producto..", "imacprestashop");?>
							</li>
						</ol>
						<p><?php _e("Antes de usar las funcionalidades prueba la conexión pulsando el botón: Prueba la Conexión (Si haces cambios guarda primero). Si todo está correcto debajo de los botones se mostrarán tus productos:", "imacprestashop");?></p>																		
						<br><img src="<?php echo plugins_url('images/probando_conexion.jpg',__FILE__);?>" width="85%" height="85%" />
					</div>
					<br>
					<h3><?php _e("Funcionamiento en las entradas, páginas o widgets", "imacprestashop");?></h3>
					<div id="imacreste-alerts">						
						<p>	
							<ol>								
								<li>
									<?php _e("Dentro de las entradas, páginas o Widgets podemos poner un <strong>grupo de productos</strong>:", "imacprestashop");?>
									<strong><pre>[imacprestashop_productos_ws productos='2,8' idioma='0' iva='21' show_idioma='1']</pre></strong>
									<ul>										
										<li><strong>productos</strong> => <?php _e("Son los ids de los productos, puedes verlos desde tu Prestashop en Catalogo -> Productos. Por defecto se coge la establecida en la configuración. Cada id_producto se debe separar con una coma.", "imacprestashop");?></li>
										<li><strong>idioma</strong> => <?php _e("Es el ID del idioma. Por defecto se coge la establecida en la configuración. Se usa para extraer los productos en el idioma indicado.", "imacprestashop");?></li>
										<li><strong>show_idioma</strong> => <?php _e("Permite añadir el código de idioma en la URL del enlace. Sólo admite el valor 1 o en blanco show_idioma=''", "imacprestashop");?></li>
										<li><strong>IVA</strong> => <?php _e("Permite añadir IVA", "imacprestashop");?></li>
									</ul>
									<br>
									<?php _e("El código mínimo para que funcione es:", "imacprestashop");?>
									<strong><pre>[imacprestashop_productos_ws]</pre></strong>									
									<ul>										
										<li><?php _e("En este caso el idioma y productos se cogerán los establecidos en la configuración.", "imacprestashop");?></li>										
									</ul>																		
								</li>	
								<li>
									<?php _e("Dentro de las entradas, páginas o Widgets podemos poner un <strong>grupo de productos de una categoría</strong> en concreto:", "imacprestashop");?>
									<strong><pre>[imacprestashop_categorias_ws]</pre></strong>
									<?php _e("Pudiendo añadir las siguientes variables:", "imacprestashop");?>
									<ul>										
										<li><strong>categoria_id</strong> => <?php _e("Es el id de la categoría de la que se extraerán los productos. Por defecto = 1.", "imacprestashop");?></li>
										<li><strong>idioma</strong> => <?php _e("Es el ID del idioma. Por defecto se coge la establecida en la configuración. Se usa para extraer los productos en el idioma indicado.", "imacprestashop");?></li>
										<li><strong>num_productos</strong> => <?php _e("Es el número de productos que se extraerán de la categoría.", "imacprestashop");?></li>
										<li><strong>show_idioma</strong> => <?php _e("Permite añadir el código de idioma en la URL del enlace. Sólo admite el valor 1 o en blanco show_idioma=''", "imacprestashop");?></li>
										<li><strong>IVA</strong> => <?php _e("Permite añadir IVA", "imacprestashop");?></li>
									</ul>
								</li>	
								<strong><pre>[imacprestashop_categorias_ws idioma='0' categoria_id='47' iva='21' num_productos='9'  show_idioma='']</pre></strong>
								<li>
									<?php _e("Se pueden arrastrar dos widgets en el sidebar. <br>Entrando en Apariencia -> Widgets, puedes ver dos bloques. 1 llamado 'prestashop_webservice' y el 2 'prestashop categorías', que puedes arrastrar a los cuadros de la derecha.", "imacprestashop");?>
								</li>							
							</ol>
						</p>
					</div>						
				</div>			
			</div>
		</div>
	<?php
	}
	
	function imacprestashopbd_fc(){				
	?>
		<script type="text/javascript">						
			jQuery(document).ready(function($) {
				$('.probar_conexion').click(function(){
					$('#probando_bd').show();
				});		
			});		
		</script>
		<div class="wrap">								
			<h2 class="nav-tab-wrapper">				
				<a class="nav-tab" href="?page=imacprestashop"><?php _e("Configuración de Prestashop WebService", "imacprestashop");?></a>
				<a class="nav-tab" href="?page=imacprestashop_help"><?php _e("Ayuda WebService", "imacprestashop");?></a>				
				<a class="nav-tab nav-tab-active" href="?page=imacprestashopbd"><?php _e("Configuración de Prestashop base de Datos", "imacprestashop");?></a>
				<a class="nav-tab" href="?page=imacPrestashopbd_help"><?php _e("Ayuda base de Datos", "imacprestashop");?></a>				
			</h2>
			<?php 
					settings_fields('imacPrestashop-grupo-config');
					$imacPrestashop_options=get_option('imacPrestashop_options');
					
					if (isset($_POST['option_localhost'])){
						check_admin_referer('guardar_imac_settings','imacPrestashop_guardar_settings');
						$imacPrestashop_options['option_localhost']=imacPrestashop_encriptacion::encriptar($_POST['option_localhost']);
						$imacPrestashop_options['option_name']=imacPrestashop_encriptacion::encriptar($_POST['option_name']);
						$imacPrestashop_options['option_user']=imacPrestashop_encriptacion::encriptar($_POST['option_user']);
						$imacPrestashop_options['option_pass']=imacPrestashop_encriptacion::encriptar($_POST['option_pass']);
						$imacPrestashop_options['option_prefijo']=imacPrestashop_encriptacion::encriptar($_POST['option_prefijo']);
						$imacPrestashop_options['option_url']=sanitize_text_field($_POST['option_url']);
						$imacPrestashop_options['option_idioma']=sanitize_text_field($_POST['option_idioma']);
						$imacPrestashop_options['option_categoria']=sanitize_text_field($_POST['option_categoria']);
						$imacPrestashop_options['option_urlP']=sanitize_text_field($_POST['option_urlP']);
						$imacPrestashop_options['option_nofollow']=0;
						if (isset($_POST['option_nofollow']))$imacPrestashop_options['option_nofollow']=sanitize_text_field($_POST['option_nofollow']);
						$imacPrestashop_options['option_ofertas']=0;
						if (isset($_POST['option_ofertas']))$imacPrestashop_options['option_ofertas']=sanitize_text_field($_POST['option_ofertas']);											
						update_option( 'imacPrestashop_options', $imacPrestashop_options );						
					}			
			?>
			<form method="post" action="options-general.php?page=imacprestashopbd">
				<?php wp_nonce_field('guardar_imac_settings','imacPrestashop_guardar_settings');?>
				<p><br><em class="clave"><?php _e("Los datos de base de datos se guardan encriptados. (Servidor, Nombre BD, Usuario, Contraseña y Prefijo). <br>Si no sabes cómo cambiarlo, contacta con tu webmaster o si prefieres que te asesoremos puedes mandarnos un <a href='mailto:imacreste@gmail.com'>mail</a> o entrando en la web <a href='https://imacreste.com' target='_blank'>https://imacreste.com</a>.<br>Los productos fuera de stock y los no activos no se muestran.", "imacPrestashop");?></em></p>	
				<table class="form-table">					
					<tr valign="top">
						<th scope="row">
							<?php _e("Servidor BD:", "imacPrestashop");?>
						</th>
						<td>							
							<input type="text" name="option_localhost" required value="<?php echo (isset($imacPrestashop_options['option_localhost'])) ? imacPrestashop_encriptacion::desencriptar($imacPrestashop_options['option_localhost']) : '';?>" />	
							<em><?php _e("<br>Es el servidor de acceso a la base de datos.<br>Puedes probar con localhost o con el dominio de la web sin http. Ejemplo: miweb.com", "imacPrestashop");?></em>						
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<?php _e("Nombre BD:", "imacPrestashop");?> 
						</th>
						<td>							
							<input type="text" name="option_name" required value="<?php echo (isset($imacPrestashop_options['option_name'])) ? imacPrestashop_encriptacion::desencriptar($imacPrestashop_options['option_name']) : '';?>" />		
							<em><?php _e("<br>Es el nombre de la base de datos.", "imacPrestashop");?></em>						
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<?php _e("Usuario BD:", "imacPrestashop");?>
						</th>
						<td>							
							<input type="text" name="option_user" required value="<?php echo (isset($imacPrestashop_options['option_user'])) ? imacPrestashop_encriptacion::desencriptar($imacPrestashop_options['option_user']) : '';?>" />	
							<em><?php _e("<br>Es el usuario de acceso a la base de datos.", "imacPrestashop");?></em>							
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<?php _e("Contraseña BD:", "imacPrestashop");?>
						</th>
						<td>
							<input type="text" name="option_pass" required value="<?php echo (isset($imacPrestashop_options['option_pass'])) ? imacPrestashop_encriptacion::desencriptar($imacPrestashop_options['option_pass']) : '';?>" />			
							<em><?php _e("<br>Es la contraseña de acceso a la base de datos.", "imacPrestashop");?></em>					
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<?php _e("Url de la tienda:","imacPrestashop");?>
						</th>
						<td>							
							<input type="text" name="option_url" required value="<?php echo (isset($imacPrestashop_options['option_url'])) ? esc_attr($imacPrestashop_options['option_url']) : '';?>" />
							<em><?php _e("<br>Incluir http o https, ejemplo: https://imacreste.com/ o http://www.orainbai.es/, y la barra al final.", "imacPrestashop");?></em>								
						</td>
					</tr>	
					<tr valign="top">
						<th scope="row">
							<?php _e("Prefijo:","imacPrestashop");?>
						</th>
						<td>							
							<input type="text" name="option_prefijo" required value="<?php echo (isset($imacPrestashop_options['option_prefijo'])) ?  imacPrestashop_encriptacion::desencriptar($imacPrestashop_options['option_prefijo']) : '';?>" />
							<em><?php _e("<br>Es el prefijo de las tablas de prestashop. Si tienes acceso a la base de datos fíjate que todas empiezan igual.", "imacPrestashop");?></em>								
						</td>
					</tr>			
					<tr valign="top">
						<th scope="row">
							<?php _e("ID categoría con productos:", "imacPrestashop");?>
						</th>
						<td>							
							<input type="number" name="option_categoria" required value="<?php echo (isset($imacPrestashop_options['option_categoria'])) ? esc_attr($imacPrestashop_options['option_categoria']) : 1;?>" />	
							<em><?php _e("<br>Este campo se usara por defecto, si no se introduce una categoría en el shortcode", "imacPrestashop");?></em>						
						</td>
					</tr>		
					<tr valign="top">
						<th scope="row">
							<?php _e("ID idioma:", "imacPrestashop");?>
						</th>
						<td>							
							<input type="number" name="option_idioma" required value="<?php echo (isset($imacPrestashop_options['option_idioma'])) ? esc_attr($imacPrestashop_options['option_idioma']) : 1;?>" />	
							<em><?php _e("<br>Este campo se usara por defecto, si no se introduce un idioma se usara el id_idioma=1", "imacPrestashop");?></em>						
						</td>
					</tr>	
					<tr valign="top">
						<th scope="row">
							<?php _e("Enlaces Nofollow:", "imacPrestashop");?>
						</th>
						<td>							
							<input type="checkbox" name="option_nofollow" value="1" <?php checked( $imacPrestashop_options['option_nofollow'], '1' ); ?> />	
							<em><?php _e("<br>Si esta seleccionada la casilla, los enlaces serán nofollow, es un atributo SEO. <a href='https://support.google.com/webmasters/answer/96569?hl=es' target='_blank'>Más Información</a>.", "imacPrestashop");?></em>						
						</td>
					</tr>	
					<tr valign="top">
						<th scope="row">
							<?php _e("Ocultar Ofertas:", "imacPrestashop");?>
						</th>
						<td>							
							<input type="checkbox" name="option_ofertas" value="1" <?php checked( $imacPrestashop_options['option_ofertas'], '1' ); ?> />	
							<em><?php _e("<br>Si esta seleccionada la casilla, No se visualizarán las ofertas, solo el precio de los productos.", "imacPrestashop");?></em>						
						</td>
					</tr>						
					<tr valign="top">
						<th scope="row">
							<?php _e("URLs de producto:", "imacPrestashop");?>
						</th>
						<td>																					
							<select name="option_urlP">
								<option value='0' <?php selected($imacPrestashop_options['option_urlP'],0);?>><?php _e("ID-name_product.html", "imacPrestashop");?></option>
								<option value='1' <?php selected($imacPrestashop_options['option_urlP'],1);?>><?php _e("name_product-ID.html", "imacPrestashop");?></option>
							</select>
							<em><?php _e("<br>Las URLS de Prestashop se pueden configurar desde:<br> parámetros de la tienda -> Tráfico y SEO -> Ruta a los productos. <br>Esto permite múltiples combinaciones. Nos hemos basado en <strong>2 alternativas</strong><br>1º) por defecto, basada en: <strong>id-nombre_producto.html (2-blusa.html)</strong><br> 2º) <strong>nombre_producto-id.html (blusa-2.html)</strong><br><em>Si abres un producto puedes verlo en la URL, Dónde se pone el ID producto? Antes o después del nombre del producto.</em>", "imacPrestashop");?></em>
						</td>
					</tr>									 
				</table>
				<p class="submit"><input type="submit" class="button-primary" value="Guardar" /> &nbsp; <a class="button-primary probar_conexion"><?php _e("Prueba la Conexión (Si haces cambios guarda primero)", "imacPrestashop");?></a></p>
				<div id="probando_bd" style="display:none;">
					<?php												
						$desencriptar_host=imacPrestashop_encriptacion::desencriptar($imacPrestashop_options['option_localhost']);						
						$desencriptar_nombre=imacPrestashop_encriptacion::desencriptar($imacPrestashop_options['option_name']);						
						$desencriptar_user=imacPrestashop_encriptacion::desencriptar($imacPrestashop_options['option_user']);												
						$desencriptar_pass=imacPrestashop_encriptacion::desencriptar($imacPrestashop_options['option_pass']);		
						$desencriptar_prefijo=imacPrestashop_encriptacion::desencriptar($imacPrestashop_options['option_prefijo']);									
										
						$db_host=$desencriptar_host;						
						$db_nombre=$desencriptar_nombre;						
						$db_user=$desencriptar_user;						
						$db_pass=$desencriptar_pass;						
						$prefijo=$desencriptar_prefijo;						
						$url_tienda=esc_attr($imacPrestashop_options['option_url']);    								
						$categoria_base=esc_attr($imacPrestashop_options['option_categoria']);									
						$idioma_base=esc_attr($imacPrestashop_options['option_idioma']);
						$url_base=(isset($imacPrestashop_options['option_urlP']) ? $imacPrestashop_options['option_urlP'] : 0);
						$nofollow=$imacPrestashop_options['option_nofollow'];
						$ofertas=$imacPrestashop_options['option_ofertas'];								
						$txt_nofollow='';
						if ($nofollow==1){
							$txt_nofollow='rel="nofollow"';	
						}						
						$link=mysqli_connect($db_host, $db_user, $db_pass, $db_nombre);									
						if (!$link) {
						    _e("<strong>No ha sido posible conectarse con prestashop:</strong> <br><ul style='margin-left:20px;'><li>1º) Confirma que los datos de arriba son correctos.</li><li>2º) Contacta con tu webmaster y confirma estos datos.</li><li>3º) Confirma con tu Webmaster o Hosting que su firewall permita conectarse con un hosting externo.</li></ul>", "imacPrestashop");
						   	_e("Revisa el siguiente error por si te da alguna pista:<br>", "imacPrestashop");
						    _e("- errno de depuración: ", "imacPrestashop");
						    echo mysqli_connect_errno() . PHP_EOL;
						    _e("<br>- error de depuración: ", "imacPrestashop");
						    echo mysqli_connect_error() . PHP_EOL;
						     _e("<br><br>Necesitas ayuda, mándame un <a href='mailto:imacreste@gmail.com'>mail</a>.", "imacPrestashop");
						    exit;
						}else{
								echo "Éxito: Se realizó una conexión apropiada a MySQL! Puedes ver un ejemplo:<br>" . PHP_EOL;
						}								
						mysqli_select_db($link, $db_nombre) or die("Error seleccionando la base de datos.");		
											
						$cant_productos=3;
						$id_idioma=$idioma_base;
						$categoria=$categoria_base;
												
						$images=$prefijo."image";
						$product=$prefijo."product";
						$prodyct_lang=$prefijo."product_lang";
						$category_product=$prefijo."category_product";
						$product_attribute=$prefijo."product_attribute";
						$category_lang=$prefijo."category_lang";
						$image_lang=$prefijo."image_lang";
						$tax_rule=$prefijo."tax_rule";
						$tax=$prefijo."tax";
						$specific_price=$prefijo."specific_price";
						$stock_available=$prefijo."stock_available";													
						$language=$prefijo."lang";
						
						$sqllangCount="select * from $language where active=1";	
						$consultalangCount = mysqli_query($link, $sqllangCount);		
						$total_resultadoslang = mysqli_num_rows($consultalangCount);						
						
						$sqllang="select * from $language where id_lang='$id_idioma'";			
						$consultalang = mysqli_query($link, $sqllang);				
						$registrosqllang=mysqli_fetch_array($consultalang);										
						$lang_txt='';
						if ($total_resultadoslang>1)$lang_txt=$registrosqllang['iso_code']."/";							
						$sql ="
							SELECT p.*, pa.id_product_attribute, pl.description, pl.description_short, pl.available_now, pl.available_later, pl.link_rewrite, pl.meta_description,
						 	pl.meta_keywords, pl.meta_title, pl.name, i.id_image, il.legend, 	p.price as precio_base,
						  ROUND(p.price * (COALESCE(ptx.rate, 0) / 100 + 1), 2) AS 'regular_price', ptx.rate as iva,
						  IF(pr.reduction_type = 'amount', pr.reduction, '') AS 'Discount_amount', IF(pr.reduction_type = 'percentage', pr.reduction, '') AS 'Discount_percentage', pr.reduction_tax as reduction_offer, s.quantity						  
						  FROM $category_product cp 
						  LEFT JOIN $product p ON p.id_product = cp.id_product 
						  LEFT JOIN $product_attribute pa ON (p.id_product = pa.id_product AND default_on = 1) 
						  LEFT JOIN $category_lang cl ON (p.id_category_default = cl.id_category AND cl.id_lang = '$id_idioma') 
						  LEFT JOIN $specific_price pr ON(p.id_product = pr.id_product)
						  LEFT JOIN $prodyct_lang pl ON (p.id_product = pl.id_product AND pl.id_lang = '$id_idioma') 
						  LEFT JOIN $images i ON (i.id_product = p.id_product AND i.cover = 1) 
						  LEFT JOIN $image_lang il ON (i.id_image = il.id_image AND il.id_lang = '$id_idioma')   
						  LEFT JOIN $tax_rule ptxgrp ON ptxgrp.id_tax_rules_group = p.id_tax_rules_group
							LEFT JOIN $tax ptx ON ptx.id_tax = ptxgrp.id_tax    
							LEFT JOIN $stock_available s ON (p.id_product = s.id_product)							
						  WHERE cp.id_category IN ($categoria) AND p.active = 1 AND s.quantity>0
						  GROUP BY cp.id_product
						  order by rand() LIMIT $cant_productos
						";										
					
						$consulta = mysqli_query($link, $sql);		
						$total_resultados = mysqli_num_rows($consulta);		
						$content='<ul class="short-products3">';						
						if ($total_resultados>0)
						{		
							$i = 1;
							while ($row = $consulta->fetch_object())
							{				
								$resto = ($i % 3);
								$sqlobtener="select name, link_rewrite from $prodyct_lang where id_product='$row->id_product' and id_lang='$id_idioma'";			
								$consutlasql=mysqli_query($link, $sqlobtener);
								$registrosql=mysqli_fetch_array($consutlasql);		
								$content.="<li>";
								if ($url_base==0){
									$content.="<a ".$txt_nofollow." title='".$registrosql['name']."' href='".$url_tienda.$lang_txt.$row->id_product."-".$registrosql['link_rewrite'].".html' target='blank_'>";								
								}else{
									$content.="<a ".$txt_nofollow." title='".$registrosql['name']."' href='".$url_tienda.$lang_txt.$registrosql['link_rewrite']."-".$row->id_product.".html' target='blank_'>";								
								}
								$content.="<img style='border-color:#F90' src='".$url_tienda.$row->id_image."-home_default/".$registrosql['link_rewrite'].".jpg' alt='Imagén: ".$registrosql['name']."' /></a>";
								if ($url_base==0){
									$content.="<br><a ".$txt_nofollow." class='product_name' title='".$registrosql['name']."' href='".$url_tienda.$lang_txt.$row->id_product."-".$registrosql['link_rewrite'].".html' target='blank_'>";
								}else{
									$content.="<br><a ".$txt_nofollow." class='product_name' title='".$registrosql['name']."' href='".$url_tienda.$lang_txt.$registrosql['link_rewrite']."-".$row->id_product.".html' target='blank_'>";
								}
								$content.=$registrosql['name'];								
								$content.="</a><br>";
								$precio=$row->regular_price;
								$preu = number_format($precio, 2, ',', '');	
								$discount_iva=0;					
								if ($row->reduction_offer==1){										
									$Discount_amount =$row->Discount_amount;		
									$Discount_percentage =$row->Discount_percentage;
									if ($Discount_amount!=''){		
										$preu_dto=number_format($precio-$Discount_amount, 2, ',', '');		
									}
									if ($Discount_percentage!=''){		
										$preu_dto=number_format($precio-($precio*$Discount_percentage), 2, ',', '');		
									}					
								}else{					
									$Discount_amount = $row->Discount_amount;					
									if ($Discount_amount!=''){
										$discount_iva =(($Discount_amount * 21)/100);			
										$Discount_amount=$Discount_amount+$discount_iva;				
										$preu_dto=number_format($precio-$Discount_amount, 2, ',', '');		
									}
									$Discount_percentage =$row->Discount_percentage;
									if ($Discount_percentage!=''){		
										$discount_iva =(($Discount_percentage * 21)/100);			
										$Discount_percentage=$Discount_percentage+$discount_iva;
										$preu_dto=number_format($precio-($precio*$Discount_percentage), 2, ',', '');		
									}	
								}				
								
								if ($ofertas==1){
									$content.="<span class='price'>".$preu." €</span><br>";
								}else{
									if (($Discount_amount!=0) || ($Discount_percentage!=0)){
										if ($Discount_percentage!=0){
											$content.="<span class='offer'>".$preu."</span><span class='price'>".$preu_dto." €</span> <span class='dto'>(".number_format($Discount_percentage*100, 2, ',', '')." %)</span><br>";
										}else{
											$content.="<span class='offer'>".$preu."</span><span class='price'>".$preu_dto." €</span> <span class='dto'>(- ".number_format($Discount_amount, 2, ',', '')." €)</span><br>";
										}
									}else{
										$content.="<span class='price'>".$preu." €</span><br>";
									}			
								}										
								$content.="</li>";			
								$i++;
							}	
								$content.="</ul><p class='important'><a href='https://imacreste.com' target='_blank'>Creado por imacreste</a></p>";
								
								$content.="<p>Ahora que todo funciona, puedes probar a meter estos códigos dentro de la descripción de una entrada: <strong>[imacPrestashop_productos] o [imacPrestashop_productos]</strong>. <br>Si quieres aprender a mostrar X productos de una categoría concreta o mostrar unos productos concretos <strong>visita la <a href='options-general.php?page=imacPrestashopbd_help'>ayuda</a></strong>.</p>";
							
						}else{
							$content=__("La conexión a la Base de datos sido correcta.<br> Pero no hemos encontrado resultados en esa categoría.<br>Revisa si el ID categoría, el ID idioma y el prefijo son correctos.", "imacPrestashop");
						}		
						
						mysqli_close($link);
						echo $content;						
					?>
				</div>
			</form>
		</div>
	<?php
	}	
	
	function imacprestashopbd_help_fc(){	
		global $wpdb;		
	?>
		<div class="wrap">								
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab" href="?page=imacprestashop"><?php _e("Configuración de Prestashop WebService", "imacprestashop");?></a>
				<a class="nav-tab" href="?page=imacprestashop_help"><?php _e("Ayuda WebService", "imacprestashop");?></a>				
				<a class="nav-tab" href="?page=imacprestashopbd"><?php _e("Configuración de Prestashop base de Datos", "imacprestashop");?></a>
				<a class="nav-tab nav-tab-active" href="?page=imacPrestashopbd_help"><?php _e("Ayuda base de Datos", "imacprestashop");?></a>				
			</h2>
			<div class="wrap imacreste-alerts">				
				<div class="imacreste-container imacreste-container__alert">
					<p class="donativo"><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=P9DG5TCRGDYAW&lc=ES&item_name=imacreste&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHostedGuest" target="_blank"><?php _e("Con un donativo, contribuiras a mejorar este plugin. Gracias.", "imacPrestashop");?></a></p>					
					<h3><?php _e("Seguridad:", "imacPrestashop");?></h3>
					<div id="imacreste-alerts">
						<p><?php _e("Los datos de configuración de: Servidor, Nombre BD, Usuario, Contraseña y Prefijo, <strong>se guardan encriptados</strong>.","");?></p> 													
						<?php if ($wpdb->base_prefix=="wp_"){?>
							<p><?php _e("<strong>Si el prefijo de tu Wordpress es wp_</strong>, que es la configuración por defecto, <strong>te recomendamos que lo cambies</strong>, no solo por el funcionamiento de este plugin sino porque al ser genérico es inseguro.", "imacPrestashop");?></p>
							<p><?php _e("Si no sabes cómo cambiarlo, contacta con tu webmaster o si prefieres que te asesoremos puedes mandarnos un <a href='mailto:imacreste@gmail.com'>mail</a> o entrando en la web <a href='https://imacreste.com' target='_blank'>https://imacreste.com</a>", "imacPrestashop");?></p>
						<?php }?>
					</div>
					<br>
					<h3><?php _e("Configuración:", "imacPrestashop");?></h3>
					<div id="imacreste-alerts">
						<p><?php _e("Para poder conectarnos con Prestashop necesitamos acceder al servidor y a la base de datos, para lo que se necesita:", "imacPrestashop");?></p>
						<ol>
							<li><?php _e("<strong>Servidor BD:</strong> Si la instalación de Prestashop es la misma que este blog, puedes probar a poner: lcoalhost. Si no puedes probar poniendo la url de la web del Prestashop pero sin el inicio http ni las www. Ejemplo: miweb.com", "imacPrestashop");?></li>
							<li><?php _e("<strong>Nombre BD:</strong> Todo Prestashop necesita una base de datos, y es a la que nos conectaremos para extraer la información de los productos. Si no la conoces pregunta a tu webmaster o hosting.", "imacPrestashop");?></li>
							<li><?php _e("<strong>Usuario BD:</strong> Es el usuario de la base de datos, también es necesario en cualquier instalación Prestashop. Si no la conoces pregunta a tu webmaster o hosting.", "imacPrestashop");?></li>
							<li><?php _e("<strong>Contraseña BD:</strong> Es la contraseña del usuario de la base de datos, también es necesario en cualquier instalación Prestashop. Si no la conoces pregunta a tu webmaster o hosting.", "imacPrestashop");?></li>
							<li><?php _e("<strong>Url de la tienda:</strong> En este caso es necesario que sea con http, www, etc. para evitar errores copia la URL de la web. Es necesario que al final tenga un /. Ejemplo: https://miweb.com/ o Ejemplo: http://www.miweb.com/", "imacPrestashop");?></li>
							<li><?php _e("<strong>Prefijo:</strong> Es el prefijo de la base de datos.", "imacPrestashop");?></li>
							<li><?php _e("<strong>ID categoría con productos:</strong> Es la categoría en la que se buscarán productos. Asegúrate de que el ID exista en tu Prestashop. Puedes verlo desde el menú catálogo -> categorías.", "imacPrestashop");?></li>
							<li><?php _e("<strong>ID idioma:</strong> Es el ID del idioma de Prestashop. Puedes verlo desde localización -> idiomas. Se usa para sacar los productos y URL en el idioma requerido.", "imacPrestashop");?></li>
							<li><?php _e("<strong>Enlaces Nofollow:</strong> Si esta seleccionada la casilla, los enlaces serán nofollow, es un atributo SEO. <a href='https://support.google.com/webmasters/answer/96569?hl=es' target='_blank'>Más Información</a>.", "imacPrestashop");?></li>
							<li><?php _e("<strong>Ocultar Ofertas:</strong> Si esta seleccionada la casilla, No se visualizarán las ofertas, solo el precio de los productos.", "imacPrestashop");?></li>
							<li><?php _e("<strong>URLs de producto:</strong> Las URLS de Prestashop se pueden configurar desde:
										<br>parámetros de la tienda -> Tráfico y SEO -> Ruta a los productos. 
										<br>Esto permite múltiples combinaciones. Nos hemos basado en 2 alternativas
										<br>1º) La que viene por defecto basada en: id-nombre_producto.html (2-blusa.html)
										<br>2º) nombre_producto-id.html (blusa-2.html)
										<br>Si abres un producto puedes verlo en la URL, Dónde se pone el ID producto? Antes o después del nombre del producto.", "imacPrestashop");?>
							</li>
						</ol>
						<p><?php _e("Antes de usar las funcionalidades prueba la conexión pulsando el botón. Si todo esta correcto debajo de los botones se mostrarán como ejemplo 3 productos:", "imacPrestashop");?></p>												
						<em class="clave"><?php _e("Si Wordpress y Prestashop están en diferentes servidores, es posible que tengas que solicitar al servidor de Wordpress que te habilite en su firewall un acceso a la IP del prestashop.", "imacPrestashop");?></em>
						<br><img src="<?php echo plugins_url('images/probando_conexion.jpg',__FILE__);?>" width="85%" height="85%" />
					</div>
					<br>
					<h3><?php _e("Funcionamiento:", "imacPrestashop");?></h3>
					<div id="imacreste-alerts">
						<p><?php _e("En estos momentos el plugin se puede usar de 3 formas:", "imacPrestashop");?></p>
						<p>	
							<ol>
								<li>
									<?php _e("Dentro de los artículos podemos poner los <strong>productos de una categoría concreta</strong>:", "imacPrestashop");?>
									<strong><pre>[imacPrestashop_categorias cant_productos="6" categoria="1" idioma="1"]</pre></strong>
									<ul>
										<li><strong>cant_productos</strong> => <?php _e("Número de productos que se mostraran. La visualización se adapta a los formatos de pantalla pudiendo quedar en 3, 2 o 1 única columna por fila para mvls.", "imacPrestashop");?></li>
										<li><strong>categoria</strong> => <?php _e("Es el ID de la categoría. Por defecto se coge la establecida en la configuración. Los productos se extraen de esta categoría, y se muestran el número de productos indicado seleccionándolos de forma aleatoria.", "imacPrestashop");?></li>
										<li><strong>idioma</strong> => <?php _e("Es el ID del idioma. Por defecto se coge la establecida en la configuración. Se usa para extraer los productos en el idioma indicado.", "imacPrestashop");?></li>
									</ul>
									<br>
									<?php _e("El código mínimo para que funcione es:", "imacPrestashop");?>
									<strong><pre>[imacPrestashop_categorias]</pre></strong>									
									<ul>										
										<li><?php _e("En este caso el idioma se cogerá el establecido en la configuración (si se deja en blanco será 1), cant_productos = 6 de la categoría configurada.", "imacPrestashop");?></li>										
									</ul>
									<br>
									<img src="<?php echo plugins_url('images/ej1.jpg',__FILE__);?>" width="85%" height="85%" />
								</li>
								<li>
									<?php _e("Dentro de los artículos podemos poner un <strong>grupo de productos</strong>:", "imacPrestashop");?>
									<strong><pre>[imacPrestashop_productos productos="1,2,3,4,5,6" idioma="1"]</pre></strong>
									<ul>										
										<li><strong>productos</strong> => <?php _e("Son los ids de los productos, puedes verlos desde tu Prestashop en Catalogo -> Productos. Cada id_producto se debe separar con una coma.", "imacPrestashop");?></li>
										<li><strong>idioma</strong> => <?php _e("Es el ID del idioma. Por defecto se coge la establecida en la configuración. Se usa para extraer los productos en el idioma indicado.", "imacPrestashop");?></li>
									</ul>
									<br>
									<?php _e("El código mínimo para que funcione es:", "imacPrestashop");?>
									<strong><pre>[imacPrestashop_productos]</pre></strong>									
									<ul>										
										<li><?php _e("En este caso el idioma se cogerá el establecido en la configuración (si se deja en blanco será 1), y solo mostrará el id_producto = 1.", "imacPrestashop");?></li>										
									</ul>
									<br>
									<img src="<?php echo plugins_url('images/ej2.jpg',__FILE__);?>" width="75%" height="75%" />
								</li>
								<li>
									<?php _e("Se puede arrastrar un <strong>widget</strong> en el sidebar.", "imacPrestashop");?><br><br>
									<ul>										
										<li><?php _e("Entrando en Apariencia -> Widgets, puedes ver un bloque llamado: prestashop, que puedes arrastrar a los cuadros de la derecha.", "imacPrestashop");?></li>										
										<li><strong>Título</strong> => <?php _e("No es obligatorio. Sería la cabecera..", "imacPrestashop");?></li>
										<li><strong>ID Categoría productos</strong> => <?php _e("No es obligatorio pero si recomendable. Es la categoría de la que se extraen productos. Puedes verlo desde el menú catálogo -> categorías.", "imacPrestashop");?></li>
										<li><strong>Número productos</strong> => <?php _e("No es obligatorio pero si recomendable. Es el número de productos que se visualizarán, si en la categoría hay más de los indicados, se mostrarán de forma aleatoria.", "imacPrestashop");?></li>
										<li><strong>ID Idioma</strong> => <?php _e("No es obligatorio pero si recomendable. Indica el id_idioma en caso de tener varios. Puedes verlo desde localización -> idiomas. por defecto = 1", "imacPrestashop");?></li>
									</ul>
									<br>
									<img src="<?php echo plugins_url('images/ej3.jpg',__FILE__);?>" width="85%" height="85%" />
								</li>
							</ol>
						</p>
					</div>	
					<h3><?php _e("Funcionalidades implementadas:", "imacPrestashop");?></h3>
					<div id="imacreste-alerts">
						<p><?php _e("Listado de funcionalidades testeadas en algunas versiones de Prestashop: ", "imacPrestashop");?></p>
						<ol>
							<li>
								<?php _e("Se muestran productos estándar.", "imacPrestashop");?>
							</li>
							<li>
								<?php _e("Se muestran precios base y con ofertas en %.", "imacPrestashop");?>
							</li>
							<li>
								<?php _e("En el filtro de categoría y widget no salen productos sin stock y productos no activos.", "imacPrestashop");?>
							</li>
							<li>
								<?php _e("Es posible que otras combinaciones se muestre sin problemas pero no han sido testeadas", "imacPrestashop");?>
							</li>							
						</ol>
						<p><?php _e("Aunque subiremos mejoras, cualquier sugerencia será bienvenida. Necesitaremos el máximo de información posible como: Versión de Prestashop y descripción completa del problema detectado. Contacto: <a href='mailto:imacreste@gmail.com'>mail</a> o entrando en la web <a href='https://imacreste.com' target='_blank'>https://imacreste.com</a>", "imacPrestashop");?></p>
						<div class="red"></div>
					</div>				
				</div>			
			</div>
		</div>
	<?php
	}	
	
	function imacPrestashop_sanitize($input){
		
		return $input;
	}	
		
	function imacPrestashop_shortcode_categorias($atts) {		    		
		$imacPrestashop_options=get_option('imacPrestashop_options');						  
		
		$desencriptar_host=imacPrestashop_encriptacion::desencriptar($imacPrestashop_options['option_localhost']);						
		$desencriptar_nombre=imacPrestashop_encriptacion::desencriptar($imacPrestashop_options['option_name']);						
		$desencriptar_user=imacPrestashop_encriptacion::desencriptar($imacPrestashop_options['option_user']);												
		$desencriptar_pass=imacPrestashop_encriptacion::desencriptar($imacPrestashop_options['option_pass']);		
		$desencriptar_prefijo=imacPrestashop_encriptacion::desencriptar($imacPrestashop_options['option_prefijo']);
						
		$db_host=$desencriptar_host;						
		$db_nombre=$desencriptar_nombre;						
		$db_user=$desencriptar_user;						
		$db_pass=$desencriptar_pass;						
		$prefijo=$desencriptar_prefijo;			
		$url_tienda=esc_attr($imacPrestashop_options['option_url']);    		
		$categoria_base=esc_attr($imacPrestashop_options['option_categoria']);
		$idioma_base=esc_attr($imacPrestashop_options['option_idioma']);
		$url_base=(isset($imacPrestashop_options['option_urlP']) ? $imacPrestashop_options['option_urlP'] : 0);
		$nofollow=$imacPrestashop_options['option_nofollow'];
		$ofertas=$imacPrestashop_options['option_ofertas'];	
		$txt_nofollow='';
		if ($nofollow==1){
			$txt_nofollow='rel="nofollow"';	
		}
		
		$link=mysqli_connect($db_host, $db_user, $db_pass, $db_nombre);			
		if (!$link) {		    
		    exit;
		}			
		mysqli_select_db($link, $db_nombre) or die("Error seleccionando la base de datos.");			
		$datos = shortcode_atts( array(   
        'cant_productos' => '6',
        'idioma' => $idioma_base,
        'categoria' => $categoria_base,
   	), $atts );
   				
		$cant_productos=$datos["cant_productos"];
		$id_idioma=$datos["idioma"];
		$categoria=$datos["categoria"];
		
		$images=$prefijo."image";
		$product=$prefijo."product";
		$prodyct_lang=$prefijo."product_lang";
		$category_product=$prefijo."category_product";
		$product_attribute=$prefijo."product_attribute";
		$category_lang=$prefijo."category_lang";
		$image_lang=$prefijo."image_lang";
		$tax_rule=$prefijo."tax_rule";
		$tax=$prefijo."tax";
		$specific_price=$prefijo."specific_price";
		$stock_available=$prefijo."stock_available";
		$language=$prefijo."lang";
						
		$sqllangCount="select * from $language where active=1";	
		$consultalangCount = mysqli_query($link, $sqllangCount);		
		$total_resultadoslang = mysqli_num_rows($consultalangCount);						
		
		$sqllang="select * from $language where id_lang='$id_idioma'";			
		$consultalang = mysqli_query($link, $sqllang);				
		$registrosqllang=mysqli_fetch_array($consultalang);										
		$lang_txt='';
		if ($total_resultadoslang>1)$lang_txt=$registrosqllang['iso_code']."/";	
		
		$sql ="
			SELECT p.*, pa.id_product_attribute, pl.description, pl.description_short, pl.available_now, pl.available_later, pl.link_rewrite, pl.meta_description,
		 	pl.meta_keywords, pl.meta_title, pl.name, i.id_image, il.legend, 	p.price as precio_base,
		  ROUND(p.price * (COALESCE(ptx.rate, 0) / 100 + 1), 2) AS 'regular_price', ptx.rate as iva,
		  IF(pr.reduction_type = 'amount', pr.reduction, '') AS 'Discount_amount', IF(pr.reduction_type = 'percentage', pr.reduction, '') AS 'Discount_percentage', pr.reduction_tax as reduction_offer, s.quantity
		  FROM $category_product cp 
		  LEFT JOIN $product p ON p.id_product = cp.id_product 
		  LEFT JOIN $product_attribute pa ON (p.id_product = pa.id_product AND default_on = 1) 
		  LEFT JOIN $category_lang cl ON (p.id_category_default = cl.id_category AND cl.id_lang = '$id_idioma') 
		  LEFT JOIN $specific_price pr ON(p.id_product = pr.id_product)
		  LEFT JOIN $prodyct_lang pl ON (p.id_product = pl.id_product AND pl.id_lang = '$id_idioma') 
		  LEFT JOIN $images i ON (i.id_product = p.id_product AND i.cover = 1) 
		  LEFT JOIN $image_lang il ON (i.id_image = il.id_image AND il.id_lang = '$id_idioma')   
		  LEFT JOIN $tax_rule ptxgrp ON ptxgrp.id_tax_rules_group = p.id_tax_rules_group
			LEFT JOIN $tax ptx ON ptx.id_tax = ptxgrp.id_tax        
			LEFT JOIN $stock_available s ON (p.id_product = s.id_product) 
		  WHERE cp.id_category IN ($categoria) AND p.active = 1 AND s.quantity>0
		  GROUP BY cp.id_product
		  order by rand() LIMIT $cant_productos
		";														
		$consulta = mysqli_query($link, $sql);				
		$total_resultados = mysqli_num_rows($consulta);		
					
		
		$content='<ul class="short-products">';
		if ($total_resultados>0)
		{		
			$i = 1;
			while ($row = $consulta->fetch_object())
			{				
				$resto = ($i % 3);
				$sqlobtener="select name, link_rewrite from $prodyct_lang where id_product='$row->id_product' and id_lang='$id_idioma'";			
				$consutlasql=mysqli_query($link, $sqlobtener);
				$registrosql=mysqli_fetch_array($consutlasql);	
				
				$content.="<li>";
				if ($url_base==0){
					$content.="<a ".$txt_nofollow." title='".$registrosql['name']."' href='".$url_tienda.$lang_txt.$row->id_product."-".$registrosql['link_rewrite'].".html' target='blank_'>";								
				}else{
					$content.="<a ".$txt_nofollow." title='".$registrosql['name']."' href='".$url_tienda.$lang_txt.$registrosql['link_rewrite']."-".$row->id_product.".html' target='blank_'>";								
				}
				$content.="<img style='border-color:#F90' src='".$url_tienda.$row->id_image."-home_default/".$registrosql['link_rewrite'].".jpg' alt='Imagén: ".$registrosql['name']."' /></a>";		
				if ($url_base==0){
					$content.="<br><a ".$txt_nofollow." class='product_name' title='".$registrosql['name']."' href='".$url_tienda.$lang_txt.$row->id_product."-".$registrosql['link_rewrite'].".html' target='blank_'>";
				}else{
					$content.="<br><a ".$txt_nofollow." class='product_name' title='".$registrosql['name']."' href='".$url_tienda.$lang_txt.$registrosql['link_rewrite']."-".$row->id_product.".html' target='blank_'>";
				}				
				$content.=$registrosql['name'];								
				$content.="</a><br>";
								
				$precio=$row->regular_price;
				$preu = number_format($precio, 2, ',', '');	
				$preu_dto=0;
				$discount_iva=0;
				$Discount_percentage=0;										
				if ($row->reduction_offer==1){										
					$Discount_amount =$row->discount_amount;					
					$Discount_percentage =$row->discount_percentage;					
					if ($Discount_amount!=''){ 		
						$preu_dto=number_format($precio-$Discount_amount, 2, ',', '');		
					}else{
						$Discount_amount=0;
					}
					if ($Discount_percentage!=''){		
						$preu_dto=number_format($precio-($precio*$Discount_percentage), 2, ',', '');		
					}else{
						$Discount_percentage=0;
					}					
				}else{					
					$Discount_amount = $row->Discount_amount;					
					if ($Discount_amount!=''){
						$discount_iva =(($Discount_amount * 21)/100);			
						$Discount_amount=$Discount_amount+$discount_iva;				
						$preu_dto=number_format($precio-$Discount_amount, 2, ',', '');		
					}else{
						$Discount_amount=0;
					}
					$Discount_percentage=$row->Discount_percentage;					
					if ($Discount_percentage!=''){								
						$discount_iva =(($Discount_percentage * 21)/100);			
						$Discount_percentage=$Discount_percentage+$discount_iva;
						$preu_dto=number_format($precio-($precio*$Discount_percentage), 2, ',', '');		
					}else{
						$Discount_percentage=0;
					}						
				}								
				if ($ofertas==1){
					$content.="<span class='price'>".$preu." €</span><br>";
				}else{
					if (($Discount_amount!=0) || ($Discount_percentage!=0)){
						if ($Discount_percentage!=0){							
							$content.="<span class='offer'>".$preu."</span><span class='price'>".$preu_dto." €</span> <span class='dto'>(".number_format($Discount_percentage*100, 2, ',', '')." %)</span><br>";
						}else{
							echo $Discount_amount;
							$content.="<span class='offer'>".$preu."</span><span class='price'>".$preu_dto." €</span> <span class='dto'>(- ".number_format($Discount_amount, 2, ',', '')." €)</span><br>";
						}
					}else{
						$content.="<span class='price'>".$preu." €</span><br>";
					}	
				}				
				$content.="</li>";							
				
				$i++;
			}					
				$content.="</ul><p class='important'><a href='https://imacreste.com' target='_blank'>Creado por imacreste</a></p>";
			
		}else{
			$content="";
		}			
		return $content;
	}
	add_shortcode('imacPrestashop_categorias', 'imacPrestashop_shortcode_categorias');
	
	function imacPrestashop_shortcode_productos($atts) {		        
		$imacPrestashop_options=get_option('imacPrestashop_options');	
		
		$desencriptar_host=imacPrestashop_encriptacion::desencriptar($imacPrestashop_options['option_localhost']);						
		$desencriptar_nombre=imacPrestashop_encriptacion::desencriptar($imacPrestashop_options['option_name']);						
		$desencriptar_user=imacPrestashop_encriptacion::desencriptar($imacPrestashop_options['option_user']);												
		$desencriptar_pass=imacPrestashop_encriptacion::desencriptar($imacPrestashop_options['option_pass']);		
		$desencriptar_prefijo=imacPrestashop_encriptacion::desencriptar($imacPrestashop_options['option_prefijo']);
						
		$db_host=$desencriptar_host;						
		$db_nombre=$desencriptar_nombre;						
		$db_user=$desencriptar_user;						
		$db_pass=$desencriptar_pass;						
		$prefijo=$desencriptar_prefijo;			
		$url_tienda=esc_attr($imacPrestashop_options['option_url']);    		
		$categoria_base=esc_attr($imacPrestashop_options['option_categoria']);
		$idioma_base=esc_attr($imacPrestashop_options['option_idioma']);
		$url_base=(isset($imacPrestashop_options['option_urlP']) ? $imacPrestashop_options['option_urlP'] : 0);
		$nofollow=$imacPrestashop_options['option_nofollow'];
		$ofertas=$imacPrestashop_options['option_ofertas'];	
		$txt_nofollow='';
		if ($nofollow==1){
			$txt_nofollow='rel="nofollow"';	
		}
		
		$link=mysqli_connect($db_host, $db_user, $db_pass, $db_nombre);			
		if (!$link) {		   
		    exit;
		}			
		mysqli_select_db($link, $db_nombre) or die("Error seleccionando la base de datos.");	
		
		$datos = shortcode_atts( array(           
        'cant_productos' => '6',
        'idioma' => $idioma_base,
        'productos' => '1',
   	), $atts );
						
		$cant_productos=$datos["cant_productos"];
		$id_idioma=$datos["idioma"];
		$productos=$datos["productos"];
		
		$images=$prefijo."image";
		$product=$prefijo."product";
		$prodyct_lang=$prefijo."product_lang";
		$category_product=$prefijo."category_product";
		$product_attribute=$prefijo."product_attribute";
		$category_lang=$prefijo."category_lang";
		$image_lang=$prefijo."image_lang";
		$tax_rule=$prefijo."tax_rule";
		$tax=$prefijo."tax";
		$specific_price=$prefijo."specific_price";
		$language=$prefijo."lang";
						
		$sqllangCount="select * from $language where active=1";	
		$consultalangCount = mysqli_query($link, $sqllangCount);		
		$total_resultadoslang = mysqli_num_rows($consultalangCount);						
		
		$sqllang="select * from $language where id_lang='$id_idioma'";			
		$consultalang = mysqli_query($link, $sqllang);				
		$registrosqllang=mysqli_fetch_array($consultalang);										
		$lang_txt='';
		if ($total_resultadoslang>1)$lang_txt=$registrosqllang['iso_code']."/";	
		
		$sql ="
			SELECT p.*, pa.id_product_attribute, pl.description, pl.description_short, pl.available_now, pl.available_later, pl.link_rewrite, pl.meta_description,
		 	pl.meta_keywords, pl.meta_title, pl.name, i.id_image, il.legend, 	p.price as precio_base,
		  ROUND(p.price * (COALESCE(ptx.rate, 0) / 100 + 1), 2) AS 'regular_price', ptx.rate as iva,
		  IF(pr.reduction_type = 'amount', pr.reduction, '') AS 'Discount_amount', IF(pr.reduction_type = 'percentage', pr.reduction, '') AS 'Discount_percentage', pr.reduction_tax as reduction_offer
		  FROM $category_product cp 
		  LEFT JOIN $product p ON p.id_product = cp.id_product 
		  LEFT JOIN $product_attribute pa ON (p.id_product = pa.id_product AND default_on = 1) 
		  LEFT JOIN $category_lang cl ON (p.id_category_default = cl.id_category AND cl.id_lang = '$id_idioma') 
		  LEFT JOIN $specific_price pr ON(p.id_product = pr.id_product)
		  LEFT JOIN $prodyct_lang pl ON (p.id_product = pl.id_product AND pl.id_lang = '$id_idioma') 
		  LEFT JOIN $images i ON (i.id_product = p.id_product AND i.cover = 1) 
		  LEFT JOIN $image_lang il ON (i.id_image = il.id_image AND il.id_lang = '$id_idioma')   
		  LEFT JOIN $tax_rule ptxgrp ON ptxgrp.id_tax_rules_group = p.id_tax_rules_group
			LEFT JOIN $tax ptx ON ptx.id_tax = ptxgrp.id_tax        
		  WHERE p.id_product IN ($productos) AND p.active = 1
		  GROUP BY cp.id_product
		  order by rand()
		";										
		$consulta = mysqli_query($link, $sql);		
		$total_resultados = mysqli_num_rows($consulta);		
		$content='<ul class="short-products">';
		if ($total_resultados>0)
		{		
			$i = 1;
			while ($row = $consulta->fetch_object())
			{				
				$resto = ($i % 3);
				$sqlobtener="select name, link_rewrite from $prodyct_lang where id_product='$row->id_product' and id_lang='$id_idioma'";			
				$consutlasql=mysqli_query($link, $sqlobtener);
				$registrosql=mysqli_fetch_array($consutlasql);			
				
				$content.="<li>";
				if ($url_base==0){
					$content.="<a ".$txt_nofollow." title='".$registrosql['name']."' href='".$url_tienda.$lang_txt.$row->id_product."-".$registrosql['link_rewrite'].".html' target='blank_'>";								
				}else{
					$content.="<a ".$txt_nofollow." title='".$registrosql['name']."' href='".$url_tienda.$lang_txt.$registrosql['link_rewrite']."-".$row->id_product.".html' target='blank_'>";								
				}
				$content.="<img style='border-color:#F90' src='".$url_tienda.$row->id_image."-home_default/".$registrosql['link_rewrite'].".jpg' alt='Imagén: ".$registrosql['name']."' /></a>";		
				if ($url_base==0){
					$content.="<br><a ".$txt_nofollow." class='product_name' title='".$registrosql['name']."' href='".$url_tienda.$lang_txt.$row->id_product."-".$registrosql['link_rewrite'].".html' target='blank_'>";
				}else{
					$content.="<br><a ".$txt_nofollow." class='product_name' title='".$registrosql['name']."' href='".$url_tienda.$lang_txt.$registrosql['link_rewrite']."-".$row->id_product.".html' target='blank_'>";
				}
				$content.=$registrosql['name'];								
				$content.="</a><br>";
								
				$precio=$row->regular_price;
				$preu = number_format($precio, 2, ',', '');	
				$discount_iva=0;					
				if ($row->reduction_offer==1){										
					$Discount_amount =$row->Discount_amount;		
					$Discount_percentage =$row->Discount_percentage;
					if ($Discount_amount!=''){		
						$preu_dto=number_format($precio-$Discount_amount, 2, ',', '');		
					}
					if ($Discount_percentage!=''){		
						$preu_dto=number_format($precio-($precio*$Discount_percentage), 2, ',', '');		
					}					
				}else{					
					$Discount_amount = $row->Discount_amount;					
					if ($Discount_amount!=''){
						$discount_iva =(($Discount_amount * 21)/100);			
						$Discount_amount=$Discount_amount+$discount_iva;				
						$preu_dto=number_format($precio-$Discount_amount, 2, ',', '');		
					}else{
						$Discount_amount=0;
					}
					$Discount_percentage =$row->Discount_percentage;
					if ($Discount_percentage!=''){		
						$discount_iva =(($Discount_percentage * 21)/100);			
						$Discount_percentage=$Discount_percentage+$discount_iva;
						$preu_dto=number_format($precio-($precio*$Discount_percentage), 2, ',', '');		
					}else{
						$Discount_percentage=0;
					}	
				}					
				if ($ofertas==1){
					$content.="<span class='price'>".$preu." €</span><br>";
				}else{
					if (($Discount_amount!=0) || ($Discount_percentage!=0)){
						if ($Discount_percentage!=0){
							$content.="<span class='offer'>".$preu."</span><span class='price'>".$preu_dto." €</span> <span class='dto'>(".number_format($Discount_percentage*100, 2, ',', '')." %)</span><br>";
						}else{
							$content.="<span class='offer'>".$preu."</span><span class='price'>".$preu_dto." €</span> <span class='dto'> (- ".number_format($Discount_amount, 2, ',', '')." €)</span><br>";
						}
					}else{
						$content.="<span class='price'>".$preu." €</span><br>";
					}
				}					
				$content.="</li>";
				
				$i++;
			}	
				$content.="</ul><p class='important'><a href='https://imacreste.com' target='_blank'>Creado por imacreste</a></p>";
			
		}else{
			$content="";
		}			
	
		return $content;
	}
	add_shortcode('imacPrestashop_productos', 'imacPrestashop_shortcode_productos');
	
	add_action('widgets_init','imacPrestashop_widget');
	function imacPrestashop_widget() {	
		register_widget( 'imacPrestashop_1_widget' );	
	}	
	
	class imacPrestashop_1_widget extends WP_Widget{
		function __construct(){
			$options = array(
				'classname' => 'imacPrestashop_class',
				'description' => 'Mostrar productos prestashop.'
			);
			parent::__construct('imacPrestashop_widget','Prestashop - Vía Base de datos',$options);
		}
		
		function form($instance){			
			$imacPrestashop_options=get_option('imacPrestashop_options');			
			
			$defaults= array(
				'title' => 'Productos',
				'idioma' => '1',
				'category' => esc_attr($imacPrestashop_options['option_categoria']),
				'n_products' => ''
			);
			$instance=wp_parse_args((array) $instance, $defaults);
						
			$category=$instance['category'];
			$n_products=$instance['n_products'];
			$title=$instance['title'];
			$idioma=$instance['idioma'];
			?>			
				<p><?php _e("Título:", "imacPrestashop");?> <input type="text" class="widefat" name="<?php echo $this->get_field_name('title')?>" value="<?php echo esc_attr($title)?>" /></p>
				<p><?php _e("ID categoría Prestashop:", "imacPrestashop");?> <input type="text" class="widefat" name="<?php echo $this->get_field_name('category')?>" value="<?php echo esc_attr($category)?>" /></p>
				<p><?php _e("Número productos:", "imacPrestashop");?> <input type="text" class="widefat" name="<?php echo $this->get_field_name('n_products')?>" value="<?php echo esc_attr($n_products)?>" /></p>
				<p><?php _e("ID idioma:", "imacPrestashop");?> <input type="text" class="widefat" name="<?php echo $this->get_field_name('idioma')?>" value="<?php echo esc_attr($idioma)?>" /></p>
			<?php
		}
		
		function update($new_instance,$old_instance){
			global $file_prefix;
	    if ( function_exists( 'wp_cache_clean_cache' ) ) wp_cache_clean_cache( $file_prefix );
	    
			$instance=$old_instance;
			$instance['category']=sanitize_text_field($new_instance['category']);
			$instance['n_products']=sanitize_text_field($new_instance['n_products']);
			$instance['title']=sanitize_text_field($new_instance['title']);
			$instance['idioma']=sanitize_text_field($new_instance['idioma']);
			
			return $instance;
		}
		
		function widget($args, $instance){
			extract($args);
			
			echo $before_widget;					
			global $file_prefix;
	    if ( function_exists( 'wp_cache_clean_cache' ) ) wp_cache_clean_cache( $file_prefix );
													
			$imacPrestashop_options=get_option('imacPrestashop_options');	
						
			$categoria_base=(!empty($instance['category']) ? $instance['category'] : esc_attr($imacPrestashop_options['option_categoria']));
			$n_products=(!empty($instance['n_products']) ? $instance['n_products'] : 6);
			$title=$instance['title'];			
			$idioma=(!empty($instance['idioma']) ? $instance['idioma'] : 1);
			
			$desencriptar_host=imacPrestashop_encriptacion::desencriptar($imacPrestashop_options['option_localhost']);						
			$desencriptar_nombre=imacPrestashop_encriptacion::desencriptar($imacPrestashop_options['option_name']);						
			$desencriptar_user=imacPrestashop_encriptacion::desencriptar($imacPrestashop_options['option_user']);												
			$desencriptar_pass=imacPrestashop_encriptacion::desencriptar($imacPrestashop_options['option_pass']);		
			$desencriptar_prefijo=imacPrestashop_encriptacion::desencriptar($imacPrestashop_options['option_prefijo']);
							
			$db_host=$desencriptar_host;						
			$db_nombre=$desencriptar_nombre;						
			$db_user=$desencriptar_user;						
			$db_pass=$desencriptar_pass;						
			$prefijo=$desencriptar_prefijo;			
			$url_tienda=esc_attr($imacPrestashop_options['option_url']);   
			$url_base=(isset($imacPrestashop_options['option_urlP']) ? $imacPrestashop_options['option_urlP'] : 0); 	
			$nofollow=$imacPrestashop_options['option_nofollow'];
			$ofertas=$imacPrestashop_options['option_ofertas'];	
			$txt_nofollow='';
			if ($nofollow==1){
				$txt_nofollow='rel="nofollow"';	
			}
													
			$link=mysqli_connect($db_host, $db_user, $db_pass, $db_nombre);			
			if (!$link) {			    
			    exit;
			}			
			mysqli_select_db($link, $db_nombre) or die("Error seleccionando la base de datos.");
						
			if ( !empty($title) ){echo $before_title.esc_html($title).$after_title;}
			
			$cant_productos=$n_products; 
			$id_idioma=$idioma;
			$categoria=$categoria_base;
		
			$images=$prefijo."image";
			$product=$prefijo."product";
			$prodyct_lang=$prefijo."product_lang";
			$category_product=$prefijo."category_product";
			$product_attribute=$prefijo."product_attribute";
			$category_lang=$prefijo."category_lang";
			$image_lang=$prefijo."image_lang";
			$tax_rule=$prefijo."tax_rule";
			$tax=$prefijo."tax";
			$specific_price=$prefijo."specific_price";	
			$stock_available=$prefijo."stock_available";					
			$language=$prefijo."lang";
						
			$sqllangCount="select * from $language where active=1";	
			$consultalangCount = mysqli_query($link, $sqllangCount);		
			$total_resultadoslang = mysqli_num_rows($consultalangCount);						
			
			$sqllang="select * from $language where id_lang='$id_idioma'";			
			$consultalang = mysqli_query($link, $sqllang);				
			$registrosqllang=mysqli_fetch_array($consultalang);										
			$lang_txt='';			
			if ($total_resultadoslang>1)$lang_txt=$registrosqllang['iso_code']."/";	
				
			$sql ="
			SELECT p.*, pa.id_product_attribute, pl.description, pl.description_short, pl.available_now, pl.available_later, pl.link_rewrite, pl.meta_description,
		 	pl.meta_keywords, pl.meta_title, pl.name, i.id_image, il.legend, 	p.price as precio_base,
		  ROUND(p.price * (COALESCE(ptx.rate, 0) / 100 + 1), 2) AS 'regular_price', ptx.rate as iva,
		  IF(pr.reduction_type = 'amount', pr.reduction, '') AS 'Discount_amount', IF(pr.reduction_type = 'percentage', pr.reduction, '') AS 'Discount_percentage', pr.reduction_tax as reduction_offer, s.quantity
		  FROM $category_product cp 
		  LEFT JOIN $product p ON p.id_product = cp.id_product 
		  LEFT JOIN $product_attribute pa ON (p.id_product = pa.id_product AND default_on = 1) 
		  LEFT JOIN $category_lang cl ON (p.id_category_default = cl.id_category AND cl.id_lang = '$id_idioma') 
		  LEFT JOIN $specific_price pr ON(p.id_product = pr.id_product)
		  LEFT JOIN $prodyct_lang pl ON (p.id_product = pl.id_product AND pl.id_lang = '$id_idioma') 
		  LEFT JOIN $images i ON (i.id_product = p.id_product AND i.cover = 1) 
		  LEFT JOIN $image_lang il ON (i.id_image = il.id_image AND il.id_lang = '$id_idioma')   
		  LEFT JOIN $tax_rule ptxgrp ON ptxgrp.id_tax_rules_group = p.id_tax_rules_group
			LEFT JOIN $tax ptx ON ptx.id_tax = ptxgrp.id_tax 
			LEFT JOIN $stock_available s ON (p.id_product = s.id_product)       
		  WHERE cp.id_category IN ($categoria) AND p.active = 1 AND s.quantity>0
		  GROUP BY cp.id_product
		  order by rand() LIMIT $cant_productos
			";								
			$consulta = mysqli_query($link, $sql);		
			$total_resultados = mysqli_num_rows($consulta);		
			$content='<ul class="short-products2 widget-title">';
			if ($total_resultados>0)
			{		
				$i = 1;
				while ($row = $consulta->fetch_object())
				{				
					$resto = ($i % 3);
					$sqlobtener="select name, link_rewrite from $prodyct_lang where id_product='$row->id_product' and id_lang='$id_idioma'";			
					$consutlasql=mysqli_query($link, $sqlobtener);
					$registrosql=mysqli_fetch_array($consutlasql);			
									
					$content.="<li>";
					if ($url_base==0){
						$content.="<a ".$txt_nofollow." title='".$registrosql['name']."' href='".$url_tienda.$lang_txt.$row->id_product."-".$registrosql['link_rewrite'].".html' target='blank_'>";								
					}else{
						$content.="<a ".$txt_nofollow." title='".$registrosql['name']."' href='".$url_tienda.$lang_txt.$registrosql['link_rewrite']."-".$row->id_product.".html' target='blank_'>";								
					}
					$content.="<img style='border-color:#F90' src='".$url_tienda.$row->id_image."-home_default/".$registrosql['link_rewrite'].".jpg' alt='Imagén: ".$registrosql['name']."' /></a>";		
					if ($url_base==0){
						$content.="<br><a ".$txt_nofollow." class='product_name' title='".$registrosql['name']."' href='".$url_tienda.$lang_txt.$row->id_product."-".$registrosql['link_rewrite'].".html' target='blank_'>";
					}else{
						$content.="<br><a ".$txt_nofollow." class='product_name' title='".$registrosql['name']."' href='".$url_tienda.$lang_txt.$registrosql['link_rewrite']."-".$row->id_product.".html' target='blank_'>";
					}
					$content.=$registrosql['name'];								
					$content.="</a><br>";
									
					$precio=$row->regular_price;
					$preu = number_format($precio, 2, ',', '');	
					$discount_iva=0;					
					if ($row->reduction_offer==1){										
						$Discount_amount =$row->Discount_amount;		
						$Discount_percentage =$row->Discount_percentage;
						if ($Discount_amount!=''){		
							$preu_dto=number_format($precio-$Discount_amount, 2, ',', '');		
						}
						if ($Discount_percentage!=''){		
							$preu_dto=number_format($precio-($precio*$Discount_percentage), 2, ',', '');		
						}					
					}else{					
						$Discount_amount = $row->Discount_amount;					
						if ($Discount_amount!=''){
							$discount_iva =(($Discount_amount * 21)/100);			
							$Discount_amount=$Discount_amount+$discount_iva;				
							$preu_dto=number_format($precio-$Discount_amount, 2, ',', '');		
						}else{
							$Discount_amount=0;
						}
						$Discount_percentage =$row->Discount_percentage;
						if ($Discount_percentage!=''){		
							$discount_iva =(($Discount_percentage * 21)/100);			
							$Discount_percentage=$Discount_percentage+$discount_iva;
							$preu_dto=number_format($precio-($precio*$Discount_percentage), 2, ',', '');		
						}else{
							$Discount_percentage=0;
						}
					}			
					if ($ofertas==1){
						$content.="<span class='price'>".$preu." €</span><br>";
					}else{		
						if (($Discount_amount!=0) || ($Discount_percentage!=0)){
							if ($Discount_percentage!=0){
								$content.="<span class='offer'>".$preu."</span><span class='price'>".$preu_dto." €</span> <span class='dto'>(".number_format($Discount_percentage*100, 2, ',', '')." %)</span><br>";
							}else{
								$content.="<span class='offer'>".$preu."</span><span class='price'>".$preu_dto." €</span> <span class='dto'>(- ".number_format($Discount_amount, 2, ',', '')." €)</span><br>";
							}
						}else{
							$content.="<span class='price'>".$preu." €</span><br>";
						}	
					}				
					$content.="</li>";
						
					$i++;
				}	
					$content.="</ul><p class='important'><a href='https://imacreste.com' target='_blank'>Creado por imacreste</a></p>";
				
			}else{
				$content="";
			}			
		
			echo $content;
			
			echo $after_widget;
		}
	}	
	
	class imacPrestashop_encriptacion{				
		public static function encriptar($cadena){	
			global $wpdb;
			$key ='asasest&A2oeds3-asdwas23'.$wpdb->base_prefix.'Acunt#33ddasd_asextod2Dseprueba31';		
			$iv = '12as16as78as12as';			
	    $encrypted = openssl_encrypt($cadena,'AES-256-CBC',$key,0,$iv); 
	    return $encrypted; 
		} 
		public static function desencriptar($cadena){  		
			global $wpdb;
			$key ='asasest&A2oeds3-asdwas23'.$wpdb->base_prefix.'Acunt#33ddasd_asextod2Dseprueba31';	
			$iv = '12as16as78as12as';	   	
	   	$decrypted = openssl_decrypt($cadena,'AES-256-CBC',$key,0,$iv); 	
	    return $decrypted;
		}
	}
?>