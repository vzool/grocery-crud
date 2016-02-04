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
		
		// Map

		$map = [

			/* 
				// Made for nested structure tables
				// something like Country -> City -> Municipality -> Neighborhood -> Place

				// USAGE

				"TABEL_NAME" => [
					"ref" => "FK_FIELD",
					"link_column" => "COLUMN_NAME_LINK_ASSOCIATIVE",
					"next_depth" => "SUB_TABLE_NAME",
				],

			*/

			$base_table => [
				"ref" => null,
				"link_column" => "name",
				"next_depth" => "city",
			],
			"city" => [
				"ref" => "country_id",
				"link_column" => "name",
				"next_depth" => "municipality",
			],
			"municipality" => [
				"ref" => "city_id",
				"link_column" => "name",
				"next_depth" => "neighborhood",
			],
			"neighborhood" => [
				"ref" => "municipality_id",
				"link_column" => "name",
				"next_depth" => "place",
			],
			"place" => [
				"ref" => "neighborhood_id",
				"link_column" => "name",
				"next_depth" => null,
			],
		];

		/*##########################################################*/
		/*##################### DEEP LOGIC #########################*/
		/*##########################################################*/

		$base_url = current_url();

		$args = func_get_args();

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

			}elseif(strpos($base_url, 'edit') || strpos($base_url, 'update_validation') || strpos($base_url, 'update') || strpos($base_url, 'delete')){
				
				if($args_count > 2){

					$last_section = $args[$args_count - 3];
					
				}else{

					$last_section = $base_table;
				}

			}elseif (strpos($base_url, 'ajax_list')) {


			}

			print_r("<h3>TMP: $last_section - $args_count</h3>");

			$base_table = $last_section;
		}
		
		if (strpos($base_url, $base_table) === false){
			$base_url .= '/' . $base_table;
		}

		print_r($base_url. '<br/>');
		print_r(strpos($base_url, 'edit'). '<br/>');
		print_r($args);

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

				$crud->callback_column($map[$base_table]['link_column'], function() use($base_url, $map, $base_table){

					$x = func_get_args();

					if($map[$base_table]['next_depth']){

						$url = $base_url . '/' . $x[1]->id . '/' . $map[$base_table]['next_depth'];

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
		
		$crud->unset_columns($ignore);

		foreach($ignore as $f){
			$crud->change_field_type($f,'invisible');
		}

		/*##########################################################*/
		/*##################### DEEP LOGIC #########################*/
		/*##########################################################*/

		// Your LOGIC Here

       	$crud->set_language("arabic");

		switch($base_table){
			
			case 'country':

				$crud->set_subject("الدولة");

			break;

			case 'project':
				
				$crud->set_subject("المشروع");

			break;

			// ..
		}

		// ...

		/* --------------------------------------------------------- */

		// OUTPUT
		$output = $crud->render();

		$this->_example_output($output);
	}

	function _vzool_nested_crud($args){
		
	}

}