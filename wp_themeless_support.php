<?php
/*
Plugin Name: Wordpress themeless support
Plugin URI: 
Description: Collection of supporting functions
Version: 1.0
Author: Alkar. E. 
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// adding filesize property to acf data (hiking_route CPT)
function filter_hiking_route_json( $data, $request ) {
  $file_id = $data['acf']['attached_document']['ID'];
  if( $file_id )
 	$data['acf']['attached_document']['filesize'] = size_format( filesize( get_attached_file($file_id) ) );
  return $data;
}
add_filter( 'acf/rest_api/hiking_route/get_fields', 'filter_hiking_route_json', 10, 2 );

// hiking_poi CPT
function filter_hiking_poi_json( $data, $request ) {
	$arrs = $data['acf']['attachments'];
	if(!empty($arrs)){
		for($i = 0; $i < count($arrs); $i++){
			$path = $arrs[$i]['document'];
			$path = iconv('UTF-8', 'ISO-8859-1',$path);
			$filesize = size_format( filesize( get_attached_file( fjarrett_get_attachment_id_by_url($path) ) ) );
			$data['acf']['attachments'][$i]['filesize'] = $filesize;			
		}
	}
  return $data;
}

add_filter( 'acf/rest_api/hiking_poi/get_fields', 'filter_hiking_poi_json', 10, 2 );

// returns ID by URL
function fjarrett_get_attachment_id_by_url( $url ) {
	$parsed_url  = explode( parse_url( WP_CONTENT_URL, PHP_URL_PATH ), $url );
	$this_host = str_ireplace( 'www.', '', parse_url( home_url(), PHP_URL_HOST ) );
	$file_host = str_ireplace( 'www.', '', parse_url( $url, PHP_URL_HOST ) );
	if ( ! isset( $parsed_url[1] ) || empty( $parsed_url[1] ) || ( $this_host != $file_host ) ) {
		return;
	}

	global $wpdb;
	$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid RLIKE %s;", $parsed_url[1] ) );

	return $attachment[0];
}