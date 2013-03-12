<?php 
define ( "IN_KEKE", TRUE );
include "app_comm.php";
$img = new Secode_class (); 
$img->use_gd_font = false;
$img->text_color = "#3399ff";
$img->image_type = 2;
$img->text_x_start = 8;
if ($img->use_gd_font) {
	$img->image_height = 45;
	$img->image_width = 175;
	$img->gd_font_size = 20;
	$img->gd_font_file = realpath ( S_ROOT .DIRECTORY_SEPARATOR. "resource/img/gdfonts/bubblebath.gdf" );
}
$img->multi_text_color = "#3399ff,#3300cc,#3333cc,#6666ff";
$img->ttf_file = realpath ( S_ROOT . DIRECTORY_SEPARATOR."resource/img/gdfonts/elephant.ttf" );
$img->use_transparent_text = true;
$img->show ();
 