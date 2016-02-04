<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Examples extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->helper('url');

		$this->load->library('grocery_CRUD');
	}

	public function _example_output($output = null)
	{
		$this->load->view('example.php',$output);
	}

	public function offices()
	{
		$output = $this->grocery_crud->render();

		$this->_example_output($output);
	}

	public function index()
	{
		$this->_example_output((object)array('output' => '' , 'js_files' => array() , 'css_files' => array()));
	}

	public function offices_management()
	{
		try{
			$crud = new grocery_CRUD();

			$crud->set_theme('datatables');
			$crud->set_table('offices');
			$crud->set_subject('Office');
			$crud->required_fields('city');
			$crud->columns('city','country','phone','addressLine1','postalCode');

			$output = $crud->render();

			$this->_example_output($output);

		}catch(Exception $e){
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}

	public function employees_management()
	{
			$crud = new grocery_CRUD();

			$crud->set_theme('datatables');
			$crud->set_table('employees');
			$crud->set_relation('officeCode','offices','city');
			$crud->display_as('officeCode','Office City');
			$crud->set_subject('Employee');

			$crud->required_fields('lastName');

			$crud->set_field_upload('file_url','assets/uploads/files');

			$output = $crud->render();

			$this->_example_output($output);
	}

	public function customers_management()
	{
			$crud = new grocery_CRUD();

			$crud->set_table('customers');
			$crud->columns('customerName','contactLastName','phone','city','country','salesRepEmployeeNumber','creditLimit');
			$crud->display_as('salesRepEmployeeNumber','from Employeer')
				 ->display_as('customerName','Name')
				 ->display_as('contactLastName','Last Name');
			$crud->set_subject('Customer');
			$crud->set_relation('salesRepEmployeeNumber','employees','lastName');

			$output = $crud->render();

			$this->_example_output($output);
	}

	public function orders_management()
	{
			$crud = new grocery_CRUD();

			$crud->set_relation('customerNumber','customers','{contactLastName} {contactFirstName}');
			$crud->display_as('customerNumber','Customer');
			$crud->set_table('orders');
			$crud->set_subject('Order');
			$crud->unset_add();
			$crud->unset_delete();

			$output = $crud->render();

			$this->_example_output($output);
	}

	public function products_management()
	{
			$crud = new grocery_CRUD();

			$crud->set_table('products');
			$crud->set_subject('Product');
			$crud->unset_columns('productDescription');
			$crud->callback_column('buyPrice',array($this,'valueToEuro'));

			$output = $crud->render();

			$this->_example_output($output);
	}

	public function valueToEuro($value, $row)
	{
		return $value.' &euro;';
	}

	public function film_management()
	{
		$crud = new grocery_CRUD();

		$crud->set_table('film');
		$crud->set_relation_n_n('actors', 'film_actor', 'actor', 'film_id', 'actor_id', 'fullname','priority');
		$crud->set_relation_n_n('category', 'film_category', 'category', 'film_id', 'category_id', 'name');
		$crud->unset_columns('special_features','description','actors');

		$crud->fields('title', 'description', 'actors' ,  'category' ,'release_year', 'rental_duration', 'rental_rate', 'length', 'replacement_cost', 'rating', 'special_features');

		$output = $crud->render();

		$this->_example_output($output);
	}

	public function film_management_twitter_bootstrap()
	{
		try{
			$crud = new grocery_CRUD();

			$crud->set_theme('twitter-bootstrap');
			$crud->set_table('film');
			$crud->set_relation_n_n('actors', 'film_actor', 'actor', 'film_id', 'actor_id', 'fullname','priority');
			$crud->set_relation_n_n('category', 'film_category', 'category', 'film_id', 'category_id', 'name');
			$crud->unset_columns('special_features','description','actors');

			$crud->fields('title', 'description', 'actors' ,  'category' ,'release_year', 'rental_duration', 'rental_rate', 'length', 'replacement_cost', 'rating', 'special_features');

			$output = $crud->render();
			$this->_example_output($output);

		}catch(Exception $e){
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}

	function multigrids()
	{
		$this->config->load('grocery_crud');
		$this->config->set_item('grocery_crud_dialog_forms',true);
		$this->config->set_item('grocery_crud_default_per_page',10);

		$output1 = $this->offices_management2();

		$output2 = $this->employees_management2();

		$output3 = $this->customers_management2();

		$js_files = $output1->js_files + $output2->js_files + $output3->js_files;
		$css_files = $output1->css_files + $output2->css_files + $output3->css_files;
		$output = "<h1>List 1</h1>".$output1->output."<h1>List 2</h1>".$output2->output."<h1>List 3</h1>".$output3->output;

		$this->_example_output((object)array(
				'js_files' => $js_files,
				'css_files' => $css_files,
				'output'	=> $output
		));
	}

	public function offices_management2()
	{
		$crud = new grocery_CRUD();
		$crud->set_table('offices');
		$crud->set_subject('Office');

		$crud->set_crud_url_path(site_url(strtolower(__CLASS__."/".__FUNCTION__)),site_url(strtolower(__CLASS__."/multigrids")));

		$output = $crud->render();

		if($crud->getState() != 'list') {
			$this->_example_output($output);
		} else {
			return $output;
		}
	}

	public function employees_management2()
	{
		$crud = new grocery_CRUD();

		$crud->set_theme('datatables');
		$crud->set_table('employees');
		$crud->set_relation('officeCode','offices','city');
		$crud->display_as('officeCode','Office City');
		$crud->set_subject('Employee');

		$crud->required_fields('lastName');

		$crud->set_field_upload('file_url','assets/uploads/files');

		$crud->set_crud_url_path(site_url(strtolower(__CLASS__."/".__FUNCTION__)),site_url(strtolower(__CLASS__."/multigrids")));

		$output = $crud->render();

		if($crud->getState() != 'list') {
			$this->_example_output($output);
		} else {
			return $output;
		}
	}

	public function customers_management2()
	{
		$crud = new grocery_CRUD();

		$crud->set_table('customers');
		$crud->columns('customerName','contactLastName','phone','city','country','salesRepEmployeeNumber','creditLimit');
		$crud->display_as('salesRepEmployeeNumber','from Employeer')
			 ->display_as('customerName','Name')
			 ->display_as('contactLastName','Last Name');
		$crud->set_subject('Customer');
		$crud->set_relation('salesRepEmployeeNumber','employees','lastName');


		echo "<pre>";
		print_r([
			strtolower(__CLASS__."/".__FUNCTION__),
			site_url(strtolower(__CLASS__."/".__FUNCTION__)),
			site_url(strtolower(__CLASS__."/multigrids"))
		]);
		echo "</pre>";

		$crud->set_crud_url_path(site_url(strtolower(__CLASS__."/".__FUNCTION__)),site_url(strtolower(__CLASS__."/multigrids")));

		$output = $crud->render();

		if($crud->getState() != 'list') {
			$this->_example_output($output);
		} else {
			return $output;
		}
	}

	public function location(){

		$base_table = 'country';
		
		// Map (Required)

		$map = [

			/* 
				// Made for nested structure tables
				// something like for Country -> City -> Municipality -> Neighborhood -> Place

				// USAGE

				"TABEL_NAME" => [
					"ref" => "FK_FIELD",
					"link_column" => "COLUMN_NAME_LINK_ASSOCIATIVE",
					"set_subject" => "SUBJECT_NAME",
					"next_depth" => "SUB_TABLE_NAME",
				],

			*/

			$base_table => [
				"ref" => null,
				"link_column" => "name",
				"set_subject" => "الدولة",
				"next_depth" => "city",
			],
			"city" => [
				"ref" => "country_id",
				"link_column" => "name",
				"set_subject" => "المدينة",
				"next_depth" => "municipality",
			],
			"municipality" => [
				"ref" => "city_id",
				"link_column" => "name",
				"set_subject" => "البلدية",
				"next_depth" => "neighborhood",
			],
			"neighborhood" => [
				"ref" => "municipality_id",
				"link_column" => "name",
				"set_subject" => "الحي",
				"next_depth" => "place",
			],
			"place" => [
				"ref" => "neighborhood_id",
				"link_column" => "name",
				"set_subject" => "المكان",
				"next_depth" => null,
			],
		];

		// vZool Deep Logic Function (Required)

		$crud = $this->_vzool_nested_crud(func_get_args(), $map, $base_table, strtolower(__CLASS__ .'/'. __FUNCTION__));

		// Your LOGIC Here

       	$crud->set_language("arabic");

		switch($base_table){
			
			case 'country':

				$crud->display_as('name','الاسم');

			break;

			case 'project':
					
				// ...

			break;

			// .
		}

		// ...

		/* --------------------------------------------------------- */

		// OUTPUT  (Required)
		$output = $crud->render();

		$this->_example_output($output);
	}

	private function _vzool_nested_crud($args, $map, $base_table, $function_name){

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

		/*  BUILD LINKS */

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

			foreach($links_table as $k => $v){

				$query = $this->db->get_where($links_table[$k], array('id' => $links_ref_id[$k]), 1);

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

		/*  BUILD LINKS */

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