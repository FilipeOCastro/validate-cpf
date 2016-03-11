<?php
/**
 * Plugin Name:       validate-cpf
 * Plugin URI:        https://github.com/FilipeOCastro/validate-cpf
 * Description:       validation CPF field.
 * Version:           1.0.1
 * Author:            Filipe Castro
 * Author URI:        http://filipecastro.com.br
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt 
 * GitHub Plugin URI: https://github.com/FilipeOCastro/validate-cpf
 */


if ( ! defined( 'ABSPATH' ) ) { die; }

//define constant
const status_code_successful = 200; //200 - successful


/**
 * validation CPF field.
 
	* @param  username.
	* @param  email.
	* @param  $validation_errors WP_Error object.
  
 */
function wp_wc_validate_cpf_field( $username, $email, $validation_errors ) {

	//valida se o cpf digitado é valido
	if ( isset( $_POST['billing_cpf'] ) && !empty( $_POST['billing_cpf'] ) ) {
		
		//validação web api		
		$response = wp_remote_get( 'http://amafresp.afresp.org.br/consulta-cpf/api/Validador/'.$_POST['billing_cpf'] );
		
		//Verifica se a requisicao obteve sucesso
		if(wp_remote_retrieve_response_code( $response ) == status_code_successful )
		{
			//armazena o corpo do response
			$body = wp_remote_retrieve_body($response );		
			//Separa o campo do valor ex> {campo:valor}			
			$cpf_valido = explode(":",$body);	
			$cpf_valido = str_replace("}","",$cpf_valido[1]);		
			
			if ($cpf_valido == "false")
			{
				$validation_errors->add( 'billing_cpf_error', 'CPF inválido!' ) ;		  
			}	
		}
		else
		{
			$validation_errors->add( 'billing_cpf_error', 'Erro ao validar o CPF !' );		  
		}		
	}
}
add_action( 'woocommerce_register_post', 'wp_wc_validate_cpf_field', 10, 3 );
/**
 * Save the CPF field.
 
	* @param  customer ID. 
 
 */
function wp_wc_save_cpf_field( $customer_id ) {
		
	if ( isset( $_POST['billing_cpf'] ) ) {
		// WordPress default last name field.
		update_user_meta( $customer_id, 'cpf', sanitize_text_field( $_POST['billing_cpf'] ) );
		// WooCommerce billing last name.
		update_user_meta( $customer_id, 'billing_cpf', sanitize_text_field( $_POST['billing_cpf'] ) );
				
		//validação web api		
		$response = wp_remote_get( 'http://amafresp.afresp.org.br/consulta-cpf/api/Associado/'.$_POST['billing_cpf'] );
		
		//Verifica se a requisicao obteve sucesso
		if(wp_remote_retrieve_response_code( $response ) == status_code_successful )
		{
			//armazena o corpo do response
			$body = wp_remote_retrieve_body($response );		
			//Separa o campo do valor ex> {campo:valor}			
			$Associado = explode(":",$body);	
			$Associado = str_replace("}","",$Associado[1]);					
			
			if ($Associado == "true")
			{
				//atualiza role 
				wp_update_user(array(
				'ID' => $customer_id,
				'role' => 'associado'
				));
			}	
		}
		else
		{
			$validation_errors->add( 'billing_cpf_error', 'Erro ao criar função do usuário!' );		  
		}	
	}
}

add_action( 'woocommerce_created_customer', 'wp_wc_save_cpf_field' );