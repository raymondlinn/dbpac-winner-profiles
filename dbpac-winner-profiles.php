<?php
/**
 * Plugin Name: DBPAC Winner Profiles 
 * Plugin URI:  
 * Description: Winner profile functionality for DBPAC winners.
 * Author: Raymond Linn
 * Version: 1.0
 * Author URI: http://raymondlinn.com/
 */

function run_dbpac_winner_profile_plugin(){

	// include the files that are needed
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	require_once( ABSPATH . 'wp-admin/includes/media.php' );

	// add_action section
	// add the category to attachments
	add_action( 'init' , 'dbpac_winner_add_categories_to_attachments' );	
	// custom taxonomies not adding it.
	// add_action( 'init', 'dbpac_winner_add_winner_taxonomy' );
	// added css and javascript
	add_action('wp_enqueue_scripts', 'add_dbpac_winner_scripts');
	// process the form upload multipart
	add_action('admin_post_add_winner_profile', 'process_post_add_winner_form');
	
	// add_shortcode section
	add_shortcode('dbpac-winner-profiles', 'render_winner_profiles');
}

// add the category term to attachement
function dbpac_winner_add_categories_to_attachments() {
    register_taxonomy_for_object_type( 'category', 'attachment' );
}


// need divisions, winners, teachers taxonomy in attachments
// register new taxonomy which applies to attachments
function dbpac_winner_add_winner_taxonomy() {
 
 	// winner taxonomy
    $winner_args = array(
        'labels' => array(
	        'name'              => 'Winners',
	        'singular_name'     => 'Winner',
	        'search_items'      => 'Search Winners',
	        'all_items'         => 'All Winners',
	        'parent_item'       => 'Parent Winner',
	        'parent_item_colon' => 'Parent Winner:',
	        'edit_item'         => 'Edit Winner',
	        'update_item'       => 'Update Winner',
	        'add_new_item'      => 'Add New Winner',
	        'new_item_name'     => 'New Winner Name',
	        'menu_name'         => 'Winner',
	    ),
        'hierarchical' => true,
        'query_var' => 'true',
        'rewrite' => 'true',
        'show_admin_column' => 'true',
    );
 
    register_taxonomy( 'winner', 'attachment', $winner_args );

    // teacher taxonomy
    $teacher_args = array(
	    'labels' => array(
				        'name'              => 'Teachers',
				        'singular_name'     => 'Teacher',
				        'search_items'      => 'Search Teachers',
				        'all_items'         => 'All Teachers',
				        'parent_item'       => 'Parent Teacher',
				        'parent_item_colon' => 'Parent Teacher:',
				        'edit_item'         => 'Edit Teacher',
				        'update_item'       => 'Update Teacher',
				        'add_new_item'      => 'Add New Teacher',
				        'new_item_name'     => 'New Teacher Name',
				        'menu_name'         => 'Teacher',
				    ),    
        'hierarchical' => true,
        'query_var' => 'true',
        'rewrite' => 'true',
        'show_admin_column' => 'true',
    );
 
    register_taxonomy( 'teacher', 'attachment', $teacher_args );
}


// for scripts
function add_dbpac_winner_scripts(){
	// css
	wp_register_style( 'dbpac-winner-style', plugins_url('css/style.css', __FILE__));
    wp_enqueue_style('dbpac-winner-style');

    // jquery 
    /* do not need it
    if (!wp_script_is('jquery')) {
		wp_enqueue_script('jquery');
	}
	*/
}

// rendering the shortcode
function render_winner_profiles(){

	// 1)check if user logged in and if one of permitted users
	if(is_user_logged_in()){
		global $current_user;
		$current_user = wp_get_current_user();

		// 2) the render the first part of taking photo and insert attachment with meta form
		$permitted_users = array("joanna@teachers.org", "raymondlinn@gmail.com");
		if(in_array($current_user->user_email, $permitted_users)){
			// echo "current user is permitted to take a winner picture and submit the form";
			// render the form
			render_winner_form();
		}
		else{
			echo '<p><strong style="color:red;">You need permission to access! </strong></p>';
		}
	}

	// 3) for non permitted users, only render grid layout winner photos and meta data
	render_winner_grid_view();

}

// function to render the take a photo and submit form which includes the attachment meta data
function render_winner_form(){

?>
	<div width="70%">
		<h3><center>Submit 2017 DBPAC Winners</center><h3>
		<form id="addwinner" name="addwinner" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data">

		<?php wp_nonce_field( 'add_winner_profile', 'dbpac_winner_nonce_field' ); ?>

			<div>
				<label for="winner_name" class="desc">Winner's Name</label>
				<div>
	        		<input type="text" name="winner_name" id="winner_name" class="field text fn" placeholder="Alicia Vikander"/>
	        	</div>
	        </div>	

	        <div>
	        	<label for="teacher_name" class="desc">Teacher's Name</label>
	        	<div>
	        		<input type="text" name="teacher_name" id="teacher_name" class="field text fn" placeholder="Jane Smith"/>
	        	</div>
	        </div>
	        
	        <div>
	        	<label for="division_name" class="desc">Division</label>
	        	<div>
	        		<select id="sel_division" name="sel_division" class="field select medium"> 
			      		<option value="2017 Piano">Piano</option> 
			      		<option value="2017 Mixed Ensemble">Mixed Ensemble</option> 
			      		<option value="2017 Strings">Strings</option> 
			      		<option value="2017 Woodwinds">Woodwinds</option>
			      		<option value="2017 Guitar">Guitar</option>
			   		</select>
			   	</div>
			</div>
	   		
	   		<div>
	   			
	        	<label for="winner_image" class="desc">Take photo (press "choose file" button) </label>
	        	
	        	<div>	       	
					<input type="file" id="winner_image" name="winner_image" accept="image/*" >
				</div>
				
			</div>

	        <input type="hidden" name="action" value="add_winner_profile">

		    <div>
		        <div>
		            <input type="submit" name="add_winner" value="Add Winner" /> 
		        </div>
		    </div>
	    </form>
	</div>
	<div class="line-separator"></div>
	<div class="clearfix"></div>

<?php

}

// function to render winner grid view on the Winners page which needs to be manually created
function render_winner_grid_view(){

	// title
	echo '
		<center><h3>2017 DBPAC Winners Gallery</h3></center><br/>
	';

	// 0) check if there is any winner images

	// 1) for loop to check how many images are attached 
	// 2) retrieve the images to display in grid
	// 3) images name in tag a and image description in class desc div

	/*
	$media = get_attached_media( 'image' );
	echo '</br>';
	print_r($media);
	foreach($media as $m) {
		echo '<br>';
		echo $m->ID;

		$terms = wp_get_object_terms($m->ID, 'winner');
		
		foreach($terms as $term){
			echo '</br>';
			echo $term->name;
		}
	}
	*/
	$post_parent = get_page_by_title('Winners')->ID;
	//echo $post_parent;
	//echo '<br/>';
	$attachments = get_posts( array(
        'post_type'   => 'attachment',
        'numberposts' => -1,
        'post_status' => null,
        'post_parent' => $post_parent
    ) );
    

    if ( $attachments ) {
    	//echo '</br>';
    	//print_r($attachments);

        foreach ( $attachments as $attachment ) {
	?>
			<?php $img = wp_get_attachment_image_src( $attachment->ID, 'thumbnail' ); ?>
			<div class="responsive">
				  <div class="gallery">
				    <a target="_blank" <?php echo 'href="'.$img[0].'"';?>>
				    	<?php echo '<img src="'.$img[0].'">'; ?>
				    </a>
				    <div class="desc">
				    	<?php echo $attachment->post_title . '<br/>'; ?>
				    	<?php echo 'Teacher: ' . $attachment->post_content. '<br/>'; ?>
				    	<?php 
				    		$cats = get_the_category($attachment->ID);
				    		echo esc_html($cats[0]->name);
				    	?>
				    </div>				  
				  </div>
				</div>			
    	
    <?php
       } // end foreach
    }
    else {
    	echo '</br>';
    	echo '<p><strong style="color:red;">No Winners have been posted yet!<strong></p>';
    }

	// anything the we would like to inform
	echo '
			<div class="clearfix"></div>
	';
}

// function to process winner profile form
function process_post_add_winner_form() {
	
	if('POST' == $_SERVER['REQUEST_METHOD']) {
		$redirect_url = home_url('winners');

		if(!empty($_POST) && wp_verify_nonce($_REQUEST['dbpac_winner_nonce_field'], 'add_winner_profile')){
			//echo '</br>';
			//echo 'there is form to process';
			// get the student name
			$winner_name = sanitize_text_field($_POST['winner_name']);
			//echo "</br>";
			//echo $winner_name;
			// get the teacher name
			$teacher_name = sanitize_text_field($_POST['teacher_name']);
			//echo "</br>";
			//echo $teacher_name;
			// get the division
			$selected_division = sanitize_text_field($_POST['sel_division']);
			echo "</br>";
			echo $selected_division;

			// get the image file
			if(isset($_FILES)) {
				$uploadedfile = $_FILES['winner_image'];
				//echo "</br>";
				//echo $uploadedfile;
				$upload_overrides = array('test_form' => false);
				// upload the file to upload_dir
				//echo "</br>";
				$movefile = wp_handle_upload($uploadedfile, $upload_overrides);
				if ($movefile && !isset($movefile['error'])){
				    echo "File is valid, and was successfully uploaded.\n";
				} else{ 					
					$errors = $movefile['error'];
					echo $error;
					$redirect_url = add_query_arg( 'winners-errors', $errors, $redirect_url );
					wp_redirect($redirect_url);
					exit;
				}

				// attach file to winners page
				$filename = $movefile['file'];
				//echo "</br>";
				//echo $filename;

				$page = get_page_by_title('Winners');
				$parent_post_id = $page->ID;
				//echo "</br>";
				//print_r($parent_post_id);
				
				// check file type
				$filetype = wp_check_filetype(basename($filename), null);

				// Get the path to the upload directory.
				$wp_upload_dir = wp_upload_dir();
				//echo "</br>";
				//print_r($wp_upload_dir);

				// Prepare an array of post data for the attachment.
				// post_title to be winner's name
				// post_content to be teacher's name				
				$attachment = array(
					'guid'           => $wp_upload_dir['url'] . '/' . basename($filename), 
					'post_mime_type' => $filetype['type'],
					'post_title'     => $winner_name,
					'post_content'   => $teacher_name,
					'post_status'    => 'inherit'
				);
				//echo '</br>';
				//print_r($attachment);

				// Insert the attachment.
				$attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );
				if($attach_id == 0){
					// return error
					$errors = 'Insert attachment Failed';
					$redirect_url = add_query_arg( 'winners-errors', $errors, $redirect_url );
					wp_redirect($redirect_url);
					exit;
				}
				//echo "</br>";
				//echo $attach_id;

				// Generate the metadata for the attachment, and update the database record.
				$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
				//echo "</br>";
				//echo $attach_data;

				// category to be selected division				
				$category_taxonomy_id = wp_set_object_terms($attach_id, $selected_division, 'category', true);
				if ( is_wp_error( $category_taxonomy_id ) ) {
					echo "</br>";
					echo "Adding category error!";
				} 
				
				
				//update_post_meta($attach_id, 'winner', $winner_name);
				wp_update_attachment_metadata( $attach_id, $attach_data );				

				// commented out as we don't need to set post thumbnail here
				// set_post_thumbnail( $parent_post_id, $attach_id );
				
				// save the attachment id with update post meta data 
				// redirect
				//wp_redirect($redirect_url);
				//exit;
			}
			else {
				// return error
				$errors = 'no file was uploaded';
				$redirect_url = add_query_arg( 'winners-errors', $errors, $redirect_url );
				wp_redirect($redirect_url);
				exit;
			}
		}

	}
}

run_dbpac_winner_profile_plugin();
