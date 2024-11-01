<?php
/*
Plugin Name:wp-effective-contact-us
Plugin URI: http://www.hire-web-developers.com/
Description: This WH-Contacts plug-in creates a contactform along with captcha field  WordPress 2.7 or higher.
Version: 3.0.0
Author: hire-web-developers.com
Author URI: http://www.hire-web-developers.com/
License: GPL
WH-Contacts - This plug-in manages contacts in wordpress
Version 3.0.0
Copyright (C) 2012 hire-web-developers.com
Released 2012-01-31
Contact hire-web-developers.com at http://www.hire-web-developers.com/
*/

// +---------------------------------------------------------------------------+
// | WP hooks                                                                  |
// +---------------------------------------------------------------------------+

/* WP actions */

register_activation_hook( __FILE__, 'whcf_install' );
register_deactivation_hook( __FILE__, 'whcf_deactivate' );
add_action('admin_menu', 'whcf_addcontacts');
add_action( 'admin_init', 'register_whcf_options' );
add_action('init', 'whcf_addcss');
add_shortcode('WH-contacts', 'whcf_contactform');

function register_whcf_options() { // whitelist options

  register_setting( 'whcf-option-group', 'whcf_admng' );
  register_setting( 'whcf-option-group', 'whcf_deldata' );
  register_setting( 'whcf-option-group', 'whcf_copyright' );
  register_setting( 'whcf-option-group', 'whcf_captcha_key');
  register_setting( 'whcf-option-group', 'whcf_database_insert');
}


function unregister_whcf_options() { // unset options

  unregister_setting( 'whcf-option-group', 'whcf_admng' );
  unregister_setting( 'whcf-option-group', 'whcf_deldata' );
  unregister_setting( 'whcf-option-group', 'whcf_copyright' );
  unregister_setting( 'whcf-option-group', 'whcf_captcha_key');
  unregister_setting( 'whcf-option-group', 'whcf_database_insert');
}


// +---------------------------------------------------------------------------+
// | Create table on activation                                                |
// +---------------------------------------------------------------------------+

function whcf_install () {

   global $wpdb;

       $table_name = $wpdb->prefix . "contacttable";

   //create database table

	   $sql = "CREATE TABLE IF NOT EXISTS " . $table_name . "(
		testid int( 15 ) NOT NULL AUTO_INCREMENT ,
		contact_name text,
		email text,
		phoneno float(50),
		messagesubject text,
		messagedata text,
		lead_published 	int(11),
		PRIMARY KEY ( `testid`)
		) ";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	 dbDelta($sql);
	  add_option("whcf_version", "3.0.0");
       $insert = "INSERT INTO " . $table_name .
            " (contact_name,email,phoneno,messagesubject,messagedata,lead_published) " .
            "VALUES ('Test','test@adodis.com',9739174499,'welcome to contact us','Thank you installing contact plug-in','1')";

      $results = $wpdb->query( $insert );

	// insert default settings into wp_options
	$toptions = $wpdb->prefix ."options";
	$insert = "INSERT INTO ".$toptions.
		"(option_name, option_value) " .
		"VALUES ('whcf_admng', 'update_plugins'),('whcf_deldata', ''),".
		"('whcf_copyright', ''),".
		"('whcf_captcha_key', '1'),('whcf_database_insert', '1')";
	     $solu = $wpdb->query( $insert );
}


//action to include javascript file

add_action('wp_print_scripts', 'WPWall_ScriptsAction');

function WPWall_ScriptsAction()
{
 $wp_wall_plugin_url = trailingslashit( get_bloginfo('wpurl') ).PLUGINDIR.'/'. dirname( plugin_basename(__FILE__) );

 if (!is_admin())
	{
	  wp_enqueue_script('wp_wall_script', $wp_wall_plugin_url.'/validate.js');
	}
}


//contact form to display in the site

function whcf_contactform() {
?>
	<div class="wrap">
	<h2>Contact Us</h2>
	<br />
	<div id="Wpetest-form">
	<form name="myform" method="post" id="myform" onsubmit="return validateForm();" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">

    	<label for="name">Enter Your Name:</label> <input name="contact_name" id="contact_name"  type="text" size="45"><br/>

    	<label for="email">Enter Your Email:</label> <input name="email" type="text" id="email" size="45" ><br/>

    	<label for="phoneno">Enter Your PhoneNo:</label><input name="phoneno" type="text" ><br/>

    	<label for="messagesubject">Message Subject:</label> <input name="messagesubject" type="text"  size="45"><br/>

    	<label for="messagedata">Enter Your Message:</label> <textarea name="messagedata" cols="29"  rows="5"></textarea><br/><br/>

		           <?php
	                    $whcf_captcha_key = get_option('whcf_captcha_key');

	                  if($whcf_captcha_key){
	    	        ?>
	     <label for="capthcaimage">captcha image:</label> <img src="wp-content/plugins/wp-effective-contact-us/CaptchaSecurityImages.php?width=100&height=40&characters=5" align="center" /><br />
		 <label for="security_code">Security Code: </label>
		 <input id="security_code" name="security_code" type="text" /><br />

				     <?php
			         }
			         ?>

	    <input type="submit"  name="Wpe_addnew" value="Submit" /><br/>
	    <input type="reset"  name="reset"><br/>
	    </form>

       <?php

	     session_start();

	      if (isset($_POST['Wpe_addnew'])) {

	          if($whcf_captcha_key){

	           if( $_SESSION['security_code'] == $_POST['security_code'] && !empty($_SESSION['security_code'] ) ) {

		            Wpe_insertnew();

		            unset($_SESSION['security_code']);
               }
	         else {

              echo "<script>alert('Sorry, you have provided an invalid security code.')</script>";

                 }
	          }

	       else{
	       	       if (isset($_POST['Wpe_addnew'])) {

	       	           Wpe_insertnew();

	            }

	          }

	       }

         $copyright = get_option('whcf_copyright');

         if($copyright){
	     ?>
	    <div style="text-align:center;font-size:10px;padding-bottom:7px;">Developed by <a href="http://www.hire-web-developers.com/">hire web developer</a></div>
      <?php
      }
	 ?>
	 </div>
	 </div>
     <?php }




//inserts contacts in to database

function Wpe_insertnew()
{
    if (isset($_POST['Wpe_addnew'])) {


    global $wpdb;
	$table_name = $wpdb->prefix . "contacttable";
	$contact_name = $wpdb->escape($_POST['contact_name']);
	$email = $wpdb->escape($_POST['email']);
    $phoneno = $wpdb->escape($_POST['phoneno']);
	$messagesubject = $wpdb->escape($_POST['messagesubject']);
	$messagedata = $_POST['messagedata'];
    $administrator_email = get_settings('admin_email');

     $whcf_database_insert = get_option('whcf_database_insert');

     if($whcf_database_insert)
     {
     	 $insert = "INSERT INTO " . $table_name .
	" (contact_name,email,phoneno,messagesubject,messagedata)" .
	"VALUES ('$contact_name','$email','$phoneno','$messagesubject','$messagedata')";
	$results = $wpdb->query( $insert );
     }

       if($administrator_email){

                   send_email_to_the_administrator();

                }
             }
          }




//function to send e-mail to the administrator

 function send_email_to_the_administrator(){

  $admin_email = get_settings('admin_email');
  $contact_name = $_POST['contact_name'];
  $client_email = $_POST['email'];
  $phoneno = $_POST['phoneno'];
  $subject = "You got a new contact message";
  $message_subject = $_POST['messagesubject'];
  $messagedata = $_POST['messagedata'];
  $client_name = $_POST['contact_name'];
  $from = $client_email;
      $body ='
      <table  width="100%" cellspacing="0" border="0">
	  <tr>
	  <td>Name:-'.$contact_name.',</td>
	  </tr>
	  <tr>
	  <td>PhoneNo:-'.$phoneno.',</td>
	  </tr>
	  <tr>
	  <td>Email:-'.$client_email.',</td>
	  </tr>
	  <tr>
	  <td>Message-Subject:-'.$message_subject.',</td>
	  </tr>
      <tr>
	  <tr>
	  <td>Message-Text:-'.$messagedata.',</td>
	  </tr>
      <tr>
	  <td>**************************</td>
	  </tr>
      <tr>
	  <td>Thanks</td>
	  </tr>
	 </table>';

    $from = $client_email;
    $headers = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-2\r\nContent-Transfer-Encoding: 8bit\r\nX-Priority: 1\r\nX-MSMail-Priority: High\r\n";
	$headers .= "From: $contact_name\r\n" . "Reply-To: $from\r\n";


   if (isset($_POST['Wpe_addnew'])) {

    if(wp_mail($admin_email, $subject, $body, $headers))
    {
        echo "<script>alert('Your Mail is sucessfully delivered Thank you for your e-mail.')</script>";

    }

    else{

        echo "<script>alert(' Could not instantiate mail function. mail cannot be sent')</script>";

        }

      }

   }




  //function to include css file

        function whcf_addcss() { // include style sheet
  	                wp_enqueue_style('Wpe_css', '/' . PLUGINDIR . '/wp-effective-contact-us/css/contact-style.css' );
                 }




// +---------------------------------------------------------------------------+
// | this function is to list all contacts in the administrator	                       |
// +---------------------------------------------------------------------------+

function Wpe_showlist() {
	global $wpdb;
    $i = 1;
	$table_name = $wpdb->prefix . "contacttable";
	$tstlist = $wpdb->get_results("SELECT contact_name,email,phoneno,messagedata,testid FROM $table_name");
    echo '<h3>List Of Contacts</h3>';
	foreach ($tstlist as $tstlist2) {
		echo '<p style="font-size:15px;font-weight:bold;";>';
		echo "["; echo $i; echo "]";
		echo '&nbsp;&nbsp;';
		echo stripslashes($tstlist2->contact_name);
		echo '&nbsp;&nbsp;';
		echo "<b style='color:#A0A0A0;'>"; echo stripslashes($tstlist2->email); echo "</b>";
		echo '&nbsp;&nbsp;';
		echo '<a href="admin.php?page=Wpe_manage&amp;mode=Wpeedit&amp;testid='.$tstlist2->testid.'">View</a>';
		echo '&nbsp;|&nbsp;';
		echo '<a href="admin.php?page=Wpe_manage&amp;mode=Wperem&amp;testid='.$tstlist2->testid.'" onClick="return confirm(\'Delete this contact?\')">Delete</a>';
		echo '&nbsp;|&nbsp;';
        echo '<a href="admin.php?page=Wpe_manage&amp;mode=Wpe_reply&amp;testid='.$tstlist2->testid.'">Reply</a>';
		echo '</p>';
		$i++;
	}
}


        function Wpe_reply_to($testid){

               ?> <br/>
                <form name="reply_to_form" method="post" id="reply_to_form"  action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">

    	        <label for="client_message">Enter Your Message:</label> <textarea name="client_message" cols="29"  rows="5"></textarea><br/><br/>

                <input type="submit"  name="Wpe_add_client" value="Submit" /><br/>

              <?php
             }



     function reply_the_person_with_email($testid){

         global $wpdb;
	     $table_name = $wpdb->prefix . "contacttable";
         $gettst2 = $wpdb->get_row("SELECT  contact_name, email FROM $table_name WHERE testid = $testid");
         $client_email = $gettst2->email;
         $administrator_email = get_settings('admin_email');
         $client_message = $_POST['client_message'];

         $from = $administrator_email;
         $headers = "MIME-Version: 1.0\r\n";
	     $headers .= "Content-type: text/html; charset=iso-8859-2\r\nContent-Transfer-Encoding: 8bit\r\nX-Priority: 1\r\nX-MSMail-Priority: High\r\n";
	     $headers .= "From: $from\r\n" . "Reply-To: $from\r\n";

         $body ='
			      <table  width="100%" cellspacing="0" border="0">
				  <tr>
				  <td>'.$client_message.',</td>
				  </tr>
			      </table>';


        if(wp_mail($client_email, $client_message, $body, $headers))
        {

        echo "<script>alert('Your Mail is sucessfully delivered.')</script>";

        }

         else{

         echo "<script>alert(' Could not instantiate mail function. mail cannot be sent')</script>";

        }

         Wpe_showlist();

      }




// +---------------------------------------------------------------------------+
// | this function is for an detail view of an contact	                       |
// +---------------------------------------------------------------------------+

function wpe_view_contacts_in_detail($testid){

	 global $wpdb;

	 $table_name = $wpdb->prefix . "contacttable";

	 $gettst2 = $wpdb->get_row("SELECT  contact_name, email, phoneno, messagesubject, messagedata FROM $table_name WHERE testid = $testid");

     echo '<div class="total">';

     echo '<h3>Detail View Of an Contact</h3>';

     echo '<table class="wide_fat" id="table_align" width="100%" cellspacing="0" border="1">';

     echo '<tr class="title_background">';

     echo '<th>Name</th>';echo '<th>Email</th>';echo '<th>PhoneNo</th>';echo '<th>MessageSubject</th>';echo '<th>MessageData</th>';

     echo '</tr>';

     echo '&nbsp;&nbsp;';

     echo '</br>';

     echo '<tr align="center;">';

     echo '<td>'.$gettst2->contact_name.'</td>';

     echo '<td>'.$gettst2->email.'</td>';

     echo '<td>'.$gettst2->phoneno.'</td>';

     echo '<td>'.$gettst2->messagesubject.'</td>';

     echo '<td>'.$gettst2->messagedata.'</td>';

     echo '</tr>';

     echo '</table>';

     echo '</div>';

   }



/* this function is to delete contacts from the Database */

function Wpe_removetst($testid) {
	global $wpdb;

	$table_name = $wpdb->prefix . "contacttable";

	$insert = "DELETE FROM " . $table_name .
	" WHERE testid = ".$testid ."";
	$results = $wpdb->query( $insert );
}


// +---------------------------------------------------------------------------+
// | Create admin links                                                        |
// +---------------------------------------------------------------------------+

function whcf_addcontacts() {

	if (get_option('whcf_admng') == '') { $Wpe_admng = 'update_plugins'; } else {$Wpe_admng = get_option('whcf_admng'); }

// Create top-level menu and appropriate sub-level menus:
	add_menu_page('Contact', 'Contact', $Wpe_admng, 'Wpe_manage', 'Wpe_adminpage', plugins_url('/wp-effective-contact-us/contact.png'));
	add_submenu_page('Wpe_manage', 'Settings', 'Settings', $Wpe_admng, 'Wpe_config', 'Wpe_options_page');
}


// +---------------------------------------------------------------------------+
// | admin page display                                   |
// +---------------------------------------------------------------------------+

function Wpe_adminpage() {
	global $wpdb;
?>
	<div class="wrap">
	<?php
	       echo '<h2>Contacts Management Page</h2>';

		if ($_REQUEST['mode']=='Wperem') {
			Wpe_removetst($_REQUEST['testid']);
			?><div id="message" class="updated fade"><p><strong><?php _e('contact Deleted'); ?>.</strong></p></div><?php
		}

        if ($_REQUEST['mode']=='Wpeedit') {
			wpe_view_contacts_in_detail($_REQUEST['testid']);
		    exit;
		}

		if (isset($_POST['Wpe_add_client'])) {

			reply_the_person_with_email($_REQUEST['testid']);
			exit;
		}


		 if ($_REQUEST['mode']=='Wpe_reply') {

			Wpe_reply_to($_REQUEST['testid']);
		         exit;
		       }

			Wpe_showlist(); // show contacts
		?>
	</div>
<?php }



// +---------------------------------------------------------------------------+
// | Configuration options for contacts                                   |
// +---------------------------------------------------------------------------+

function Wpe_options_page() {
?>
	<div class="wrap">
	<?php if ($_REQUEST['updated']=='true') { ?>
	<div id="message" class="updated fade"><p><strong>Settings Updated</strong></p></div>
	<?php  } ?>

	<h2>Contact Form Settings</h2>
     <p> (copy and paste the short-code <b> '[WH-contacts]' </b> in the post or page <br>of the administrator to display contact form  in the site).
	<form method="post" action="options.php">
	<?php wp_nonce_field('update-options'); ?>
	<?php settings_fields( 'whcf-option-group' ); ?>

	<table cellpadding="5" cellspacing="5">

	<tr>
	<td>
	    <p> Enable this option to show copyright information along with the contactform<br/>
	        (By default this option is unchecked)
	    </p>
	</td>
	<td><?php
	$whcf_copyright = get_option('whcf_copyright');
	if ($whcf_copyright == '1') { ?>
	<input type="checkbox" name="whcf_copyright" value="1" checked />
	<?php } else { ?>
	<input type="checkbox" name="whcf_copyright" value="1" />
	<?php } ?>
	</td>
	</tr>

	<tr>
	<td>
	    Check this option to Enable Captcha in the contact form <br/>
	    (if you uncheck this option then captcha is disabled in the contact form)
	</td>
	<td><?php
	$whcf_captcha_key = get_option('whcf_captcha_key');
	if ($whcf_captcha_key == '1') { ?>
	<input type="checkbox" name="whcf_captcha_key" value="1" checked />
	<?php } else { ?>
	<input type="checkbox" name="whcf_captcha_key" value="1" />
	<?php } ?>
	</td>
	</tr>

     <tr>
	  <td>
	     Check this option to store contact form values in to the database.<br/>
         (if you uncheck this option then form values are not stored in database)
	   </td>
	<td><?php
	$whcf_database_insert = get_option('whcf_database_insert');
	if ($whcf_database_insert == '1') { ?>
	<input type="checkbox" name="whcf_database_insert" value="1" checked />
	<?php } else { ?>
	<input type="checkbox" name="whcf_database_insert" value="1" />
	<?php } ?>
	</td>
	</tr>


	<tr valign="top">
	<td>check this option to Remove database table when deactivating plugin</td>
	<td>
	<?php $Whcf_deldata = get_option('whcf_deldata');
	if ($Whcf_deldata == 'yes') { ?>
	<input type="checkbox" name="whcf_deldata" value="yes" checked /> (this will result in all data being deleted!)
	<?php } else { ?>
	<input type="checkbox" name="whcf_deldata" value="yes" /> (this will result in all data being deleted!)
	<?php } ?>
	</td>
	</tr>

	</table>
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="whcf_admng, whcf_deldata" />

	<p class="submit">
	<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
	</p>

	</form>

	</div>
<?php
}



// +---------------------------------------------------------------------------+
// | Uninstall plugin                                                          |
// +---------------------------------------------------------------------------+

function whcf_deactivate () {
	global $wpdb;

	$table_name = $wpdb->prefix . "contacttable";

	$whcf_deldata = get_option('whcf_deldata');
	if ($whcf_deldata == 'yes') {
		$wpdb->query("DROP TABLE {$table_name}");
		delete_option("whcf_admng");
		delete_option("whcf_deldata");
		delete_option("whcf_copyright");
 	    delete_option("whcf_captcha_key");
 	    delete_option("whcf_database_insert");
 	}
    delete_option("whcf_version");
	unregister_whcf_options();

}

?>
