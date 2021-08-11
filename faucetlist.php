<?php
/**
* Plugin Name: Faucetlist
* Plugin URI: https://clickforcharity.net/
* Description: Faucetlist.
* Version: 1.0
* Author: 
* Author URI: 
**/

// Create a new table
function faucetlist_table(){
	global $wpdb;

	$charset_collate = $wpdb->get_charset_collate();
	$tablename = $wpdb->prefix."faucetlist";
	$sql = "CREATE TABLE $tablename (
	  id mediumint(11) NOT NULL AUTO_INCREMENT,
	  name varchar(80) NOT NULL,
	  url varchar(80) NOT NULL,
	  timer int(11),
	  last_claim int(11),
	  user int(11),
	  PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}
register_activation_hook( __FILE__, 'faucetlist_table' );

register_deactivation_hook( __FILE__, 'my_plugin_remove_database' );
function my_plugin_remove_database() {
     global $wpdb;
     $table_name = $wpdb->prefix . 'faucetlist';
     $sql = "DROP TABLE IF EXISTS $table_name";
     $wpdb->query($sql);
     delete_option("my_plugin_db_version");
}  


function app_output_buffer() {
    ob_start();
} // soi_output_buffer
add_action('init', 'app_output_buffer');

function faucet_script(){

    global $current_user;
    get_currentuserinfo();

    global $wpdb;
    $user_ID = $current_user->ID;
    $tablename = $wpdb->prefix."faucetlist";

    if(isset($_GET['redirect'])){
        
        $redirect = $_GET['redirect'];
        
    	$check = "SELECT url FROM ".$tablename." WHERE id=%d AND user = %d";
        $sql = $wpdb->prepare($check, array($redirect,$user_ID));
        $check_data = $wpdb->get_results($sql);           
        
        if(count($check_data) > 0){
            
            foreach($check_data as $entry){
                
                $url = $entry->url;
                
            	$update = "UPDATE ".$tablename." SET last_claim=%d WHERE id=%d AND user = %d";
                $sqlu = $wpdb->prepare($update, array(time(),$redirect,$user_ID));
                $wpdb->query($sqlu);                   
            
                header("Location: ".$url."");
                exit();        
            
            }
            
        }else{
                header("Location: /faucetlist/");
                exit();             
        }
    }

    ?>
    <style>
        .button,
        .button-primary,
        .button-secondary {
            display: inline-block;
            text-decoration: none;
            font-size: 13px;
            line-height: 26px;
            height: 28px;
            margin: 0;
            padding: 0 10px 1px;
            cursor: pointer;
            border-width: 1px;
            border-style: solid;
            -webkit-appearance: none;
            -webkit-border-radius: 3px;
            border-radius: 3px;
            white-space: nowrap;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
        }
        
        .button,
        .button-secondary {
            color: #555;
            border-color: #cccccc;
            background: #f7f7f7;
            -webkit-box-shadow: inset 0 1px 0 #fff, 0 1px 0 rgba(0,0,0,.08);
            box-shadow: inset 0 1px 0 #fff, 0 1px 0 rgba(0,0,0,.08);
            vertical-align: top;
        }
        
        p .button {
            vertical-align: baseline;
        }
        
        .button:hover,
        .button-secondary:hover,
        .button:focus,
        .button-secondary:focus {
            background: #fafafa;
            border-color: #999;
            color: #222;
        }
        
        .button:focus,
        .button-secondary:focus {
            -webkit-box-shadow: 1px 1px 1px rgba(0,0,0,.2);
            box-shadow: 1px 1px 1px rgba(0,0,0,.2);
        }
        
        .button:active,
        .button-secondary:active {
            background: #eee;
            border-color: #999;
            color: #333;
            -webkit-box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 );
            box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 );
        }
        
        .button-primary {
            background: #2ea2cc;
            border-color: #0074a2;
            -webkit-box-shadow: inset 0 1px 0 rgba(120,200,230,0.5), 0 1px 0 rgba(0,0,0,.15);
            box-shadow: inset 0 1px 0 rgba(120,200,230,0.5), 0 1px 0 rgba(0,0,0,.15);
            color: #fff;
            text-decoration: none;
        }
        
        .button-primary:hover,
        .button-primary:focus {
            background: #1e8cbe;
            border-color: #0074a2;
            -webkit-box-shadow: inset 0 1px 0 rgba(120,200,230,0.6);
            box-shadow: inset 0 1px 0 rgba(120,200,230,0.6);
            color: #fff;
        }
        
        .button-primary:focus {
            border-color: #0e3950;
            -webkit-box-shadow: inset 0 1px 0 rgba(120,200,230,0.6), 1px 1px 2px rgba(0,0,0,0.4);
            box-shadow: inset 0 1px 0 rgba(120,200,230,0.6), 1px 1px 2px rgba(0,0,0,0.4);
        }
        
        .button-primary:active {
            background: #1b7aa6;
            border-color: #005684;
            color: rgba(255,255,255,0.95);
            -webkit-box-shadow: inset 0 1px 0 rgba(0,0,0,0.1);
            box-shadow: inset 0 1px 0 rgba(0,0,0,0.1);
            vertical-align: top;
        }
        
        .progressBar {
          width: 200px;
          margin: 10px auto;
          height: 22px;
          background-color: #28a745;
          color: white;
        }
        
        .progressBar div {
          height: 100%;
          line-height: 22px;
          width: 0;
          background-color: #17a2b8;
          box-sizing: border-box;
          color: white;
        }        
        
    </style>
    
    <script>
    
        function progress(timeleft, timetotal, $element) {
            var progressBarWidth = timeleft * $element.width() / timetotal;
            $element.find('div').animate({ width: progressBarWidth }, timeleft == timetotal ? 0 : 1000, 'linear').html("");
            if(timeleft > 0) {
                setTimeout(function() {
                    progress(timeleft - 1, timetotal, $element);
                }, 1000);
            }else{
                $element.find('div').html("Ready");
            }
        };

    </script>
    <?

    // Add record
    if(isset($_POST['but_submit'])){
  
    	$name = $_POST['txt_name'];
    	$url = $_POST['txt_url'];
    	$timer = $_POST['txt_timer'];
    
    	if($name != '' && $url != ''){
    	    
	        $insert_sql = "INSERT INTO ".$tablename." (name,url,user,timer) values(%s,%s,%d,%d) ";
	        $sql = $wpdb->prepare($insert_sql, array($name,$url,$user_ID,$timer));
	        $wpdb->query($sql);
	        
            header("Location: /faucetlist/");
            exit();
                
    	}
    	
    }
    
    echo '
    <h1>Add New Faucet</h1>
    <form method="post" action="">
    	<table>
    		<tr>
    			<td>Name</td>
    			<td><input type="text" name="txt_name"></td>
    			<td>Timer (in minutes)</td>
    			<td><input type="text" name="txt_timer"></td>
    		</tr>
    		<tr>
    			<td>Url</td>
    			<td><input type="text" name="txt_url"></td>
                <td colspan="2"><input type="submit" name="but_submit" value="Submit"></td>
    		</tr>
    	</table>
    </form>';


    // Delete record
    if(isset($_GET['delid'])){
    	$delid = $_GET['delid'];
    	
    	$entriesList = "DELETE FROM ".$tablename." WHERE id=%d AND user=%d";
        $sql = $wpdb->prepare($entriesList, array($delid,$user_ID));
        $wpdb->query($sql);    	

    	 header("Location: /faucetlist/");
    	 exit;
    }
    
    echo '
    <h1>All Faucets</h1>
    
    <table width="100%" border="1" style="border-collapse: collapse;">
    	<tr>
    		<th>Name</th>
    		<th>Url</th>
    		<th>Timer</th>
    		<th>Action</th>
    	</tr>';
	
	// Select records
	$entriesList = "SELECT * FROM ".$tablename." WHERE user=%d order by id desc";
    $sql = $wpdb->prepare($entriesList, array($user_ID));
    $entriesList = $wpdb->get_results($sql);
	
	if(count($entriesList) > 0){
		$count = 1;
		foreach($entriesList as $entry){
		    $id = $entry->id;
		    $name = $entry->name;
		    $url = $entry->url;
		    $timer = $entry->timer;
		    $time = $entry->last_claim;
		    
		    $seconds=null;
		    $seconds = $timer*60;

            $now = time();
            
            
            $diff = $now - $time;
            $diff = $seconds-$diff;

		    echo "<tr>
		    	<td>".$name."</td>
		    	<td>".$url."</td>";
		    	
		    	
		    	if($diff>0){
		    	    
		    	    ?>
		    	    <script>
    		    	    jQuery(document).ready(function($) {
    		    	        
		    	            var id = "#timeBar_all_<?=$id;?>";
		    	            progress('<?=$diff;?>', '<?=$seconds;?>', $(id));
    		    	        
    		    	    });
		    	    </script>
		    	    <?php		    	    
    		    	echo "
    		    	<td>
    		    	
                        <center><span class='text-muted'>".$timer." min</span></center>
                        <div id='timeBar_all_".$id."' class='progressBar'>
                          <div> Ready </div>
                        </div>		    	
    		    	
    		    	
    		    	</td>";
    		    	
    		    	
		    	}else{
		    	    
		    	    ?>
		    	    <script>
    		    	    jQuery(document).ready(function($) {
    		    	        
    		    	        $('.toClaim<?=$id;?>').on('click', function(){
    		    	            var id = "#timeBar_all_" + $(this).attr("data-loopindex");
    		    	            progress('<?=$seconds;?>', '<?=$seconds;?>', $(id));
    		    	        });
    		    	        
    		    	    });
		    	    </script>
		    	    <?php

    		    	echo "
    		    	<td>
    		    	
                        <center><span class='text-muted'>".$timer." min</span></center>
                        <div id='timeBar_all_".$id."' class='progressBar'>
                          <div> Ready </div>
                        </div>		    	
    		    	
    		    	
    		    	</td>";		    	    
		    	    
		    	}
		    	
		    	echo "
		    	<td>
		    	    <a href='?page=faucetlist&delid=".$id."' class='button-secondary' onclick=\"return confirm('Are you sure you want to delete this faucet?');\">Delete</a>
		    	    <a href='?page=faucetlist&redirect=".$id."' target='_blank' data-loopindex='".$id."' data-currency='all' class='button-primary toClaim".$id."'>Claim</a>
		    	    
		    	</td>
		    </tr>";
		    $count++;
		}
		
	}else{
		echo "<tr><td colspan='5'>No record found</td></tr>";
	} 
	echo "</table>";
    
}
add_shortcode('faucet_script', 'faucet_script');