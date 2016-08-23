<?php

//================================================
 // Поиск на весь экран для Dle от oxxxiss
 //-----------------------------------------------
 // Автор: oxxxiss
 //-----------------------------------------------
 // Почта: oxxxiss69@mail.ru
 //-----------------------------------------------
 // skype: oxxxiss69
 //-----------------------------------------------
 // Назначение: Быстрый поиск
//================================================

@error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

define( 'DATALIFEENGINE', true );
define( 'ROOT_DIR', substr( dirname(  __FILE__ ), 0, -12 ) );
define( 'ENGINE_DIR', ROOT_DIR . '/engine' );

include ENGINE_DIR . '/data/config.php';

date_default_timezone_set ( $config['date_adjust'] );

if( $config['http_home_url'] == "" ) {
	
	$config['http_home_url'] = explode( "engine/ajax/search2.php", $_SERVER['PHP_SELF'] );
	$config['http_home_url'] = reset( $config['http_home_url'] );
	$config['http_home_url'] = "http://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

}

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';

dle_session(); 
require_once ENGINE_DIR . '/modules/sitelogin.php';
require_once ROOT_DIR . '/language/' . $config['langs'] . '/website.lng';
if( ! $is_logged ) $member_id['user_group'] = 5;

//################# Определение групп пользователей
$user_group = get_vars( "usergroup" );

if( ! $user_group ) {
	$user_group = array ();
	
	$db->query( "SELECT * FROM " . USERPREFIX . "_usergroups ORDER BY id ASC" );
	
	while ( $row = $db->get_row() ) {
		
		$user_group[$row['id']] = array ();
		
		foreach ( $row as $key => $value ) {
			$user_group[$row['id']][$key] = stripslashes($value);
		}
	
	}
	set_vars( "usergroup", $user_group );
	$db->free();
}

if( !$config['fast_search'] OR !$user_group[$member_id['user_group']]['allow_search'] ) die( "error" );

//####################################################################################################################
//                    Определение категорий и их параметры
//####################################################################################################################
$cat_info = get_vars( "category" );

if( ! is_array( $cat_info ) ) {
	$cat_info = array ();
	
	$db->query( "SELECT * FROM " . PREFIX . "_category ORDER BY posi ASC" );
	while ( $row = $db->get_row() ) {
		
		$cat_info[$row['id']] = array ();
		
		foreach ( $row as $key => $value ) {
			$cat_info[$row['id']][$key] = stripslashes( $value );
		}
	
	}
	set_vars( "category", $cat_info );
	$db->free();
}

$query = $db->safesql( htmlspecialchars ( trim(  strip_tags (convert_unicode( $_POST['query'], $config['charset'] ) ) ), ENT_QUOTES, $config['charset']) );

if( $query == "" ) die();

$buffer = "";

$_TIME = time ();
$this_date = date( "Y-m-d H:i:s", $_TIME );
if( $config['no_date'] AND !$config['news_future'] ) $this_date = " AND " . PREFIX . "_post.date < '" . $this_date . "'"; else $this_date = "";

$db->query("SELECT id, short_story, title, date, alt_name, category FROM " . PREFIX . "_post WHERE " . PREFIX . "_post.approve=1".$this_date." AND (short_story LIKE '%{$query}%' OR full_story LIKE '%{$query}%' OR xfields LIKE '%{$query}%' OR title LIKE '%{$query}%') ORDER by date DESC LIMIT 5");

while($row = $db->get_row()){

		$row['date'] = strtotime( $row['date'] );
		$row['category'] = intval( $row['category'] );

		if( $config['allow_alt_url'] ) {
			
			if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
				
				if( $row['category'] and $config['seo_type'] == 2 ) {
					
					$full_link = $config['http_home_url'] . get_url( $row['category'] ) . "/" . $row['id'] . "-" . $row['alt_name'] . ".html";
				
				} else {
					
					$full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";
				
				}
			
			} else {
				
				$full_link = $config['http_home_url'] . date( 'Y/m/d/', $row['date'] ) . $row['alt_name'] . ".html";
			}
		
		} else {
			
			$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['id'];
		
		}

		$row['title'] = stripslashes($row['title']);

		if( dle_strlen( $row['title'], $config['charset'] ) > 43 ) $title = dle_substr( $row['title'], 0, 43, $config['charset'] ) . " ...";
		else $title = $row['title'];

		
		$short_story = stripslashes($row['short_story']);
		$images = array();
		preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $short_story, $media);
		$data = preg_replace('/(img|src)("|\'|="|=\')(.*)/i', "$3", $media[0]);
	 
		foreach($data as $url) {
			$info = pathinfo($url);
			if (isset($info['extension'])) {
				$info['extension'] = strtolower($info['extension']);
				if (($info['extension'] == 'jpg') || ($info['extension'] == 'jpeg') || ($info['extension'] == 'gif') || ($info['extension'] == 'png')) array_push($images, $url);
			}
		}
	 
		
		if ( count($images) ) {
			$image = $url;
		} else {
			$image = $config['http_home_url'] . "templates/" . $config['skin'] . "/dleimages/no_image.jpg";
		}
		
		$row['short_story'] = trim (htmlspecialchars( strip_tags( stripslashes( str_replace( array("<br />", "&nbsp;"), " ", $row['short_story'] ) ) ), ENT_QUOTES, $config['charset'] ) );

		if( $user_group[$member_id['user_group']]['allow_hide'] ) $row['short_story'] = str_ireplace( "[hide]", "", str_ireplace( "[/hide]", "", $row['short_story']) );
		else $row['short_story'] = preg_replace ( "#\[hide\](.+?)\[/hide\]#is", "", $row['short_story'] );


		if( dle_strlen( $row['short_story'], $config['charset'] ) > 500 ) $description = dle_substr( $row['short_story'], 0, 500, $config['charset'] ) . " ...";
		else $description = $row['short_story'];

		$description = str_replace('&amp;', '&', $description);

		$description = preg_replace( "'\[attachment=(.*?)\]'si", "", $description );		
	
	   	print_r(iconv("utf-8","windows-1251",$row['full_story']));	
	    $buffer .= "<nav class=\"result-2\">";
	    $buffer .= "<img src=\"".$image."\">";
	    $buffer .= "<ul><li class=\"title-result-2\"><a href=\"" . $full_link . "\"><span class=\"searchheading\">" . stripslashes( $title ) . "</span></a></li>";
		$buffer .= "<li class=\"description-result-2\">".$description."</li>";
		$buffer .= "</ul></nav>";

}

if ( !$buffer ) $buffer .= "<span class=\"notfound\">{$lang['related_not_found']}</span>";

//$buffer .= '<span class="seperator"><a href="'.$config['http_home_url'].'?do=search&amp;mode=advanced&amp;subaction=search&amp;story='.$query.'">'.$lang['s_ffullstart'].'</a></span><br class="break" />';
        
@header( "Content-type: text/html; charset=" . $config['charset'] );
echo $buffer;

?>