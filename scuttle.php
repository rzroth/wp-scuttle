<?php
/*
Plugin Name: scuttle widget
Description: Adds a sidebar widget or shortcode to display delicious links, Shortcode is: [scuttle tags=".." [count=##] [name="..."] ]
Author: Rich Roth
Version: 1.3.1
Author URI: http://www.tnrglobal.com/plugins
*/

$svr_file = dirname(__FILE__).'/scuttle-server.php';
if(file_exists($svr_file)) {
	require_once $svr_file;
} 

/*

RLR Add skip on suppress
RLR Added proper handling for failures of any tag matchs
RLR reworked for scuttle
	http://digwp.com/2010/04/call-widget-with-shortcode/

Author: David Lynch
Author URI: http://davidlynch.org

	Copyright 2008  David Lynch (kemayo@gmail.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Using the multi-widget pattern from wp-includes/widgets.php

// This saves options and prints the widget's config form.
function widget_scuttle_control($widget_args = 1) {
	global $wp_registered_widgets;
	static $updated = false; // Whether or not we have already updated the data after a POST submit
	
	if(is_numeric($widget_args)) {
		$widget_args = array('number' => $widget_args);
	}
	$widget_args = wp_parse_args($widget_args, array('number' => -1));
	extract($widget_args, EXTR_SKIP);
	
	// Data should be stored as array:  array( number => data for that instance of the widget, ... )
	$options = get_option('widget_scuttle');
	if(!is_array($options)) {
		$options = array();
	}

	if(!$updated && !empty($_POST['sidebar'])) {
		// Tells us what sidebar to put the data in
		$sidebar = (string) $_POST['sidebar'];
		$sidebars_widgets = wp_get_sidebars_widgets();
		if(isset($sidebars_widgets[$sidebar])) {
			$this_sidebar =& $sidebars_widgets[$sidebar];
		} else {
			$this_sidebar = array();
		}
		
		foreach($this_sidebar as $_widget_id) {
			// Remove all widgets of this type from the sidebar.  We'll add the new data in a second.  This makes sure we don't get any duplicate data
			// since widget ids aren't necessarily persistent across multiple updates
			if('widget_scuttle' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number'])) {
				$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
				if(!in_array("scuttle-$widget_number", $_POST['widget-id'])) { 
					// the widget has been removed. "scuttle-$widget_number" is "{id_base}-{widget_number}
					unset($options[$widget_number]);
				}
			}
		}
		
		foreach((array)$_POST['widget-scuttle'] as $widget_number => $widget_scuttle_instance) {
			// compile data from $widget_many_instance
			$options[$widget_number] = array(
				'title' => strip_tags(stripslashes(wp_specialchars($widget_scuttle_instance['title']))),
				'username' => strip_tags(stripslashes(wp_specialchars($widget_scuttle_instance['username']))),
				'refpage' => strip_tags(stripslashes(wp_specialchars($widget_scuttle_instance['refpage']))),
				'scutserver' => strip_tags(stripslashes(wp_specialchars($widget_scuttle_instance['scutserver']))),

				'count' => (int)$widget_scuttle_instance['count'],
				'showtags' => $widget_scuttle_instance['showtags'] == 'y',
				'favicon' => $widget_scuttle_instance['favicon'] == 'y',
				'description' => $widget_scuttle_instance['description'] == 'y',
				'tags' => explode(' ', trim(strip_tags(stripslashes(wp_specialchars($widget_scuttle_instance['tags']))))),
			);
		}
		
		update_option('widget_scuttle', $options);
		$updated = true; // So that we don't go through this more than once
	}
	
	// Here we echo out the form
	if(-1 == $number) { // We echo out a template for a form which can be converted to a specific form later via JS
		$count = 10;
		$username = 'all';
		$title = 'scuttle';
		$refpage = '';
		$scutserver = SCUT_SERVER;
		$showtags = false;
		$favicon = false;
		$description = false;
		$tags = '';
		$number = '%i%';
	} else {
		$title = attribute_escape($options[$number]['title']);
		$count = attribute_escape($options[$number]['count']);
		$username = attribute_escape($options[$number]['username']);
		$title = attribute_escape($options[$number]['title']);

		$refpage = attribute_escape($options[$number]['refpage']);
		$scutserver = attribute_escape($options[$number]['scutserver']);

		$showtags = $options[$number]['showtags'];
		$favicon = $options[$number]['favicon'];
		$description = $options[$number]['description'];
		$tags = attribute_escape(implode(' ', $options[$number]['tags']));
	}
	
	// The form has inputs with names like widget-many[$number][something] so that all data for that instance of
	// the widget are stored in one $_POST variable: $_POST['widget-many'][$number]
	
	?>
	<p>
		<label for="scuttle-title-<?php echo $number; ?>">
			<?php _e('Widget title:', 'widgets'); ?>
			<input type="text" class="widefat" id="scuttle-title-<?php echo $number; ?>" name="widget-scuttle[<?php echo $number; ?>][title]" value="<?php echo $title; ?>" />
		</label><br />
		<label for="scuttle-username-<?php echo $number; ?>">
			<?php _e('scuttle login:', 'widgets'); ?>
			<input type="text" class="widefat" id="scuttle-username-<?php echo $number; ?>" name="widget-scuttle[<?php echo $number; ?>][username]" value="<?php echo $username; ?>" />
		</label><br />
		<label for="scuttle-refpage-<?php echo $number; ?>">
			<?php _e('Page to expand:', 'widgets'); ?>
			<input type="text" class="widefat" id="scuttle-refpage-<?php echo $number; ?>" name="widget-scuttle[<?php echo $number; ?>][refpage]" value="<?php echo $refpage; ?>" />
		</label><br />
		<label for="scuttle-scutserver-<?php echo $number; ?>">
			<?php _e('scuttle server:', 'widgets'); ?>
			<input type="text" class="widefat" id="scuttle-scutserver-<?php echo $number; ?>" name="widget-scuttle[<?php echo $number; ?>][scutserver]" value="<?php echo $scutserver; ?>" />
		</label><br />
		<label for="scuttle-count-<?php echo $number; ?>">
			<?php _e('Number of links:', 'widgets'); ?>
			<input type="text" class="widefat" id="scuttle-count-<?php echo $number; ?>" name="widget-scuttle[<?php echo $number; ?>][count]" value="<?php echo $count; ?>" />
		</label><br />
		<label for="scuttle-tags-<?php echo $number; ?>">
			<?php _e('Show only these tags (separated by spaces):', 'widgets'); ?>
			<textarea id="scuttle-tags-<?php echo $number; ?>" name="widget-scuttle[<?php echo $number; ?>][tags]" class="widefat" cols="15", rows="2"><?php echo $tags; ?></textarea>
		</label><br />
	</p>
	<p>
		<label for="scuttle-description-<?php echo $number; ?>">
			<input type="checkbox" class="checkbox" id="scuttle-description-<?php echo $number; ?>" name="widget-scuttle[<?php echo $number; ?>][description]" value="y" <?php if($description) { echo 'checked="checked"'; } ?> />
			<?php _e('Show description', 'widgets'); ?>
		</label><br />
		<label for="scuttle-showtags-<?php echo $number; ?>">
			<input type="checkbox" class="checkbox" id="scuttle-showtags-<?php echo $number; ?>" name="widget-scuttle[<?php echo $number; ?>][showtags]" value="y" <?php if($showtags) { echo 'checked="checked"'; } ?> />
			<?php _e('Show tags', 'widgets'); ?>
		</label><br />
		<label for="scuttle-favicon-<?php echo $number; ?>">
			<input type="checkbox" class="checkbox" id="scuttle-favicon-<?php echo $number; ?>" name="widget-scuttle[<?php echo $number; ?>][favicon]" value="y" <?php if($favicon) { echo 'checked="checked"'; } ?> />
			<?php _e('Show favicon', 'widgets'); ?>
		</label><br />
		<input type="hidden" name="widget-scuttle[<?php echo $number; ?>][submit]" id="scuttle-submit-<?php echo $number; ?>" value="1" />
	</p>
	<?php
}

// This prints the widget
function widget_scuttle($args, $widget_args = 1) {

	if(is_numeric($widget_args)) {
		$widget_args = array( 'number' => $widget_args );
	}
	$widget_args = wp_parse_args($widget_args, array('number' => -1));
	extract($widget_args, EXTR_SKIP);

	$options = get_option('widget_scuttle');

	if(!isset($options[$number])) { return; }
	$options = $options[$number];

	real_scuttle($options, $number, $args, $widget_args);
}

function real_scuttle($options, $number, $args = Array(), $widget_args = Array()) {

	extract($args, EXTR_SKIP);
	extract($widget_args, EXTR_SKIP);

#	$defaults = array('count' => 10, 'username' => 'wordpress', 
#		'title' => 'scuttle',);

	$defaults = array('count' => 10, 'username' => 'all', 
		'scutserver' => SCUT_SERVER,
		// 'title' => 'scuttle',);
	);

	foreach ($defaults as $key => $value) {
		if (!isset($options[$key]) or $options[$key] == '') {
			$options[$key] = $defaults[$key];
		}
	}
//	print __LINE__ . ":options"; print_r($options);

	$tags = false;
	if($options['tags'] && ((count($options['tags']) > 1) || 
				($options['tags'][0] != ''))) {
		$tags = $options['tags'];
	}
	$refpage = $options['refpage'];
	$preTitle = !empty($refpage) ? "<a href='$refpage'>" : "";
	$afTitle  = !empty($refpage) ? "</a>" : "";

//	$feedUrl = "http://feeds.delicious.com";
//	$servUrl = "http://delicious.com";
	
//	$feedUrl = "http://scut.thrivesmedia.com/api";
//	$servUrl = "http://scut.thrivesmedia.com/bookmarks.php";;

	$scutserver = $options['scutserver'];
	$feedUrl = $scutserver. "/api";
	$servUrl = $scutserver. "/bookmarks.php";;
	
	$json_url = $feedUrl . '/v2/json/' . rawurlencode($options['username']);

	if(isset($tags) && !empty($tags))
		$json_url .= '/' . rawurlencode(implode('+', $tags));

//	$json_url.= $tags ? '/' . rawurlencode(implode('+', $tags)) : '';

	$json_url.= '/?count=' . ((int) $options['count']) ;
		//. '&callback=makeItDelicious';
//	json_url.= "&sort=date_desc";
	$json_url.= "&sort=set_desc";	
	
	echo $before_widget;
//	$uname = $options['username'];

/*
	$refUrl = $servUrl . '/' . $options['username'];
//	print __LINE__ . ":Uname=($uname) tags=($tags)";
	if(isset($tags) && !empty($tags))
		$refUrl .= '/' . rawurlencode(implode('+', $tags));
*/
//	print "<a href='$json_url'>JSON</a>";
	$ch = curl_init($json_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$json = curl_exec($ch);

	if(!isset($json)) {
		#print_r($json);        return "OK";
		$feed = false;
	} else {
		$feed = json_decode($json);
	}

	print <<<TOP
$before_title$preTitle{$options['title']}$afTitle$after_title
TOP;

	if(!$feed) {
		error_log("Scuttle ERROR=$json_url<br>".print_r($json,true));
		print __LINE__." Scuttle ERROR at URL $json_url<br>\n";
		if(empty($tags)) 
			print "Some Tags are required\n";
		return 'Bad options';
	}
//	print "ERRORJSON="; print_r($feed); print "<br>\n";
	print <<<DIV
<div id="scuttle-box-$number" style="margin:0;padding:0;border:none;">
DIV;

	$bkLast = '';
	foreach($feed as $f) {
//		print_r($f); print "<br>\n";

		if($f->sp == 'Y') continue;  //V1: skip on suppress

//		$htitle = isset($f->n) ? 'title="'.$f->n.'"' : "";
		$htitle = 'title="' . $f->bk . ":" . $f->u . '"';

		$out = "";
		if($bkLast)
			$out = ($bkLast != $f->bk) ? "<hr style='1px' />" : "<br />";

		print <<<HTML
$out<a href="{$f->u}" class="scuttle-post" target=_blank $htitle>{$f->d}</a>
HTML;
		$bkLast = $f->bk;
	}
	print "</div>\n";
	echo $after_widget;

/*
	print <<<TOP
$before_title<a href='$refUrl' target=_blank>{$options['title']}</a>$after_title
TOP;
	?>
	<div id="scuttle-box-<?php echo $number; ?>" style="margin:0;padding:0;border:none;"> </div>
	<script type="text/javascript">
	var Delicious;
	function makeItDelicious(data) {
		Delicious = data;
	}
	</script>
	<script type="text/javascript" src="<?php echo $json_url; ?>"></script>
	<script type="text/javascript">
	function showImage(img){ return (function(){ img.style.display='inline'; }) }
	var ul = document.createElement('ul');
	for (var i=0, post; post = Delicious[i]; i++) {
		var li = document.createElement('li');
		var a = document.createElement('a');
		a.setAttribute('href', post.u);
		a.setAttribute('class', 'scuttle-post');
		a.setAttribute('target', '_blank');
		a.innerHTML = post.d;
		<?php if($options['favicon']) {?>
		var img = document.createElement('img');
		img.style.display = 'none';
		img.height = img.width = 16;
		img.src = post.u.split('/').splice(0,3).join('/')+'/favicon.ico';
		img.onload = showImage(img);
		li.appendChild(img);
		<?php }?>
		li.appendChild(a);
		<?php if($options['description']) {?>if (post.n) { li.innerHTML += ': <span class="scuttle-description">'+unescape(post.n)+'</span>' }<?php }?>
		<?php if($options['showtags']) {?>
		if (post.t.length > 0) {
			li.appendChild(document.createTextNode(' / '));
			var tags = document.createElement('span');
			tags.setAttribute('class', 'scuttle-tags');
			for(var j=0, tag; tag = post.t[j]; j++) {
				var ta = document.createElement('a');
				ta.setAttribute('href', $servUrl . '/<?php echo $options['username'];?>/'+encodeURIComponent(tag));
				ta.setAttribute('target', '_blank');
				ta.appendChild(document.createTextNode(tag));
				tags.appendChild(ta);
				tags.appendChild(document.createTextNode(' '));
			}
			li.appendChild(tags);
		}
		<?php }?>
		ul.appendChild(li);
	}
	document.getElementById('scuttle-box-<?php echo $number; ?>').appendChild(ul);
	</script>
	<noscript><a href="<?php echo $servUrl . '/' . $options['username']; ?>" target=_blank>my scuttle</a></noscript>
<?php
	echo $after_widget;
*/
}

function widget_scuttle_init() {
	if(!$options = get_option('widget_scuttle')) {
		$options = array();
	}
	
	$widget_ops = array('classname' => 'widget_scuttle', 
		'description' => __('A widget to display delicious links'));
	$control_ops = array('width' => 400, 'height' => 350, 
		'id_base' => 'scuttle');
	$name = __('scuttle');

	if(isset($options['username'])) {
		// Upgrading from an old version, pre-multi.
	     $options = array(
		1 => array(
			'title' => $options['title'],
			'username' => $options['username'],
			'count' => $options['count'],
			'showtags' => $options['showtags'],
			'favicon' => $options['favicon'],
			'description' => $options['description'],
			'tags' => $options['tags'],
		),
	     );
	     update_option('widget_scuttle', $options);
	}

	$registered = false;
    foreach(array_keys($options) as $o) {
		// Old widgets can have null values for some reason
	if(!isset($options[$o]['username'])) {
		continue;
	}

		// $id should look like {$id_base}-{$o}
	$id = "scuttle-$o"; // Never never never translate an id
	$registered = true;
	wp_register_sidebar_widget($id, $name, 'widget_scuttle', 
		$widget_ops, array('number' => $o));
	wp_register_widget_control($id, $name, 
		'widget_scuttle_control', $control_ops, 
		array('number' => $o));
    }

	// If there are none, we register the widget's existance with a generic template
	if(!$registered) {
		wp_register_sidebar_widget('scuttle-1', 
			$name, 'widget_scuttle', $widget_ops, 
			array('number' => -1));
		wp_register_widget_control('scuttle-1', $name, 
			'widget_scuttle_control', $control_ops, 
			array('number' => -1));
	}
}


function widget_scuttle_shortcode($atts) {
    
    global $wp_widget_factory;
    
    extract(shortcode_atts(array(
        'username' => FALSE,
	'tags' => FALSE,
    ), $atts, 'scuttle'));

    $atts['tags'] = Array($atts['tags']);

    $widget_name = 'WP_Widget_Scuttle';    
    $widget_name = wp_specialchars($widget_name);

//	print_r($wp_widget_factory->widgets);
//	print "W=($widget_name)";
//	print "AR=".print_r($atts);

	ob_start();
	real_scuttle($atts, 'c');
	$output = ob_get_contents();
    	ob_end_clean();
	return $output;

/*  
    if (!is_a($wp_widget_factory->widgets[$widget_name], 'WP_Widget')):
        $wp_class = 'WP_Widget_'.ucwords(strtolower($class));
        
        if (!is_a($wp_widget_factory->widgets[$wp_class], 'WP_Widget')):
            return '<p>'.sprintf(__("%s: Widget class not found. Make sure this widget exists and the class name is correct"),'<strong>'.$wp_class.'</strong>').'</p>';
        else:
            $class = $wp_class;
        endif;
    endif;
    
    ob_start();
    the_widget($widget_name, $instance, 
	array('widget_id'=>'arbitrary-instance-'.$id,
        	'before_widget' => '',
        	'after_widget' => '',
        	'before_title' => '',
        	'after_title' => ''
    ));
    $output = ob_get_contents();
    ob_end_clean();
    return "Username:($username:".$output.")";
*/    
}


add_action('widgets_init', 'widget_scuttle_init');
//add_shortcode('WP_Widget_Scuttle','widget_scuttle_shortcode'); 
add_shortcode('scuttle','widget_scuttle_shortcode'); 
