<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Description of Grocery_CRUD_Ext
 *  
 * @author Abdelaziz Elrashed <aeemh.sdn@gmail.com>
 * @copyright RAE Company For Environmental Services, 2016
 **/

class Grocery_CRUD_Ext {

	public function __construct(){}

	/**
	* Description of vzool_nested_crud
	*
 
		// Made to handle nested structure of tables
		// something like for Country -> City -> Municipality -> Neighborhood -> Place

		// USAGE:

		$args = func_get_args() [Static]
			
		$map = [
			"TABEL_NAME" => [
				"ref" => "FK_FIELD",
				"link_column" => "COLUMN_NAME_LINK_ASSOCIATIVE",
				"set_subject" => "SUBJECT_NAME",
				"next_depth" => "SUB_TABLE_NAME",
			],
		];

		$base_table => Start point similar to `Country`

		$function_name = strtolower(__CLASS__ .'/'. __FUNCTION__) [Static]

	*
	*/

	public function vzool_nested_crud($args, $map, $base_table, $function_name){

		/*##########################################################*/
		/*##################### DEEP LOGIC #########################*/
		/*##########################################################*/

		$is_debug = false;

		$is_ajax_list = false;

		$base_url = current_url();

		$html_links = '';

		$functions = [
			'add',
			'delete',
			'read',
			'print',
			'export',
			'insert_validation',
			'insert',
			'delete',
			'update',
			'ajax_list_info',
			// 'ajax_list',
		];

		$ignore = [
			"created_date",
			"modified_date",
		];

		$parent_id = null;

		$args_count = sizeof($args);

		if($args){

			$ignore = array_merge($args, $ignore);
			
			$last_section = $args[$args_count - 1];
			
			if(in_array($last_section, $functions)){

				if($args_count > 1){
						
					$last_section = $args[$args_count - 2];

				}else{

					$last_section = $base_table;
				}

			}elseif(strpos($base_url, 'edit') 	|| strpos($base_url, 'update_validation') ||
					strpos($base_url, 'update') || strpos($base_url, 'delete')){
				
				if($args_count > 2){

					$last_section = $args[$args_count - 3];
					
				}else{

					$last_section = $base_table;
				}
			}

			$base_table = $last_section;
		}
		
		if($base_table === 'ajax_list'){
			// $base_table = $args[sizeof($args) - 2];
			// unset($args[$args_count - 1]);
			// $args_count = sizeof($args);
			$is_ajax_list = true;
		}

		if (strpos($base_url, $base_table) === false){
			$base_url .= '/' . $base_table;
		}

		/* ------------------------ BUILD LINKS ------------------------ */

		$links_table = array();
		$links_ref_id = array();

		foreach ($args as $k => $v) {
			
			if ($k % 2 == 0) {
				
				$links_table[] = $v;

			}else {

				$links_ref_id[] = $v;
			}
		}

		if($links_table){
			unset($links_table[sizeof($links_table) - 1]);
		}

		$links_ref_map = [];

		if($links_table && $links_ref_id){

			$long_url = '';

			$last_parent_id = null;

			$ci = &get_instance();

			foreach($links_table as $k => $v){

				$query = $ci->db->get_where($links_table[$k], array('id' => $links_ref_id[$k]), 1);

				foreach($query->result() as $f){

					if(!$long_url){

						$long_url .= '/'. $links_table[$k];

					}else{

						$long_url .= '/'. $last_parent_id .'/'. $links_table[$k];
					}

					$links_ref_map[ $links_table[$k] ] = [
						'id' => $links_ref_id[$k],
						'name' => $f->name,
						'url' => base_url($function_name . $long_url),
					];
				}
				
				$last_parent_id = $links_ref_id[$k];
			}
		}

		if($links_ref_map){

			$html_links .= "<ul class='links'>";
			
			foreach($links_ref_map as $link){

				$html_links .= "<li><a href='{$link['url']}'>{$link['name']}</a></li>";
			}

			$html_links .= "</ul>";
		}

		/* ------------------------ BUILD LINKS ------------------------ */
		
		/* ------------------------ DEBUG SPOT ------------------------ */
		if($is_debug){

			$debug_stack = [
				'URL' => $base_url,
				'TABLE' => $base_table,
				'IS_AJAX' => ($is_ajax_list ? 'YES' : 'NO'),
				'args' => $args,
				'LINKS' => [
					$links_table,
					$links_ref_id,
					$links_ref_map,
					$html_links,
				],
			];

			if(isset($map[$base_table])){

				$debug_stack['SUBJECT'] = $map[$base_table]['set_subject'];
			}

			echo "<pre>";
			print_r($debug_stack);
			echo "</pre>";
		}
		/* ------------------------ DEBUG SPOT ------------------------ */

		$crud = new grocery_CRUD();

		$crud->set_table($base_table);

		if(in_array($base_table, array_keys($map))){
			
			if($map[$base_table]['ref']){

				$parent_id = $args[sizeof($args) - 3];

				$crud->where($map[$base_table]['ref'], $args[sizeof($args) - 2]);

				$ignore []= $map[$base_table]['ref'];
			}

			// Column CallBack
			if($map[$base_table]['link_column']){

				$crud->callback_column($map[$base_table]['link_column'], function() use($base_url, $map, $base_table, $is_ajax_list){

					$x = func_get_args();

					if($map[$base_table]['next_depth']){

						$url = $base_url . '/' . $x[1]->id . '/' . $map[$base_table]['next_depth'];

						/*if($is_ajax_list){
							$url = str_replace('ajax_list/', '', $url);
						}*/

						return "<a href='$url'>{$x[0]}</a>";
						
					}else{

						return $x[0];
					}
				});
			}

			// Before Insert CallBack
			$crud->callback_before_insert(function($data) use($base_url, $map, $base_table, $crud, $parent_id){

				if($map[$base_table]['ref']){

					$data[$map[$base_table]['ref']] = $parent_id;
				}

				return $data;
			});
		}

		if($map[$base_table]['set_subject']){

			$crud->set_subject($map[$base_table]['set_subject']);
		}
		
		$crud->unset_columns($ignore);

		foreach($ignore as $f){
			$crud->change_field_type($f,'invisible');
		}
		
		$crud->links = $html_links;

		/*##########################################################*/
		/*##################### DEEP LOGIC #########################*/
		/*##########################################################*/

		return $crud;
	}
}
