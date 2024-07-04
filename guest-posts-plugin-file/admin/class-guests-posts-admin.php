<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://http://localhost/
 * @since      1.0.0
 *
 * @package    Guests_Posts
 * @subpackage Guests_Posts/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Guests_Posts
 * @subpackage Guests_Posts/admin
 * @author     rk <ramelitedesk@gmail.com>
 */
class Guests_Posts_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_shortcode('guest_post_form', array($this, 'gpp_submission_form_shortcode'));
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Guests_Posts_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Guests_Posts_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/guests-posts-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Guests_Posts_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Guests_Posts_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/guests-posts-admin.js', array('jquery'), $this->version, false);
	}

	public function gpp_register_guest_post_type()
	{
		$labels = array(
			'name'               => _x('Guest Posts', 'post type general name', 'textdomain'),
			'singular_name'      => _x('Guest Post', 'post type singular name', 'textdomain'),
			'menu_name'          => _x('Guest Posts', 'admin menu', 'textdomain'),
			'name_admin_bar'     => _x('Guest Post', 'add new on admin bar', 'textdomain'),
			'add_new'            => _x('Add New', 'guest post', 'textdomain'),
			'add_new_item'       => __('Add New Guest Post', 'textdomain'),
			'new_item'           => __('New Guest Post', 'textdomain'),
			'edit_item'          => __('Edit Guest Post', 'textdomain'),
			'view_item'          => __('View Guest Post', 'textdomain'),
			'all_items'          => __('All Guest Posts', 'textdomain'),
			'search_items'       => __('Search Guest Posts', 'textdomain'),
			'parent_item_colon'  => __('Parent Guest Posts:', 'textdomain'),
			'not_found'          => __('No guest posts found.', 'textdomain'),
			'not_found_in_trash' => __('No guest posts found in Trash.', 'textdomain')
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __('Description.', 'textdomain'),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array('slug' => 'guest-post'),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array('title', 'editor', 'author', 'thumbnail', 'custom-fields'),
			'menu_icon'          => 'dashicons-admin-post', // Use dashicons for menu icon
		);

		register_post_type('guest_post', $args);
	}

	public function gpp_submission_form_shortcode()
	{
		ob_start();
?>
		<form id="gpp-submit-form" method="post">
			<p><label for="title">Title:</label><br />
				<input type="text" id="title" name="title" required />
			</p>

			<p><label for="content">Content:</label><br />
				<textarea id="content" name="content" rows="8" required></textarea>
			</p>

			<p><label for="author_name">Your Name:</label><br />
				<input type="text" id="author_name" name="author_name" required />
			</p>

			<p><label for="author_email">Your Email:</label><br />
				<input type="email" id="author_email" name="author_email" required />
			</p>

			<p><input type="submit" name="submit" value="Submit Guest Post" /></p>
		</form>
	<?php
		return ob_get_clean();
	}



	public	function gpp_process_submission()
	{
		if (isset($_POST['submit']) && $_POST['submit'] == 'Submit Guest Post') {
			$title = sanitize_text_field($_POST['title']);
			$content = wp_kses_post($_POST['content']);
			$author_name = sanitize_text_field($_POST['author_name']);
			$author_email = sanitize_email($_POST['author_email']);

			$new_post = array(
				'post_title'    => $title,
				'post_content'  => $content,
				'post_author'   => 1, // Change this if you want to assign a different author ID
				'post_status'   => 'pending', // Set initial status to pending for admin review
				'post_type'     => 'guest_post'
			);

			$post_id = wp_insert_post($new_post);

			if (!is_wp_error($post_id)) {
				// Save additional meta data
				update_post_meta($post_id, 'author_name', $author_name);
				update_post_meta($post_id, 'author_email', $author_email);


				$this->store_custom_data($post_id, $author_name, $author_email);
				// Redirect or display success message
				// Example: wp_redirect( home_url() );
				// exit;
			} else {
				// Handle errors
				// Example: wp_die( 'Error creating post' );
			}
		}
	}

	// Register admin menu page
	public function gpp_admin_menu()
	{
		add_menu_page(
			'Guest Posts',
			'Guest Posts Details',
			'manage_options',
			'guest-posts',
			'gpp_guest_posts_page',
			'dashicons-admin-post', // Icon
			20 // Menu position
		);
	}
	public function store_custom_data($post_id, $author_name, $author_email)
	{
		global $wpdb;

		$table_name = $wpdb->prefix . 'guest_post_data';

		$wpdb->insert(
			$table_name,
			array(
				'post_id' => $post_id,
				'author_name' => $author_name,
				'author_email' => $author_email
			),
			array('%d', '%s', '%s')
		);
	}
}
// Callback function to display guest posts page

function gpp_guest_posts_page()
{
	?>
	<div class="wrap">
		<h1>Guest Posts</h1>
		<?php
		// Handle post status change actions here if needed
		if (isset($_GET['action']) && isset($_GET['post_id'])) {
			$action = $_GET['action'];
			$post_id = $_GET['post_id'];
			if ($action == 'approve') {
				wp_update_post(array(
					'ID' => $post_id,
					'post_status' => 'publish'
				));
			} elseif ($action == 'reject') {
				wp_update_post(array(
					'ID' => $post_id,
					'post_status' => 'draft'
				));
			}
		}
		?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th>Title</th>
					<th>Author</th>
					<th>Submission Date</th>
					<th>Status</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				<?php
				// Query guest posts
				$args = array(
					'post_type'      => 'guest_post',
					'posts_per_page' => 10, // Adjust posts per page as needed
					'paged'          => isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1,
					'orderby'        => 'date',
					'order'          => 'DESC'
				);

				$query = new WP_Query($args);

				if ($query->have_posts()) {
					while ($query->have_posts()) {
						$query->the_post();
						$author_name = get_post_meta(get_the_ID(), 'author_name', true);
						$submission_date = get_the_date();
						$status = get_post_status();
				?>
						<tr>
							<td><?php the_title(); ?></td>
							<td><?php echo $author_name; ?></td>
							<td><?php echo $submission_date; ?></td>
							<td><?php echo $status; ?></td>
							<td>
								<?php if ($status == 'pending') : ?>
									<a href="<?php echo admin_url('admin.php?page=guest-posts&action=approve&post_id=' . get_the_ID()); ?>" class="button">Approve</a>
									<a href="<?php echo admin_url('admin.php?page=guest-posts&action=reject&post_id=' . get_the_ID()); ?>" class="button">Reject</a>
								<?php endif; ?>
							</td>
						</tr>
					<?php
					}
				} else {
					?>
					<tr>
						<td colspan="5">No guest posts found.</td>
					</tr>
				<?php
				}
				wp_reset_postdata();
				?>
			</tbody>
		</table>
		<?php
		// Pagination
		$total_pages = $query->max_num_pages;
		if ($total_pages > 1) {
			$current_page = max(1, intval($_GET['paged']));
			echo '<div class="pagination">';
			echo paginate_links(array(
				'base'    => admin_url('admin.php?page=guest-posts&paged=%#%'),
				'format'  => '&paged=%#%',
				'current' => $current_page,
				'total'   => $total_pages,
			));
			echo '</div>';
		}
		?>
	</div>
<?php
}
