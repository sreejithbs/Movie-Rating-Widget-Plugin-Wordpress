<?php
/*
Plugin Name: MovieBrutha Rating Widget
Plugin URI: https://www.themoviebrutha.com
Description: Movie Rating Widget based on Genre
Version: 1.0
Author: Spericorn Technology
Author URI: https://www.spericorn.com
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// Solution to blocked by CORS policy: No 'Access-Control-Allow-Origin' header is present on the requested resource.
header('Access-Control-Allow-Origin: *');

// plugin_dir_path() returns the trailing slash!
define('MOVIEBRUTHA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MOVIEBRUTHA_PLUGIN_URL', plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__)) . '/');

// WP_List_Table is not loaded automatically so we need to load it in our application
if (!class_exists('WP_List_Table')) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

require_once(MOVIEBRUTHA_PLUGIN_DIR . 'includes/SimpleXLSX.php');

// function to create the DB tables
function rating_widget_table_install()
{

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$rating_widget_imports = $wpdb->prefix . 'rating_widget_imports';
	$rating_widget_logs = $wpdb->prefix . 'rating_widget_logs';
	$rating_widget_title_overview = $wpdb->prefix . 'rating_widget_title_overview';
	$rating_widget_faqs = $wpdb->prefix . 'rating_widget_faqs';

	// create rating_widget_imports table
	if ($wpdb->get_var("show tables like '$rating_widget_imports'") != $rating_widget_imports) {
		$sql = "
			CREATE TABLE IF NOT EXISTS " . $rating_widget_imports . " (
				id int(11) NOT NULL AUTO_INCREMENT,
				directorName varchar(190) NOT NULL,
				directorScore varchar(50) NOT NULL,
				created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			) $charset_collate;
		";
		dbDelta($sql);
	}

	// create rating_widget_logs table
	if ($wpdb->get_var("show tables like '$rating_widget_logs'") != $rating_widget_logs) {
		$sql = "
			CREATE TABLE IF NOT EXISTS " . $rating_widget_logs . " (
				id int(11) NOT NULL AUTO_INCREMENT,
				runTime varchar(50) NOT NULL,
				ageOfFilm varchar(50) NOT NULL,
				directorName varchar(190) NOT NULL,
				directorScore varchar(50) NOT NULL,
				ratingScore varchar(50) NOT NULL,
				genre TEXT NOT NULL,
				ipAddress varchar(190) NOT NULL,
				created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			) $charset_collate;
		";
		dbDelta($sql);
	}

	if ($wpdb->get_var("show tables like '$rating_widget_title_overview'") != $rating_widget_title_overview) {
		$sql = "
			CREATE TABLE IF NOT EXISTS " . $rating_widget_title_overview . " (
				id int(11) NOT NULL AUTO_INCREMENT,
				title varchar(190) DEFAULT NULL,
				overview TEXT DEFAULT NULL,
				created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			) $charset_collate;
		";
		dbDelta($sql);
	}

	if ($wpdb->get_var("show tables like '$rating_widget_faqs'") != $rating_widget_faqs) {
		$sql = "
			CREATE TABLE IF NOT EXISTS " . $rating_widget_faqs . " (
				id int(11) NOT NULL AUTO_INCREMENT,
				question TEXT DEFAULT NULL,
				answer TEXT DEFAULT NULL,
				created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			) $charset_collate;
		";
		dbDelta($sql);
	}
}
register_activation_hook(__FILE__, 'rating_widget_table_install');

function rating_widget_table_uninstall()
{
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	global $wpdb;

	$rating_widget_imports = $wpdb->prefix . 'rating_widget_imports';
	$rating_widget_logs = $wpdb->prefix . 'rating_widget_logs';
	$rating_widget_title_overview = $wpdb->prefix . 'rating_widget_title_overview';
	$rating_widget_faqs = $wpdb->prefix . 'rating_widget_faqs';

	$sql = "DROP TABLE IF EXISTS $rating_widget_faqs;";
	$wpdb->query($sql);
	$sql = "DROP TABLE IF EXISTS $rating_widget_title_overview;";
	$wpdb->query($sql);
	$sql = "DROP TABLE IF EXISTS $rating_widget_imports;";
	$wpdb->query($sql);
	$sql = "DROP TABLE IF EXISTS $rating_widget_logs;";
	$wpdb->query($sql);

}
// register_deactivation_hook(__FILE__, 'rating_widget_table_uninstall');


// Include CSS & JS
add_action('wp_enqueue_scripts', 'includes_css_js');
add_action('admin_enqueue_scripts', 'includes_css_js');
function includes_css_js()
{
	wp_enqueue_style('bootstrap', MOVIEBRUTHA_PLUGIN_URL . 'includes/css/bootstrap.min.css');
	wp_enqueue_style('movie_brutha_main', MOVIEBRUTHA_PLUGIN_URL . 'includes/css/main.css');
	wp_enqueue_style('movie_brutha_rate-form-style', MOVIEBRUTHA_PLUGIN_URL . 'includes/css/rate-form-style.css');
	wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/css/select2.min.css');
	wp_enqueue_style('font', 'https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900');
	wp_enqueue_style('font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
	wp_enqueue_style('toastr', 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css');

	wp_enqueue_script('bootstrap', MOVIEBRUTHA_PLUGIN_URL . 'includes/js/bootstrap.min.js', array('jquery'), true);
	wp_enqueue_script('parsley', MOVIEBRUTHA_PLUGIN_URL . 'includes/js/parsley.min.js', array('jquery'), true);
	wp_enqueue_script('movie_brutha_custom', MOVIEBRUTHA_PLUGIN_URL . 'includes/js/movie_brutha_custom.js', array('jquery'), true);
	wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/js/select2.min.js', array('jquery'), true);
	wp_enqueue_script('toastr', 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js', array('jquery'), true);
}
// end of Include CSS & JS

// Declare ajaxurl GLOBALLY
add_action('wp_head', 'myplugin_ajaxurl');
function myplugin_ajaxurl()
{

	echo '<script type="text/javascript">
		   var ajaxurl = "' . admin_url('admin-ajax.php') . '";
		 </script>';
}


// Add the admin menu and submenu page
add_action('admin_menu', 'rating_widget_admin_menu');
function rating_widget_admin_menu()
{
	add_menu_page(
		__('Rating Widget Submissions', 'rating-widget'),
		__('Rating Widget Submissions', 'rating-widget'),
		'edit_theme_options',
		'rating-widget',
		'render_logs_listing'
	);

	add_submenu_page('rating-widget', 'Import Director Z Score', 'Import Director Z Score', 'edit_theme_options', 'file-import', 'render_file_import');
	add_submenu_page('rating-widget', 'Text Settings', 'Text Settings', 'edit_theme_options', 'title-update', 'render_title_update');
}


function html_form_code()
{ ?>

	<h2>Import Director and Director Z Score</h2>
	<small style="color: red"> This action will overwrite existing DIRECTOR SCORES present in the database</small><br /><br />
	<form class="form-inline" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post" enctype="multipart/form-data">
		<div class="form-group">
			<label for="email">Upload Excel Sheet (required)</label>
			<input name="upload_file" type="file" />
		</div>
		<div class="form-group">
			<input type="submit" class="btn btn-sm btn-primary" name="form-submitted" value="Import">
		</div>
	</form>
	<hr>

	<?php
	}

	function process_excel_file()
	{

		global $wpdb;
		$rating_widget_imports = $wpdb->prefix . 'rating_widget_imports';

		// if the submit button is clicked, send the email
		if (isset($_POST['form-submitted'])) {

			if (isset($_FILES['upload_file'])) {

				if ($xlsx = SimpleXLSX::parse($_FILES['upload_file']['tmp_name'])) {

					// Produce array keys from the array values of 1st array element
					$header_values = $rows = [];
					foreach ($xlsx->rows() as $k => $r) {
						if ($k === 0) {
							$header_values = $r;
							continue;
						}
						$rows[] = array_combine($header_values, $r);
					}
					// print_r( $rows );

					if (count($rows) > 0) {
						$wpdb->query('TRUNCATE TABLE ' . $rating_widget_imports);

						$values = array();
						foreach ($rows as $key => $value) {
							$values[] = $wpdb->prepare("(%s, %s)", $value['Directors'], $value['Director Z Score']);
						}

						$values = implode(",\n", $values);
						$insertData = $wpdb->query("INSERT INTO $rating_widget_imports (directorName, directorScore) VALUES {$values} ");
						if ($insertData) {
							// echo '<script>alert("Success")</script>';
						}
					}
				} else {
					echo SimpleXLSX::parseError();
				}
			}
		}
	}

	// ************************ IMPORTED TABLE *************************
	function render_imported_listing()
	{

		/**
		 * Create a new table class that will extend the WP_List_Table
		 */
		class Imported_List_Table extends WP_List_Table
		{
			/**
			 * Prepare the items for the table to process
			 *
			 * @return Void
			 */
			public function prepare_items()
			{
				$columns = $this->get_columns();
				$hidden = $this->get_hidden_columns();
				$sortable = $this->get_sortable_columns();

				$data = $this->table_data();
				usort($data, array(&$this, 'sort_data'));

				$perPage = 50;
				$currentPage = $this->get_pagenum();
				$totalItems = count($data);

				$this->set_pagination_args(array(
					'total_items' => $totalItems,
					'per_page'    => $perPage
				));

				$data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);

				$this->_column_headers = array($columns, $hidden, $sortable);
				$this->items = $data;
			}

			/**
			 * Override the parent columns method. Defines the columns to use in your listing table
			 *
			 * @return Array
			 */
			public function get_columns()
			{
				$columns = array(
					'id'          	=> 'Sl No',
					'directorName'	=> 'Director Name',
					'directorScore' => 'Director Score',
				);

				return $columns;
			}

			/**
			 * Define which columns are hidden
			 *
			 * @return Array
			 */
			public function get_hidden_columns()
			{
				return array();
			}

			/**
			 * Define the sortable columns
			 *
			 * @return Array
			 */
			public function get_sortable_columns()
			{
				return array('directorName' => array('directorName', false));
			}

			/**
			 * Get the table data
			 *
			 * @return Array
			 */
			private function table_data()
			{
				global $wpdb;
				$rating_widget_imports = $wpdb->prefix . 'rating_widget_imports';
				$data = $wpdb->get_results("SELECT id, directorName, directorScore FROM $rating_widget_imports", ARRAY_A);

				return $data;
			}

			/**
			 * Define what data to show on each column of the table
			 *
			 * @param  Array $item        Data
			 * @param  String $column_name - Current column name
			 *
			 * @return Mixed
			 */
			public function column_default($item, $column_name)
			{
				switch ($column_name) {
					case 'id':
					case 'directorName':
					case 'directorScore':
						return $item[$column_name];

					default:
						return print_r($item, true);
				}
			}

			/**
			 * Allows you to sort the data by the variables set in the $_GET
			 *
			 * @return Mixed
			 */
			private function sort_data($a, $b)
			{
				// Set defaults
				$orderby = 'directorName';
				$order = 'asc';

				// If orderby is set, use this as the sort column
				if (!empty($_GET['orderby'])) {
					$orderby = $_GET['orderby'];
				}

				// If order is set use this as the order
				if (!empty($_GET['order'])) {
					$order = $_GET['order'];
				}

				$result = strcmp($a[$orderby], $b[$orderby]);

				if ($order === 'asc') {
					return $result;
				}

				return -$result;
			}
		}

		/**
		 * Display the list table page
		 *
		 * @return Void
		 */

		$importedListTable = new Imported_List_Table();
		$importedListTable->prepare_items();
		?>
		<div class="wrap">
			<div id="icon-users" class="icon32"></div>
			<h2>Director and Director Z Score Listing</h2>
			<?php $importedListTable->display(); ?>
		</div>
	<?php
	}
	// ************************ IMPORTED TABLE *************************

	function render_file_import()
	{
		html_form_code();
		process_excel_file();
		render_imported_listing();
	}

	function render_title_update()
	{
		global $wpdb;
		$rating_widget_title_overview = $wpdb->prefix . 'rating_widget_title_overview';
		$rating_widget_faqs = $wpdb->prefix . 'rating_widget_faqs';
		$title_overview = $wpdb->get_results("SELECT title, overview FROM $rating_widget_title_overview ORDER BY id ASC LIMIT 1");
		$rating_faqs = $wpdb->get_results("SELECT * FROM $rating_widget_faqs", ARRAY_A);

		?>
		<div class="wrap">
			<h1><strong>Text Settings</strong></h1>
			<h3>Change Widget Label</h3>
			<textarea id="title-text" cols="37" rows="3"><?php echo $wpdb->num_rows > 0 ? $title_overview[0]->title : "" ?></textarea>
			<br/>
			<button type="button" class="btn btn-primary btn-sm title_overview_update" data-type="title">Update Label</button>

			<br/><hr><br/>

			<h3>Change Widget Page Overview</h3>
			<textarea id="overview-text" cols="150" rows="5"><?php echo $wpdb->num_rows > 0 ? $title_overview[0]->overview : "" ?></textarea>
			<br/>
			<button type="button" class="btn btn-primary btn-sm title_overview_update" data-type="overview">Update Overview</button>

			<br/><hr><br/>

			<h3>Add FAQs</h3>
			<button style="display:inline-block;" type="button" id="clone_faq" class="btn btn-success">+ Add More</button>
			<br/><br/>
			<div class="row">
				<div class="col-md-11 faq-cover-div">
					<?php
					if ($wpdb->num_rows > 0) {
						foreach ($rating_faqs as $single) { ?>

							<div class="row single-clone">
								<div class="col-md-5">
									<textarea cols="55" rows="3" name="faq_question[]" class="faq_question" placeholder="Enter Question"><?php echo $single['question']; ?></textarea>
								</div>
								<div class="col-md-5">
									<textarea cols="55" rows="3" name="faq_answer[]" class="faq_answer" placeholder="Enter Answer"><?php echo $single['answer']; ?></textarea>
								</div>
								<div class="col-md-1">
									<button type="button" class="btn btn-danger btn-sm del_clone">x</button>
								</div>
							</div>

						<?php } ?>

					<?php } else{ ?>

						<div class="row single-clone">
							<div class="col-md-5">
								<textarea cols="55" rows="3" name="faq_question[]" class="faq_question" placeholder="Enter Question"></textarea>
							</div>
							<div class="col-md-5">
								<textarea cols="55" rows="3" name="faq_answer[]" class="faq_answer" placeholder="Enter Answer"></textarea>
							</div>
							<div class="col-md-1">
								<button type="button" class="btn btn-danger btn-sm del_clone">x</button>
							</div>
						</div>

					<?php } ?>
				</div>
			</div>
			<br/>
			<button type="button" class="btn btn-primary btn-sm" id="submit_faq">Submit FAQs</button>

		</div>
		<?php
		}

		// ************************ LOGS TABLE *****************************
		function render_logs_listing()
		{

			class Logs_List_Table extends WP_List_Table
			{

				function __construct()
				{
					global $status, $page;

					//Set parent defaults
					parent::__construct(array(
						'singular'  => 'User Submission Log',     //singular name of the listed records
						'plural'    => 'User Submission Logs',    //plural name of the listed records
						'ajax'      => false        //does this table support ajax?
					));
				}

				/**
				 * Define what data to show on each column of the table
				 *
				 * @param  Array $item        Data
				 * @param  String $column_name - Current column name
				 *
				 * @return Mixed
				 */
				public function column_default($item, $column_name)
				{
					switch ($column_name) {
						case 'id':
						case 'runTime':
						case 'ageOfFilm':
						case 'ratingScore':
						case 'ipAddress':
							return $item[$column_name];
						case 'directorName':
							return $item[$column_name] . '(' . $item['directorScore'] . ')';
						case 'genre':
							return implode(", ", json_decode($item[$column_name]));
						case 'created_at':
							return date('Y/m/d', strtotime($item[$column_name]));

						default:
							return print_r($item, true);
					}
				}

				function column_title($item)
				{
					//Build row actions
					$actions = array(
						'delete'    => sprintf('<a href="?page=%s&action=%s&logs=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['id']),
					);

					//Return the title contents
					return sprintf(
						'%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
						/*$1%s*/
						$item['directorName'],
						/*$2%s*/
						$item['id'],
						/*$3%s*/
						$this->row_actions($actions)
					);
				}

				function column_cb($item)
				{
					return sprintf(
						'<input type="checkbox" name="%1$s[]" value="%2$s" />',
						/*$1%s*/
						$this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
						/*$2%s*/
						$item['id']                //The value of the checkbox should be the record's id
					);
				}

				/**
				 * Override the parent columns method. Defines the columns to use in your listing table
				 *
				 * @return Array
				 */
				public function get_columns()
				{
					$columns = array(
						'cb'        		=> '<input type="checkbox" />', //Render a checkbox instead of text
						'created_at'        => 'Submission Date',
						'runTime'       	=> 'Runtime (minutes)',
						'ageOfFilm' 		=> 'Age of Film',
						'directorName'		=> 'Director Name & Score',
						'ratingScore'      	=> 'Rating Score',
						'genre'    			=> 'Genre',
						'ipAddress'    		=> 'IP Address',
					);

					return $columns;
				}

				function get_sortable_columns()
				{
					$sortable_columns = array(
						'created_at'     => array('created_at', false),     //true means it's already sorted
					);
					return $sortable_columns;
				}


				function get_bulk_actions()
				{
					$actions = array(
						'delete'    => 'Delete Entries',
					);
					return $actions;
				}

				function process_bulk_action()
				{

					global $wpdb;
					$rating_widget_logs = $wpdb->prefix . 'rating_widget_logs';

					if (isset($_POST['usersubmissionlog'])) {
						//Detect when a bulk action is being triggered...
						if ('delete' === $this->current_action()) {
							$delete_ids = esc_sql($_POST['usersubmissionlog']);
							$in = implode(',', $delete_ids);
							$delete_result = $wpdb->query("DELETE FROM $rating_widget_logs WHERE id IN ($in)");
						}
					}
				}

				function prepare_items()
				{

					global $wpdb;
					$rating_widget_logs = $wpdb->prefix . 'rating_widget_logs';

					$per_page = 50;

					$columns = $this->get_columns();
					$hidden = array();
					$sortable = $this->get_sortable_columns();

					$this->_column_headers = array($columns, $hidden, $sortable);

					$this->process_bulk_action();

					$data = $wpdb->get_results("SELECT * FROM $rating_widget_logs ORDER BY id DESC", ARRAY_A);

					function usort_reorder($a, $b)
					{
						$orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id'; //If no sort, default to title
						$order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
						$result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
						return ($order === 'asc') ? $result : -$result; //Send final sort direction to usort
					}
					usort($data, 'usort_reorder');

					$current_page = $this->get_pagenum();

					$total_items = count($data);

					$data = array_slice($data, (($current_page - 1) * $per_page), $per_page);

					$this->items = $data;

					$this->set_pagination_args(array(
						'total_items' => $total_items,                  //WE have to calculate the total number of items
						'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
						'total_pages' => ceil($total_items / $per_page)   //WE have to calculate the total number of pages
					));
				}
			}

			//Create an instance of our package class...
			$logsListTable = new Logs_List_Table();
			//Fetch, prepare, sort, and filter our data...
			$logsListTable->prepare_items();

			?>
			<div class="wrap">

				<div id="icon-users" class="icon32"><br /></div>
				<h2>User Submission Logs</h2>

				<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
				<form id="logs" method="POST">
					<!-- For plugins, we also need to ensure that the form posts back to our current page -->
					<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
					<!-- Now we can render the completed list table -->
					<?php $logsListTable->display() ?>
				</form>

			</div>
			<?php
			}
			// ************************ LOGS TABLE *****************************


			// DISPLAY WIDGET
			add_action('wp_head', 'displayWidgetbox');

			function displayWidgetbox()
			{
				global $post, $wpdb;
				$rating_widget_imports = $wpdb->prefix . 'rating_widget_imports';
				$rating_widget_title_overview = $wpdb->prefix . 'rating_widget_title_overview';
				$importedLists = $wpdb->get_results("SELECT * FROM $rating_widget_imports", ARRAY_A);
				$title = $wpdb->get_results("SELECT * FROM $rating_widget_title_overview ORDER BY id ASC LIMIT 1");

				// determine whether this page contains "my_shortcode" shortcode
				$shortcode_found = false;
				if (!has_shortcode($post->post_content, 'render_widget_on_page')) { ?>

					<style>
						.rate-box-outer .the-mv-rate-box .rate-head h2 {
							margin-top: 4px;
						}

						.rate-box-outer .the-mv-rate-box .rate-head h2:before {
							display: none;
						}

						.rate-box-outer .forms ul {
							padding-left: 0;
							margin-bottom: 0;
						}

						.rate-box-outer .forms li {
							list-style: none;
						}

						.rate-box-outer .parsley-errors-list li {
							font: 11px/16px "Poppins", sans-serif;
							color: #e42222;
						}

						.forms button:hover {
							background: #FFB726 !important;
						}

						.rate-box-outer {
							z-index: 999999;
						}

						.select2-container {
							z-index: 99999999;
							position: relative;
						}

						.rate-box-outer {
							display: none;
						}
					</style>

					<div class="rate-box-outer">
						<div class="the-mv-rate-box" id="listaCategoriaA">
							<div class="rate-head">
								<div class="movie_brutha_logo">
									<img src="<?php echo MOVIEBRUTHA_PLUGIN_URL ?>includes/img/m_brutha_logo.png; ?>" alt="">
								</div>

								<i><img src="<?php echo MOVIEBRUTHA_PLUGIN_URL ?>includes/img/star.png; ?>" alt=""></i>
								<h2><?php echo $title[0]->title != '' ? $title[0]->title : "Ant’s Movie Model Predictor" ?></h2>
							</div>
							<div id="formDiv">
								<form method="POST" action="#" id="widget_form_id" data-parsley-validate>
									<div class="forms">
										<div class="row">
											<div class="col-xs-6 pdg-rgt">
												<div class="input-holder">
													<label for="">Enter Film’s Runtime hour(s)</label>
													<input type="number" name="run_hour" id="run_hour" placeholder="Film’s Runtime hour(s)" data-parsley-required="true" data-parsley-type="digits" data-parsley-min="0" data-parsley-max="100" autofocus>
												</div>
											</div>
											<div class="col-xs-6 pdg-lft">
												<div class="input-holder">
													<label for="">Enter Film’s Runtime (minutes)</label>
													<input type="number" name="run_minutes" id="run_minutes" placeholder="Film’s Runtime (minutes)" data-parsley-required="true" data-parsley-type="digits" data-parsley-min="0" data-parsley-max="240">
												</div>
											</div>
										</div>


										<div class="input-holder">
											<label for="">Select Age of Film (year)</label>
											<div class="sl-bx">
												<select name="age_of_film" id="age_of_film" data-parsley-required="true">
													<option value="">Select Age of Film (year)</option>
													<?php
														$current_year = date("Y");
														$start_year = $current_year - 5;
														for ($x = $start_year; $x <= $current_year; $x++) {
															echo "<option value=" . $x . ">" . $x . "</option>";
														}
														?>

												</select>
											</div>

										</div>
										<div class="input-holder">
											<label for="">Select Film’s Director (choose Unknown/Other if unlisted)</label>
											<div class="sl-bx">
												<select name="director_score" id="director_score" data-parsley-required="true">
													<option value="">Select Film’s Director <br />(choose Unknown/Other if unlisted)</option>
													<?php foreach ($importedLists as $single) { ?>
														<option value="<?php echo $single['directorScore']; ?>">
															<?php echo $single['directorName']; ?>
														</option>
													<?php } ?>
												</select>
											</div>
										</div>
										<div class="input-holder">
											<label for="">Choose Film’s Genre(s)</label>
											<div class="sl-bx custom-error-handler">
												<select name="genres[]" class="js-select2 genres" multiple="multiple" data-parsley-required="true">
													<option value="Action">Action</option>
													<option value="Adventure">Adventure</option>
													<option value="Animation">Animation</option>
													<option value="Biography">Biography</option>
													<option value="Comedy">Comedy</option>
													<option value="Crime">Crime</option>
													<option value="Documentary">Documentary</option>
													<option value="Drama">Drama</option>
													<option value="Family">Family</option>
													<option value="Fantasy">Fantasy</option>
													<option value="History">History</option>
													<option value="Horror">Horror</option>
													<option value="Music">Music</option>
													<option value="Musical">Musical</option>
													<option value="Mystery">Mystery</option>
													<option value="Romance">Romance</option>
													<option value="Sci_Fi">Sci_Fi</option>
													<option value="Sport">Sport</option>
													<option value="Thriller">Thriller</option>
													<option value="War">War</option>
													<option value="Western">Western</option>
												</select>
											</div>
										</div>
									</div>
									<div class="footer">
										<button type="submit" name="submit_widget_form" id="submit_widget_form">
											SUBMIT FOR MOVIE RATING
											<span class="overlay-btn"><i class="fa fa-circle-o-notch fa-spin"></i></span>
										</button>
									</div>
								</form>
							</div>

							<div class="forms" id="scoreDiv" style="display: none;">
								<h3>Score: <span id="score_span"></span></h3>
								<button type="button" id="retake_rating">Re-Take Score Rating</button>
							</div>
						</div>
						<button class="rate-button" id="btCategoriaA">
							<i><img src="<?php echo MOVIEBRUTHA_PLUGIN_URL ?>includes/img/star.png; ?>" alt=""></i><span><?php echo $title[0]->title != '' ? $title[0]->title : "Ant’s Movie Model Predictor" ?></span>
						</button>
					</div>

			<?php }
		}

		function handleTitleOverviewUpdate()
		{
			global $wpdb;
			$rating_widget_title_overview = $wpdb->prefix . 'rating_widget_title_overview';
			if($_POST['type'] == 'title'){
				$title = $_POST['value'];
				$select = "SELECT title from $rating_widget_title_overview";
				$wpdb->query($select);

				if ($wpdb->num_rows > 0) {
					$sql = "UPDATE $rating_widget_title_overview SET title = '$title' WHERE id = 1";
				} else {
					$sql = "INSERT INTO $rating_widget_title_overview (title) VALUES ('$title') ";
				}
				$result['message'] = "Widget label updated successfully";
			} else{
				$overview = $_POST['value'];
				$select = "SELECT overview from $rating_widget_title_overview";
				$wpdb->query($select);

				if ($wpdb->num_rows > 0) {
					$sql = "UPDATE $rating_widget_title_overview SET overview = '$overview' WHERE id = 1";
				} else {
					$sql = "INSERT INTO $rating_widget_title_overview (overview) VALUES ('$overview') ";
				}
				$result['message'] = "Widget page overview updated successfully";
			}

			$status = $wpdb->query($sql);
			if ($status) {
				$result['status'] = true;
			} else {
				$result['message'] = "Updation failure";
				$result['status'] = false;
			}
			echo json_encode($result);
			die();
		}
		add_action('wp_ajax_nopriv_handleTitleOverviewUpdate', 'handleTitleOverviewUpdate');
		add_action('wp_ajax_handleTitleOverviewUpdate', 'handleTitleOverviewUpdate');


		function handleFormSubmission()
		{
			global $wpdb;
			$rating_widget_logs = $wpdb->prefix . 'rating_widget_logs';

			$run_minutes = $_POST['run_minutes'];
			$run_hour = $_POST['run_hour'];
			$hour_tominute = $_POST['run_hour'] * 60;
			$run_time = $hour_tominute +  $run_minutes;
			$year_of_film = $_POST['age_of_film'];
			$current_year = date("Y");
			$age_of_film = $current_year - $year_of_film;

			$director_score = $_POST['director_score'];
			$director_name = $_POST['director_name'];
			$genresArr = $_POST['genres'];
			$checked_genresArr = json_encode($_POST['checked_genres']);

			if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
				$ip_address = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
			} else {
				$ip_address = sanitize_text_field($_SERVER['REMOTE_ADDR']);
			}

			$postData = '{
			  "Runtime (mins)": ' . (int) $run_time . ',
			  "AgeofFilm": ' . (int) $age_of_film . ',
			  "Director Z Score": ' . (float) $director_score . ',
			  "Action": ' . ($genresArr[0] ? 1 : 0) . ',
			  "Adventure": ' . ($genresArr[1] ? 1 : 0) . ',
			  "Animation": ' . ($genresArr[2] ? 1 : 0) . ',
			  "Biography": ' . ($genresArr[3] ? 1 : 0) . ',
			  "Comedy": ' . ($genresArr[4] ? 1 : 0) . ',
			  "Crime": ' . ($genresArr[5] ? 1 : 0) . ',
			  "Documentary": ' . ($genresArr[6] ? 1 : 0) . ',
			  "Drama": ' . ($genresArr[7] ? 1 : 0) . ',
			  "Family": ' . ($genresArr[8] ? 1 : 0) . ',
			  "Fantasy": ' . ($genresArr[9] ? 1 : 0) . ',
			  "History": ' . ($genresArr[10] ? 1 : 0) . ',
			  "Horror": ' . ($genresArr[11] ? 1 : 0) . ',
			  "Music": ' . ($genresArr[12] ? 1 : 0) . ',
			  "Musical": ' . ($genresArr[13] ? 1 : 0) . ',
			  "Mystery": ' . ($genresArr[14] ? 1 : 0) . ',
			  "Romance": ' . ($genresArr[15] ? 1 : 0) . ',
			  "Sci_Fi": ' . ($genresArr[16] ? 1 : 0) . ',
			  "Sport": ' . ($genresArr[17] ? 1 : 0) . ',
			  "Thriller": ' . ($genresArr[18] ? 1 : 0) . ',
			  "War": ' . ($genresArr[19] ? 1 : 0) . ',
			  "Western": ' . ($genresArr[20] ? 1 : 0) . '
			}';

			$ch = curl_init('http://13.56.20.11/production/models/TradewindModel/predict');
			// $ch = curl_init('http://13.56.20.11/themoviebrutha/models/TradewindModel/predict');
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_USERPWD, 'spericorn:88a3eb31-9ede-4e62-ae0b-fb9a221ae9e0');
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			$response = curl_exec($ch);
			curl_close($ch);

			// Check for errors
			if ($response === FALSE) {
				die('Error');
			}

			// Decode the response
			$responseData = json_decode($response, true);

			if ($responseData['result']) {

				$score = number_format((float) $responseData['result'][0]['Score'], 1, '.', '');
				$insertLog = $wpdb->query("INSERT INTO $rating_widget_logs (runTime, ageOfFilm, directorName, directorScore, ratingScore, genre, ipAddress) VALUES ('$run_time', '$age_of_film', '$director_name', '$director_score', '$score', '$checked_genresArr', '$ip_address') ");

				if ($insertLog) {
					$tempArr = array("status" => TRUE, "message" => 'Table data Inserted successfully', 'score' => $score);
				} else {
					$tempArr = array("status" => FALSE, "message" => 'Table data Insertion failed');
				}
			} else {
				$tempArr = array("status" => FALSE, "message" => 'Promote API Error');
			}

			echo json_encode($tempArr);
			die();
		}
		add_action('wp_ajax_nopriv_handleFormSubmission', 'handleFormSubmission');
		add_action('wp_ajax_handleFormSubmission', 'handleFormSubmission');


		function handleFAQSubmission()
		{
			global $wpdb;
			$rating_widget_faqs = $wpdb->prefix . 'rating_widget_faqs';

			$wpdb->query('TRUNCATE TABLE ' . $rating_widget_faqs);

			foreach ($_POST['mainArr'] as $key => $single) {
				$question = $single['question'];
				$answer = $single['answer'];
				$insertFAQs = $wpdb->query("INSERT INTO $rating_widget_faqs (question, answer) VALUES ('$question', '$answer') ");
			}

			if ($insertFAQs) {
				$tempArr = array("status" => TRUE, "message" => 'FAQs inserted/updated successfully');
			} else {
				$tempArr = array("status" => FALSE, "message" => 'FAQs insertion/updation failed');
			}

			echo json_encode($tempArr);
			die();
		}
		add_action('wp_ajax_nopriv_handleFAQSubmission', 'handleFAQSubmission');
		add_action('wp_ajax_handleFAQSubmission', 'handleFAQSubmission');


		function render_widget_on_page_shortcode()
		{
			global $wpdb;
			$rating_widget_imports = $wpdb->prefix . 'rating_widget_imports';
			$rating_widget_title_overview = $wpdb->prefix . 'rating_widget_title_overview';
			$rating_widget_faqs = $wpdb->prefix . 'rating_widget_faqs';

			$importedLists = $wpdb->get_results("SELECT * FROM $rating_widget_imports", ARRAY_A);
			$titleOverview = $wpdb->get_results("SELECT * FROM $rating_widget_title_overview ORDER BY id ASC LIMIT 1");
			if($titleOverview[0]->title != ""){
				$title = $titleOverview[0]->title;
			} else{
				$title = "Ant’s Movie Model Predictor";
			}
			$rating_faqs = $wpdb->get_results("SELECT * FROM $rating_widget_faqs", ARRAY_A);

			$current_year = date("Y");
			$start_year = $current_year - 5;
			$options = '';
			$directors = '';
			for ($x = $start_year; $x <= $current_year; $x++) {
				$options .= '<option value=' . $x . '>' . $x . '</option>';
			}
			foreach ($importedLists as $single) {
				$directors .= '<option value='.$single['directorScore'] .'>'.
					$single['directorName'] . '
							</option>';
			}

			$star_img = MOVIEBRUTHA_PLUGIN_URL .'includes/img/star.png';
			$logo_img = MOVIEBRUTHA_PLUGIN_URL .'includes/img/m_brutha_overview.png';

			$data = '<div class="movie-theme-outer"><div class="overview-img"><img src="'.$logo_img.'" alt=""></div><h3>Overview</h3><p>'. $titleOverview[0]->overview .'</p>';

			$data.= '<div class="rate-box-outer-forms">
				<div class="the-mv-rate-box" id="listaCategoriaA">
					<div class="rate-head">
						<i><img src="'.$star_img.'" alt=""></i>
						<h2>'. $title .'</h2>
					</div>
					<div id="formDiv">
						<form method="POST" action="#" id="widget_form_id" data-parsley-validate>
							<div class="forms">
								<div class="row">
									<div class="col-xs-6 pdg-rgt">
										<div class="input-holder">
											<label for="">Enter Film’s Runtime hour(s)</label>
											<input type="number" name="run_hour" id="run_hour" placeholder="Film’s Runtime hour(s)" data-parsley-required="true" data-parsley-type="digits" data-parsley-min="0" data-parsley-max="100" autofocus>
										</div>
									</div>
									<div class="col-xs-6 pdg-lft">
										<div class="input-holder">
											<label for="">Enter Film’s Runtime (minutes)</label>
											<input type="number" name="run_minutes" id="run_minutes" placeholder="Film’s Runtime (minutes)" data-parsley-required="true" data-parsley-type="digits" data-parsley-min="0" data-parsley-max="240">
										</div>
									</div>
								</div>


								<div class="input-holder">
									<label for="">Select Age of Film (year)</label>
									<div class="sl-bx">
										<select name="age_of_film" id="age_of_film" data-parsley-required="true">
											<option value="">Select Age of Film (year)</option>'
											. $options . '
										</select>
									</div>

								</div>
								<div class="input-holder">
									<label for="">Select Film’s Director (choose Unknown/Other if unlisted)</label>
									<div class="sl-bx">
										<select name="director_score" id="director_score" data-parsley-required="true">
											<option value="">Select Film’s Director <br />(choose Unknown/Other if unlisted)</option>'
											. $directors .
										'</select>
									</div>
								</div>
								<div class="input-holder">
									<label for="">Choose Film’s Genre(s)</label>
									<div class="sl-bx custom-error-handler1">
										<select name="genres[]" class="js-select2 genres" multiple="multiple" data-parsley-required="true">
											<option value="Action">Action</option>
											<option value="Adventure">Adventure</option>
											<option value="Animation">Animation</option>
											<option value="Biography">Biography</option>
											<option value="Comedy">Comedy</option>
											<option value="Crime">Crime</option>
											<option value="Documentary">Documentary</option>
											<option value="Drama">Drama</option>
											<option value="Family">Family</option>
											<option value="Fantasy">Fantasy</option>
											<option value="History">History</option>
											<option value="Horror">Horror</option>
											<option value="Music">Music</option>
											<option value="Musical">Musical</option>
											<option value="Mystery">Mystery</option>
											<option value="Romance">Romance</option>
											<option value="Sci_Fi">Sci_Fi</option>
											<option value="Sport">Sport</option>
											<option value="Thriller">Thriller</option>
											<option value="War">War</option>
											<option value="Western">Western</option>
										</select>
									</div>
								</div>
							</div>
							<div class="footer">
								<button type="submit" name="submit_widget_form" id="submit_widget_form">
									SUBMIT FOR MOVIE RATING
									<span class="overlay-btn"><i class="fa fa-circle-o-notch fa-spin"></i></span>
								</button>
							</div>
						</form>
					</div>

					<div class="forms" id="scoreDiv" style="display: none;">
						<h3>Score: <span id="score_span"></span></h3>
						<button type="button" id="retake_rating">Re-Take Score Rating</button>
					</div>
				</div>

			 </div>';

			$data.='<h3>FAQs</h3>
				<div class="panel-group" id="accordion">';
					if ($wpdb->num_rows > 0) {
						foreach ($rating_faqs as $key => $single) {

							$data.= '
							<div class="panel panel-default">
								<div class="panel-heading">
									<h4 class="panel-title">
									<a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapse'. $key .'">
										'. $single["question"] .'
									</a>
									</h4>
								</div>
								<div id="collapse'. $key .'" class="panel-collapse collapse">
									<div class="panel-body">
										<p> '. $single["answer"] .' </p>
									</div>
								</div>
							</div>';
					    }
					}

					$data.= '
				</div>';

			$data.= '</div>';

			return $data;
		}

		// register shortcode
		add_shortcode('render_widget_on_page', 'render_widget_on_page_shortcode');
