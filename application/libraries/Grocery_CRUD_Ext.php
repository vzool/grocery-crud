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

	public function vzool_nested_crud($args, $map, $base_table, $class_ref_name, $class_function_ref_name){

		/*##########################################################*/
		/*##################### DEEP LOGIC #########################*/
		/*##########################################################*/

		$function_name = strtolower("$class_ref_name/$class_function_ref_name");

		$is_debug = false;

		$debug_stack = [];
		$debug_stack['trace'] = [];

		$current_table = $base_table;

		$base_url = current_url();

		$html_links = '';

		$is_ajax = false;

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
			'ajax_list',
		];

		$ignore = [
			"created_date",
			"modified_date",
		];

		$parent_id = null;
		$last_parent_id = null;

		$args_count = sizeof($args);

		if(strpos($base_url, 'ajax_list')){

			$is_ajax = true;
		}

		if($args){

			$ignore = array_merge($args, $ignore);
			
			$last_section = $args[$args_count - 1];
			
			if(in_array($last_section, $functions)){

				if($args_count > 1){
						
					$last_section = $args[$args_count - 2];

				}else{

					$last_section = $base_table;
				}

				if($is_debug){
					echo "<h3>IF #1</h3>";
				}

			}elseif(strpos($base_url, 'delete_file')){

				$tmp = explode("/delete_file", $base_url);
				$tmp = explode("/", $tmp[0]);

				$last_section = end($tmp);

				if($is_debug){
					echo "<h3>IF #4</h3>";
				}

			}elseif(strpos($base_url, 'edit') 	|| strpos($base_url, 'update_validation') ||
					strpos($base_url, 'update') || strpos($base_url, 'delete')){
				
				if($args_count > 2){

					$last_section = $args[$args_count - 3];
					
				}else{

					$last_section = $base_table;
				}

				if($is_debug){
					echo "<h3>IF #2</h3>";
				}

			}elseif(strpos($base_url, 'upload_file')){

				$tmp = explode("/upload_file", $base_url);
				$tmp = explode("/", $tmp[0]);

				$last_section = end($tmp);

				if($is_debug){
					echo "<h3>IF #3</h3>";
				}
			}

			$current_table = $last_section;
		}

		$uri_segments = explode("/", $base_url);

		if(is_numeric($current_table)){

			array_pop($uri_segments);
			array_pop($uri_segments);
			
			$new_url = join("/", $uri_segments);
			
			redirect($new_url);
		}

		$url_base_rule = '/index/' . $base_table;

		if (!in_array($base_table, $uri_segments)){

			if(end($uri_segments) === strtolower($class_ref_name)){

				$base_url .= $url_base_rule;

			}else{
				
				$base_url .= '/' . $base_table;
			}
			
			redirect($base_url);

		}else{

			if(end($uri_segments) === strtolower($class_ref_name) && !strpos($base_url, $url_base_rule)){
				
				$base_url .= $url_base_rule;
				
				redirect($base_url);
			}
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
						'url' => base_url($function_name . $long_url),
					];

					if(isset($map[$links_table[$k]]['link_column'])){

						$link = $map[$links_table[$k]]['link_column'];

						if(isset($f->{$link})){

							$links_ref_map[ $links_table[$k] ]['name'] = $f->{$link};
						}
					}
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

			$debug_stack['info'] = [
				'URL' => $base_url,
				'BASE_TABLE' => $base_table,
				'CURRENT_TABLE' => $current_table,
				'AJAX' => ($is_ajax ? 'YES' : 'NO'),
				'parent' => [
					'id' => $parent_id,
					'last_id' => $last_parent_id,
				],
				'args' => $args,
				'LINKS' => [
					$links_table,
					$links_ref_id,
					$links_ref_map,
					$html_links,
				],
			];

			if(isset($map[$current_table])){

				$debug_stack['info']['SUBJECT'] = $map[$current_table]['set_subject'];
			}
		}
		/* ------------------------ DEBUG SPOT ------------------------ */

		$crud = new grocery_CRUD();

		$crud->set_table($current_table);

		if(in_array($current_table, array_keys($map))){
			
			if($map[$current_table]['ref']){

				$parent_id = $args[sizeof($args) - 3];

				$col = $map[$current_table]['ref'];
				$ref_id = $args[sizeof($args) - 2];
				
				if($is_ajax){

					$ref_id = $args[sizeof($args) - 3];
				}

				$debug_stack['trace']['parent_id'] = $parent_id;
				$debug_stack['trace']['where'] = [
					$col,
					$ref_id,
				];

				$crud->where($col, $ref_id);

				$ignore []= $map[$current_table]['ref'];
			}

			// Column CallBack
			if($map[$current_table]['link_column']){

				$crud->callback_column($map[$current_table]['link_column'], function() use($base_url, $map, $current_table){

					$x = func_get_args();

					if($map[$current_table]['next_depth']){

						$url = $base_url . '/' . $x[1]->id . '/' . $map[$current_table]['next_depth'];

						$url = str_replace('ajax_list/', '', $url);

						return "<a href='$url'>{$x[0]}</a>";
						
					}else{

						return $x[0];
					}
				});
			}

			// Before Insert CallBack
			$crud->callback_before_insert(function($data) use($base_url, $map, $current_table, $crud, $parent_id){

				if($map[$current_table]['ref']){

					$data[$map[$current_table]['ref']] = $parent_id;
				}

				return $data;
			});
		}

		if(isset($map[$current_table]['set_subject'])){

			$crud->set_subject($map[$current_table]['set_subject']);
		}
		
		$crud->unset_columns($ignore);

		foreach($ignore as $f){
			$crud->change_field_type($f,'invisible');
		}
		
		$crud->current_table = $current_table;

		$crud->links = $html_links;

		/* ------------------------ DEBUG SPOT ------------------------ */
		if($is_debug){

			echo "<pre>";
			print_r($debug_stack);
			echo "</pre>";
		}
		/* ------------------------ DEBUG SPOT ------------------------ */

		/*##########################################################*/
		/*##################### DEEP LOGIC #########################*/
		/*##########################################################*/

		return $crud;
	}
}
