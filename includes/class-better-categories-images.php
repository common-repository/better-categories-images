<?php
/**
 * Better_Category_Images setup
 *
 * @package  Better_Category_Images
 * @since    1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main Better_Category_Images Class.
 *
 * @class Better_Category_Images
 */
final class Better_Category_Images {

	/**
	 * The single instance of the class.
	 *
	 * @var Better_Category_Images
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Better_Category_Images Instance.
	 *
	 * Ensures only one instance of Better_Category_Images is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see WC()
	 * @return Better_Category_Images - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		wc_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'better-categories-images' ), BCI_VERSION );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		wc_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'better-categories-images' ), BCI_VERSION );
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->includes();
		$this->init_hooks();

		do_action( 'better_categories_images_loaded' );
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 */
	private function init_hooks() {
		add_action( 'plugins_loaded', [ $this, 'init' ] );
	}

	/**
	 * Init Better Categories Images when WordPress Initialises.
	 */
	public function init() {
		// Before init action.
		do_action( 'before_better_categories_images_init' );

		// Set up localisation.
		$this->load_plugin_textdomain();

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// Setting.
		add_action( 'admin_menu', array( $this, 'options_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		// Save data.
		add_action( 'created_term', array( $this, 'save_taxonomy_fields' ), 10, 3 );
		add_action( 'edit_term', array( $this, 'save_taxonomy_fields' ), 10, 3 );
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		// Init action.
		do_action( 'better_categories_images_init' );
	}

	public function admin_init() {
		$taxonomies = get_taxonomies();
		if ( is_array( $taxonomies ) ) {
			$options = get_option( 'bci_options' );

			if ( ! is_array( $options ) ) {
				$options = array();
			}

			if ( empty( $options['excluded_taxonomies'] ) ) {
				$options['excluded_taxonomies'] = array();
			}

			foreach ( $taxonomies as $taxonomy ) {
				if ( in_array( $taxonomy, $options['excluded_taxonomies'] ) ) {
					continue;
				}
				add_action( $taxonomy . '_add_form_fields', array( $this, 'add_taxonomy_fields' ) );
				add_action( $taxonomy . '_edit_form_fields', array( $this, 'edit_taxonomy_fields' ), 10 );
				add_filter( 'manage_edit-' . $taxonomy . '_columns', array( $this, 'taxonomy_columns' ) );
				add_filter( 'manage_' . $taxonomy . '_custom_column', array( $this, 'taxonomy_column' ), 10, 3 );
			}
		}
	}

	/**
	 * Registers Widget.
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_style( 'bci-admin', BCI_ASSETS_URL . 'css/admin.css', array(), BCI_VERSION );
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/better-categories-images/better-categories-images-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/better-categories-images-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'better-categories-images' );

		unload_textdomain( 'better-categories-images' );
		load_textdomain( 'better-categories-images', WP_LANG_DIR . '/better-categories-images/better-categories-images-' . $locale . '.mo' );
		load_plugin_textdomain( 'better-categories-images', false, plugin_basename( dirname( BCI_FILE ) ) . '/languages' );
	}

	/**
	 * Category thumbnail fields.
	 */
	public function add_taxonomy_fields() {
		wp_enqueue_media();
		?>
		<div class="form-field term-thumbnail-wrap">
			<label><?php _e( 'Thumbnail', 'better-categories-images' ); ?></label>
			<div id="taxonomy_thumbnail" style="float: left; margin-right: 10px;"><img src="<?php echo esc_url( BCI_IMAGE_PLACEHOLDER ); ?>" width="60px" height="60px" /></div>
			<div style="line-height: 60px;">
				<input type="hidden" id="taxonomy_thumbnail_id" name="taxonomy_thumbnail_id" />
				<button type="button" class="upload_image_button button"><?php _e( 'Upload/Add image', 'better-categories-images' ); ?></button>
				<button type="button" class="remove_image_button button"><?php _e( 'Remove image', 'better-categories-images' ); ?></button>
			</div>
			<script type="text/javascript">

				// Only show the "remove image" button when needed
				if ( ! jQuery( '#taxonomy_thumbnail_id' ).val() ) {
					jQuery( '.remove_image_button' ).hide();
				}

				// Uploading files
				var file_frame;

				jQuery( document ).on( 'click', '.upload_image_button', function( event ) {

					event.preventDefault();

					// If the media frame already exists, reopen it.
					if ( file_frame ) {
						file_frame.open();
						return;
					}

					// Create the media frame.
					file_frame = wp.media.frames.downloadable_file = wp.media({
						title: '<?php _e( 'Choose an image', 'better-categories-images' ); ?>',
						button: {
							text: '<?php _e( 'Use image', 'better-categories-images' ); ?>'
						},
						multiple: false
					});

					// When an image is selected, run a callback.
					file_frame.on( 'select', function() {
						var attachment           = file_frame.state().get( 'selection' ).first().toJSON();
						var attachment_thumbnail = attachment.sizes.thumbnail || attachment.sizes.full;

						jQuery( '#taxonomy_thumbnail_id' ).val( attachment.id );
						jQuery( '#taxonomy_thumbnail' ).find( 'img' ).attr( 'src', attachment_thumbnail.url );
						jQuery( '.remove_image_button' ).show();
					});

					// Finally, open the modal.
					file_frame.open();
				});

				jQuery( document ).on( 'click', '.remove_image_button', function() {
					jQuery( '#taxonomy_thumbnail' ).find( 'img' ).attr( 'src', '<?php echo esc_js( BCI_IMAGE_PLACEHOLDER ); ?>' );
					jQuery( '#taxonomy_thumbnail_id' ).val( '' );
					jQuery( '.remove_image_button' ).hide();
					return false;
				});

				jQuery( document ).ajaxComplete( function( event, request, options ) {
					if ( request && 4 === request.readyState && 200 === request.status
						&& options.data && 0 <= options.data.indexOf( 'action=add-tag' ) ) {

						var res = wpAjax.parseAjaxResponse( request.responseXML, 'ajax-response' );
						if ( ! res || res.errors ) {
							return;
						}
						// Clear Thumbnail fields on submit
						jQuery( '#taxonomy_thumbnail' ).find( 'img' ).attr( 'src', '<?php echo esc_js( BCI_IMAGE_PLACEHOLDER ); ?>' );
						jQuery( '#taxonomy_thumbnail_id' ).val( '' );
						jQuery( '.remove_image_button' ).hide();
						return;
					}
				} );

			</script>
			<div class="clear"></div>
		</div>
		<?php
	}

	/**
	 * Edit taxonomy thumbnail field.
	 *
	 * @param mixed $term Term (taxonomy) being edited
	 */
	public function edit_taxonomy_fields( $term ) {
		wp_enqueue_media();

		$thumbnail_id = absint( get_term_meta( $term->term_id, 'thumbnail_id', true ) );

		if ( $thumbnail_id ) {
			$image = wp_get_attachment_thumb_url( $thumbnail_id );
		} else {
			$image = BCI_IMAGE_PLACEHOLDER;
		}
		?>
		<tr class="form-field">
			<th scope="row" valign="top"><label><?php _e( 'Thumbnail', 'better-categories-images' ); ?></label></th>
			<td>
				<div id="taxonomy_thumbnail" style="float: left; margin-right: 10px;"><img src="<?php echo esc_url( $image ); ?>" width="60px" height="60px" /></div>
				<div style="line-height: 60px;">
					<input type="hidden" id="taxonomy_thumbnail_id" name="taxonomy_thumbnail_id" value="<?php echo $thumbnail_id; ?>" />
					<button type="button" class="upload_image_button button"><?php _e( 'Upload/Add image', 'better-categories-images' ); ?></button>
					<button type="button" class="remove_image_button button"><?php _e( 'Remove image', 'better-categories-images' ); ?></button>
				</div>
				<script type="text/javascript">

					// Only show the "remove image" button when needed
					if ( '0' === jQuery( '#taxonomy_thumbnail_id' ).val() ) {
						jQuery( '.remove_image_button' ).hide();
					}

					// Uploading files
					var file_frame;

					jQuery( document ).on( 'click', '.upload_image_button', function( event ) {

						event.preventDefault();

						// If the media frame already exists, reopen it.
						if ( file_frame ) {
							file_frame.open();
							return;
						}

						// Create the media frame.
						file_frame = wp.media.frames.downloadable_file = wp.media({
							title: '<?php _e( 'Choose an image', 'better-categories-images' ); ?>',
							button: {
								text: '<?php _e( 'Use image', 'better-categories-images' ); ?>'
							},
							multiple: false
						});

						// When an image is selected, run a callback.
						file_frame.on( 'select', function() {
							var attachment           = file_frame.state().get( 'selection' ).first().toJSON();
							var attachment_thumbnail = attachment.sizes.thumbnail || attachment.sizes.full;

							jQuery( '#taxonomy_thumbnail_id' ).val( attachment.id );
							jQuery( '#taxonomy_thumbnail' ).find( 'img' ).attr( 'src', attachment_thumbnail.url );
							jQuery( '.remove_image_button' ).show();
						});

						// Finally, open the modal.
						file_frame.open();
					});

					jQuery( document ).on( 'click', '.remove_image_button', function() {
						jQuery( '#taxonomy_thumbnail' ).find( 'img' ).attr( 'src', '<?php echo esc_js( BCI_IMAGE_PLACEHOLDER ); ?>' );
						jQuery( '#taxonomy_thumbnail_id' ).val( '' );
						jQuery( '.remove_image_button' ).hide();
						return false;
					});

				</script>
				<div class="clear"></div>
			</td>
		</tr>
		<?php
	}

	/**
	 * save_taxonomy_fields function.
	 *
	 * @param mixed  $term_id Term ID being saved
	 * @param mixed  $tt_id
	 * @param string $taxonomy
	 */
	public function save_taxonomy_fields( $term_id, $tt_id = '', $taxonomy = '' ) {
		if ( isset( $_POST['taxonomy_thumbnail_id'] ) ) {
			update_term_meta( $term_id, 'thumbnail_id', absint( $_POST['taxonomy_thumbnail_id'] ) );
		}
	}

	/**
	 * Thumbnail column added to taxonomy admin.
	 *
	 * @param mixed $columns
	 * @return array
	 */
	public function taxonomy_columns( $columns ) {
		$new_columns = array();

		if ( isset( $columns['cb'] ) ) {
			$new_columns['cb'] = $columns['cb'];
			unset( $columns['cb'] );
		}

		$new_columns['thumb'] = esc_html__( 'Image', 'better-categories-images' );

		$columns           = array_merge( $new_columns, $columns );
		$columns['handle'] = '';

		return $columns;
	}

	/**
	 * Thumbnail column value added to taxonomy admin.
	 *
	 * @param string $columns
	 * @param string $column
	 * @param int    $id
	 *
	 * @return string
	 */
	public function taxonomy_column( $columns, $column, $id ) {
		if ( 'thumb' === $column ) {

			$thumbnail_id = get_term_meta( $id, 'thumbnail_id', true );

			if ( $thumbnail_id ) {
				$image = wp_get_attachment_thumb_url( $thumbnail_id );
			} else {
				$image = BCI_IMAGE_PLACEHOLDER;
			}

			// Prevent esc_url from breaking spaces in urls for image embeds. Ref: https://core.trac.wordpress.org/ticket/23605
			$image    = str_replace( ' ', '%20', $image );
			$columns .= '<img src="' . esc_url( $image ) . '" alt="' . esc_attr__( 'Thumbnail', 'better-categories-images' ) . '" class="wp-post-image" height="48" width="48" />';
		}
		if ( 'handle' === $column ) {
			$columns .= '<input type="hidden" name="term_id" value="' . esc_attr( $id ) . '" />';
		}
		return $columns;
	}

	/**
	 * New menu submenu for plugin options in Settings menu.
	 */
	public function options_menu() {
		add_options_page( esc_html__( 'Better Categories Images settings', 'better-categories-images'), esc_html__( 'Better Categories Images', 'better-categories-images' ), 'manage_options', 'bci-options', array( $this, 'bci_options' ) );
	}

	// Register plugin settings
	public function register_settings() {
		register_setting( 'bci_options', 'bci_options', array( $this, 'options_validate' ) );
		add_settings_section( 'bci_settings', esc_html__( 'Better Categories Images settings', 'better-categories-images' ), array( $this, 'section_text' ), 'bci-options' );
		add_settings_field( 'excluded_taxonomies', esc_html__( 'Excluded Taxonomies', 'better-categories-images' ), array( $this, 'excluded_taxonomies' ), 'bci-options', 'bci_settings' );
	}

	/**
	 * Validating options.
	 *
	 * @param  [mixed] $input //.
	 * @return [mixed]        //.
	 */
	public function options_validate( $input ) {
	  return $input;
	}

	/**
	 * Settings section description.
	 */
	public function section_text() {
		echo '<p>' . esc_html__( 'Please select the taxonomies you want to exclude it from Better Categories Images plugin', 'better-categories-images' ) . '</p>';
	}

	/**
	 * Excluded taxonomies checkboxs.
	 */
	public function excluded_taxonomies() {
		$options             = get_option( 'bci_options' );
		$disabled_taxonomies = array( 'nav_menu', 'link_category', 'post_format' );
		foreach ( get_taxonomies() as $tax ) :
			if ( in_array( $tax, $disabled_taxonomies ) ) {
				continue;
			}
			?>
			<input type="checkbox" name="bci_options[excluded_taxonomies][<?php echo $tax ?>]" value="<?php echo $tax ?>" <?php checked( isset( $options['excluded_taxonomies'][$tax] ) ); ?> /> <?php echo $tax ;?><br />
		<?php endforeach;
	}

	/**
	 * Plugin option page.
	 */
	public function bci_options() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'better-categories-images' ) );
			$options = get_option( 'bci_options' );
		}
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Better Categories Images', 'better-categories-images' ); ?></h2>
			<form method="post" action="options.php">
				<?php settings_fields( 'bci_options' ); ?>
				<?php do_settings_sections( 'bci-options' ); ?>
				<?php submit_button(); ?>
			</form>
		</div>
	<?php
	}
}
