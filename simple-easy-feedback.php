<?php
/**
 * @package Simple Easy Feedback
 * @version 1.0
 */
/*
  Plugin Name: Simple Easy Feedback
  Description: Simple Easy Feedback create feedback in your site.
  Author: ifourtechnolab
  Version: 1.0
  Author URI: http://www.ifourtechnolab.com/
  License: GPLv2 or later
  License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
 
if (!defined('ABSPATH')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

define('SEF_URL', plugin_dir_url(__FILE__));

global $wpdb, $wp_version;
define("WP_SEF_TABLE", $wpdb->prefix . "simple_easy_feedback");

/*
 * Main class
 */
class Simple_Easy_Feedback {

    /**
     * @global type $wp_version
     */
    public function __construct() {
        global $wp_version;
        
        /*
         *  Front-Side
         */
        /* Run scripts and shortcode */
        add_action('wp_enqueue_scripts', array($this, 'sef_frontend_scripts'));
        add_shortcode('simple-easy-feedback-plugin', array($this, 'simple_easy_feedback_shortcode'));        
        
        /* 
         * Admin-Side 
         * */
        /* Setup menu and run scripts */
        add_action('admin_menu', array($this, 'sef_plugin_setup_menu'));
        add_action('admin_enqueue_scripts', array($this, 'sef_backend_scripts'));
        
        /* Save simple easy feedback in database */
        add_action('admin_action_save-simple-easy-feedback',array($this, 'savesimpleeasyfeedback'));
        
        add_filter('widget_text','do_shortcode');
    }
        
    /** Create table and Insert default data */
    function my_plugin_create_db() {
		
		global $wpdb;
		
		$sql = "CREATE TABLE " . WP_SEF_TABLE . " (
			`feedback_id` mediumint(9) NOT NULL AUTO_INCREMENT,
			`fieldname` tinytext NOT NULL,
			`labelname` tinytext NOT NULL,
			`placeholder` tinytext NOT NULL,
			`status` char(3) NOT NULL default 'YES',
			PRIMARY KEY (feedback_id)
			);";
				  
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta($sql);
		
		$query = ("INSERT INTO ".WP_SEF_TABLE."
            (`fieldname`, `labelname`, `placeholder`, `status`)
            VALUES
            ('sf_toemail', '', '', 'YES'),
            ('sf_question', '', 'Please enter your question', 'NO'),
            ('sf_name', 'Name', 'Please enter your name', 'YES'),
            ('sf_subject', 'Subject', 'Please enter your subject', 'NO'),
            ('sf_email', 'Email', 'Please enter your email', 'YES'),
            ('sf_comments', 'Comments', 'Please enter your comments', 'YES'),
            ('sf_butname', 'Submit', '', 'YES')");
         dbDelta($query);
    }



/** 
 * 
 * ---------------------------------ADMIN SIDE----------------------------------- 
 * 
**/
    
    /**
     * Setup simple easy feedback to Admin Menu.
     * @global type $user_ID
     */
    public function sef_plugin_setup_menu() {
		global $user_ID;
		$title		 = apply_filters('sef_menu_title', 'Simple Easy Feedback');
		$capability	 = apply_filters('sef_capability', 'edit_others_posts');
		$page		 = add_menu_page($title, $title, $capability, 'sef',
			array($this, 'admin_simpleeasyfeedback'), "", 9501);
		add_action('load-'.$page, array($this, 'help_tab'));
    }

	/**
     * Admin simple feedback
     */
    public function admin_simpleeasyfeedback() {
		global $wpdb;
		
		$query = $wpdb->get_results("SELECT * FROM " . WP_SEF_TABLE . " order by feedback_id");
		foreach ($query as $data) :
			
			$feedbackid[] = $wpdb->_escape(trim($data->feedback_id));
			$lname[] = $wpdb->_escape(trim($data->labelname));
			$pholder[] = $wpdb->_escape(trim($data->placeholder));
			$status[] = $wpdb->_escape(trim($data->status));
			
		endforeach; 
		?>
	
		<div class="wrap">

			<div id="icon-options-general" class="icon32"></div>
			<h1><?php esc_attr_e( 'Simple Easy Feedback', 'wp_admin_style' ); ?></h1>

			<div id="poststuff">

				<div id="post-body" class="metabox-holder columns-2">

					<!-- main content -->
					<div id="post-body-content">

						<div class="meta-box-sortables ui-sortable">

							<div class="postbox">

								<div class="inside">
									
									<form method="post" action="<?php echo admin_url( 'admin.php' ); ?>">
										
										<input type="hidden" name="action" value="save-simple-easy-feedback" />
										
										<table style="width:100%;" id="simpleeasyfeedback">
										  
										<tr>
											<td valign="top">
												<label for="first_name">To Email</label>
											</td>
											<td valign="top" colspan="3">
												<input  type="text" name="label[]" value="<?php echo $lname[0]; ?>">
												<input  type="hidden" name="pholder[]" value="">
												<input  type="hidden" name="status[]" value="<?php echo $feedbackid[0]; ?>">
											</td>
										</tr>
										  
										<tr>
											<td valign="top">
												<label for="first_name">Question</label>
											</td>
											<td valign="top" colspan="2">
												<input  type="text" name="label[]" value="<?php echo $lname[1]; ?>">
												<input  type="hidden" name="pholder[]" value="">
											</td>
											<td valign="top" align="center">
												<input  type="checkbox" name="status[]" value="<?php echo $feedbackid[1]; ?>" <?php if($status[1]=='YES') { echo 'checked="checked"'; } ?>>
											</td>
										</tr>
										  
										  <tr>
											<th style="width: 30%;">Field</th>
											<th style="width: 30%;">Label</th>
											<th style="width: 30%;">PlaceHolder</th>
											<th style="width: 10%;">Status</th>
										  </tr>
										  
										  <tr>
											 <td valign="top">
												<label for="first_name">Name </label>
											 </td>
											 <td valign="top">
												<input  type="text" name="label[]" value="<?php echo $lname[2]; ?>">
											 </td>
											 <td valign="top">
												<input  type="text" name="pholder[]" value="<?php echo $pholder[2]; ?>">
											 </td>
											 <td valign="top" align="center">
												<input  type="checkbox" name="status[]" value="<?php echo $feedbackid[2]; ?>" <?php if($status[2]=='YES') { echo 'checked="checked"'; } ?>>
											 </td>
										  </tr>
										  
										  <tr>
											 <td valign="top">
												<label for="email">Subject </label>
											 </td>
											 <td valign="top">
												<input  type="text" name="label[]" value="<?php echo $lname[3]; ?>">
											 </td>
											 <td valign="top">
												<input  type="text" name="pholder[]" value="<?php echo $pholder[3]; ?>">
											 </td>
											 <td valign="top" align="center">
												<input  type="checkbox" name="status[]" value="<?php echo $feedbackid[3]; ?>" <?php if($status[3]=='YES') { echo 'checked="checked"'; } ?>>
											 </td>
										  </tr>
										  
										  <tr>
											 <td valign="top">
												<label for="email">Email</label>
											 </td>
											 <td valign="top">
												<input  type="text" name="label[]" value="<?php echo $lname[4]; ?>">
											 </td>
											 <td valign="top">
												<input  type="text" name="pholder[]" value="<?php echo $pholder[4]; ?>">
											 </td>
											 <td valign="top" align="center">
												<input  type="hidden" name="status[]" value="<?php echo $feedbackid[4]; ?>">
											 </td>
										  </tr>
										  
										  <tr>
											 <td valign="top">
												<label for="comments">Comments/Message</label>
											 </td>
											 <td valign="top">
												<input  type="text" name="label[]" value="<?php echo $lname[5]; ?>">
											 </td>
											 <td valign="top">
												<input  type="text" name="pholder[]" value="<?php echo $pholder[5]; ?>">
											 </td>
											 <td valign="top" align="center">
												<input  type="hidden" name="status[]" value="<?php echo $feedbackid[5]; ?>">
											 </td>
										  </tr>
										  
										  <tr>
											 <td valign="top">
												<label for="buttonname">Button Name</label>
											 </td>
											 <td valign="top" colspan="3">
												<input  type="text" name="label[]" value="<?php echo $lname[6]; ?>">
												<input  type="hidden" name="pholder[]" value="">
												<input  type="hidden" name="status[]" value="<?php echo $feedbackid[6]; ?>">
											 </td>
									       </tr>
										  
									   </table>
									   
										<table style="width:100%;" id="simpleeasyfeedback">
											<tr>
												<td colspan="4" style="text-align:center">
													<input type="submit" value="Submit" id="btnsaveform">
												</td>
											</tr>
										</table>

									</form>
									
								</div>
								<!-- .inside -->

							</div>
							<!-- .postbox -->

						</div>
						<!-- .meta-box-sortables .ui-sortable -->

					</div>
					<!-- post-body-content -->


					<!-- sidebar -->
					<div id="postbox-container-1" class="postbox-container">

						<div class="meta-box-sortables">

							<div class="postbox">

								<h2><span><?php esc_attr_e(
											'Sidebar', 'wp_admin_style'
										); ?></span></h2>

								<div class="inside">
									<p>Add <strong><code>[simple-easy-feedback-plugin]</code></strong> shortcode for use.</p>
								</div>
								<!-- .inside -->

							</div>
							<!-- .postbox -->

						</div>
						<!-- .meta-box-sortables -->

					</div>
					<!-- #postbox-container-1 .postbox-container -->

				</div>
				<!-- #post-body .metabox-holder .columns-2 -->

				<br class="clear">
			</div>
			<!-- #poststuff -->

		</div> <!-- .wrap -->
	<?php
    }
    
    // Simple feedback save in Database
    public function savesimpleeasyfeedback() {
		
		global $wpdb;
		
		$label = $wpdb->_escape($_REQUEST['label']);
		$pholder = $wpdb->_escape($_REQUEST['pholder']);
		$status = $wpdb->_escape($_REQUEST['status']);
		
		$wpdb->query($wpdb->prepare("UPDATE ".WP_SEF_TABLE." SET status='NO'"));
		
		for($i=0;$i<=6;$i++) {
			$feedbackid = $i+1;
			
			$wpdb->query($wpdb->prepare("UPDATE ".WP_SEF_TABLE." SET 
			labelname='".$label[$i]."',placeholder='".$pholder[$i]."' WHERE feedback_id=$feedbackid"));
			
			if(!empty($status[$i])) {
				$wpdb->query($wpdb->prepare("UPDATE ".WP_SEF_TABLE." SET status='YES' WHERE feedback_id=$status[$i]"));
			}
		}
		
		header("location:".$_SERVER['HTTP_REFERER']);
		exit();
    }
    
    /**
     * css and javascript scripts.
     */
    public function sef_backend_scripts() {
		wp_enqueue_style('sef-css-handler-backend', SEF_URL.'assets/css/simple-easy-feedback.css');
		//wp_enqueue_script('sef-js-handler-backend', SEF_URL.'assets/js/simple-easy-feedback.js',array('jquery'),'1.0.0',true);
    }
    
    
    
    
/** 
 * 
 * ---------------------------------FRONT END----------------------------------- 
 * 
**/
    
    /** Create simple easy feedback and Add short code */
	function simple_easy_feedback_shortcode( $atts ) {
		
		add_action('wp_enqueue_scripts', array($this, 'sef_frontend_scripts'));
		
		global $wpdb;
		
		$query = $wpdb->get_results("SELECT * FROM " . WP_SEF_TABLE . " order by feedback_id");
		foreach ($query as $data) :
			
			$fname[] = $wpdb->_escape(trim($data->fieldname));
			$lname[] = $wpdb->_escape(trim($data->labelname));
			$pholder[] = $wpdb->_escape(trim($data->placeholder));
			$status[] = $wpdb->_escape(trim($data->status));
			
		endforeach;	
		
		if(isset($_POST['front-end-action'])){
			
			/** Front end - send mail simple feedback  */
			$hidden = $_POST['front-end-action'];
			if($hidden == 'SMSF'){
				
				$contact_errors = false;
				
				$toemail = $wpdb->_escape(trim($_POST['sf_toemail']));
				$fromemail = $wpdb->_escape(trim($_POST['sf_email']));
				
				$question = $wpdb->_escape(trim($_POST['sf_question']));
				if(!empty($question)) {
					$question = $question."\n<br /><br />\n";
				}
				$name = $wpdb->_escape(trim($_POST['sf_name']));
				if(!empty($name)) {
					if($name == 'SF02') {
						$name = "";
					} else {
						$name = 'Name :- '.$name."\n<br /><br />\n";
					}
				}
				$subject = $wpdb->_escape(trim($_POST['sf_subject']));
				if($subject == 'SF03') {
					$subject = "";
				}
				$comments = $wpdb->_escape(trim($_POST['sf_comments']));
				if(!empty($comments)) {
					$var = nl2br($comments);
					$comments = 'Comments :- '.$var;
				}
				
				$headers = "";
				if(!empty($fromemail)) {
					$headers = "From: ".$fromemail. " \r\n";
				}
			
				$contents = $question."".$name."".$comments."";
				
				
				if(is_email($fromemail)) {
					add_filter('wp_mail_content_type',array($this,'set_html_content_type'));
					
					if(!wp_mail($toemail, $subject, $contents, $headers)) {
						$contact_errors = true;
						$msgresponce = 'Mail failed!';
					} else {
						$msgresponce = 'Your feedback has been received and is very valuable to us.';
					}
					remove_filter( 'wp_mail_content_type',array($this,'set_html_content_type') );
					
				} else {
					$msgresponce = "Email not correct!";
				}
				
				?>
				<table class="front-simpleeasyfeedback">
				
					<tr>
						<th colspan="2"><h2>Feedback</h2></th>
					</tr>
					
					<tr>
						<td colspan="2"><p class="message"><?php echo $msgresponce; ?></p></td>
					</tr>
					
				</table>
				<?php
				
			}
			
		} else {
			
		?>
	
		<form method="post" action="" id="frontsimpleeasyfeedback" onsubmit="return ValidateSimpleFeedback();">
			
			<input type="hidden" name="front-end-action" value="SMSF" />
			<input  type="hidden" name="<?php echo $fname[0]; ?>" value="<?php echo $lname[0]; ?>">
			
			<table class="front-simpleeasyfeedback">
				
				<tr>
					<th colspan="2"><h2>Feedback</h2></th>
				</tr>
				
				<!-- Question -->
				<?php if($status[1] == 'YES') { ?>
					<tr>
						<td colspan="2" align="center">
							<label for="<?php echo $lname[1]; ?>"><strong><?php echo $lname[1]; ?></strong></label>
							<input  type="hidden" name="<?php echo $fname[1]; ?>" value="<?php echo $lname[1]; ?>">
						</td>
					</tr>
				<?php } ?>
				
				<!-- Enter name -->
				<?php if($status[2] == 'YES') { ?>
					<tr>
						<td valign="top">
							<label for="<?php echo $fname[2]; ?>"><?php echo $lname[2]; ?></label>
						</td>
						<td valign="top">
							<input  type="text" name="<?php echo $fname[2]; ?>" id="<?php echo $fname[2]; ?>" placeholder="<?php echo $pholder[2]; ?>">
							<div id='name_error' class='error'>Please enter your name.</div>
						</td>
					</tr>
				<?php } else { ?>
					<input  type="hidden" name="<?php echo $fname[2]; ?>" value="SF02">		
				<?php } ?>	
				
				<!-- Enter subject -->
				<?php if($status[3] == 'YES') { ?>
					<tr>
						<td valign="top">
							<label for="<?php echo $fname[3]; ?>"><?php echo $lname[3]; ?></label>
						</td>
						<td valign="top">
							<input  type="text" name="<?php echo $fname[3]; ?>" id="<?php echo $fname[3]; ?>" placeholder="<?php echo $pholder[3]; ?>">
							<div id='subject_error' class='error'>Please enter your subject.</div>
						</td>
					</tr>
				<?php } else { ?>
					<input  type="hidden" name="<?php echo $fname[3]; ?>" value="SF03">		
				<?php } ?>	
				
				<!-- Enter email -->
				<?php if($status[4] == 'YES') { ?>
					<tr>
						<td valign="top">
							<label for="<?php echo $fname[4]; ?>"><?php echo $lname[4]; ?> *</label>
						</td>
						<td valign="top">
							<input  type="email" name="<?php echo $fname[4]; ?>" id="<?php echo $fname[4]; ?>" placeholder="<?php echo $pholder[4]; ?>">
							<div id='email_error' class='error'>Please enter your email.</div>
						</td>
					</tr>
				<?php } ?>
				
				<!-- Enter comments -->
				<?php if($status[5] == 'YES') { ?>
					<tr>
						<td valign="top">
							<label for="<?php echo $fname[5]; ?>"><?php echo $lname[5]; ?> *</label>
						</td>
						<td valign="top">
							<textarea name="<?php echo $fname[5]; ?>" id="<?php echo $fname[5]; ?>" placeholder="<?php echo $pholder[5]; ?>" maxlength="1000" cols="25" rows="4"></textarea>
							<div id='comments_error' class='error'>Please enter comments.</div>
						</td>
					</tr>
				<?php } ?>
				
				<tr>
					<td colspan="2" style="text-align:center">
						<input type="submit" id="btnsendfeedback" value="<?php if($status[6] == 'YES') { echo $lname[6]; }?>">
					</td>
				</tr>
				
			</table>
		</form>
	
		<?php 
		}
	}
	
	/**
     * Content html type
     */
    public function set_html_content_type() {
		return 'text/html';
	}	
    
    /**
     * Front-end css and javascript initialize.
     */
    public function sef_frontend_scripts() {
		wp_enqueue_style('sef-css-handler', SEF_URL.'assets/css/simple-easy-feedback.css');
		wp_enqueue_script('sef-js-handler', SEF_URL.'assets/js/simple-easy-feedback.js',array('jquery'),'1.0.0',true);
    }


    /**
     * Add the help tab to the screen.
     */
    public function help_tab()
    {
		$screen = get_current_screen();

		// documentation tab
		$screen->add_help_tab(array(
			'id' => 'documentation',
			'title' => __('Documentation', 'sef'),
			'content' => "<p><a href='http://www.ifourtechnolab.com/documentation/' target='blank'>Simple Easy Feedback</a></p>",
			)
		);
    }

    /**
     * Deactivation hook.
     */
    public function sef_deactivation_hook() {
		if (function_exists('update_option')) {
			global $wpdb;
			$sql = "DROP TABLE IF EXISTS $table_name".WP_SEF_TABLE;
			$wpdb->query($sql);
		}
    }

    /**
     * Uninstall hook
     */
    public function sef_uninstall_hook() {
		if (current_user_can('delete_plugins')) {
			
		}
    }
}

$simpleeasyfeedback = new Simple_Easy_Feedback();

register_activation_hook( __FILE__, array('Simple_Easy_Feedback', 'my_plugin_create_db') );

register_deactivation_hook(__FILE__, array('Simple_Easy_Feedback', 'sef_deactivation_hook'));

register_uninstall_hook(__FILE__, array('Simple_Easy_Feedback', 'sef_uninstall_hook'));
