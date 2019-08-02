<?php /* Tomo | Pratama Studio */
defined('BASEPATH') OR exit('No direct script access allowed');
class CI_Image_lib{
	public $image_library = 'gd2';
	public $library_path = '';
	public $dynamic_output = FALSE;
	public $source_image = '';
	public $new_image = '';
	public $width = '';
	public $height = '';
	public $quality = 90;
	public $create_thumb = FALSE;
	public $thumb_marker = '_thumb';
	public $maintain_ratio = TRUE;
	public $master_dim = 'auto';
	public $rotation_angle = '';
	public $x_axis = '';
	public $y_axis = '';
	public $wm_text = '';
	public $wm_type = 'text';
	public $wm_x_transp = 4;
	public $wm_y_transp = 4;
	public $wm_overlay_path = '';
	public $wm_font_path = '';
	public $wm_font_size = 17;
	public $wm_vrt_alignment = 'B';
	public $wm_hor_alignment = 'C';
	public $wm_padding = 0;
	public $wm_hor_offset = 0;
	public $wm_vrt_offset = 0;
	public $wm_shadow_distance = 2;
	public $wm_opacity = 50;
	public $source_folder = '';
	public $dest_folder = '';
	public $mime_type = '';
	public $orig_width = '';
	public $orig_height = '';
	public $image_type = '';
	public $size_str = '';
	public $full_src_path = '';
	public $full_dst_path = '';
	public $file_permissions = 0644;
	public $create_fnc = 'imagecreatetruecolor';
	public $copy_fnc = 'imagecopyresampled';
	public $error_msg = array();
	public $wm_use_truetype = FALSE;
	protected $wm_font_color = '#ffffff';
	protected $wm_shadow_color = '';
	protected $wm_use_drop_shadow = FALSE;
	public function __construct($props = array()){
		if(count($props) > 0) {
			$this->initialize($props);
		}
		log_message('info', 'Image Lib Class Initialized');
	}
	public function clear(){
		$props = array('thumb_marker', 'library_path', 'source_image', 'new_image', 'width', 'height', 'rotation_angle', 'x_axis', 'y_axis', 'wm_text', 'wm_overlay_path', 'wm_font_path', 'wm_shadow_color', 'source_folder', 'dest_folder', 'mime_type', 'orig_width', 'orig_height', 'image_type', 'size_str', 'full_src_path', 'full_dst_path');
		foreach($props as $val) {
			$this->$val = '';
		}
		$this->image_library = 'gd2';
		$this->dynamic_output = FALSE;
		$this->quality = 90;
		$this->create_thumb = FALSE;
		$this->thumb_marker = '_thumb';
		$this->maintain_ratio = TRUE;
		$this->master_dim = 'auto';
		$this->wm_type = 'text';
		$this->wm_x_transp = 4;
		$this->wm_y_transp = 4;
		$this->wm_font_size = 17;
		$this->wm_vrt_alignment = 'B';
		$this->wm_hor_alignment = 'C';
		$this->wm_padding = 0;
		$this->wm_hor_offset = 0;
		$this->wm_vrt_offset = 0;
		$this->wm_font_color = '#ffffff';
		$this->wm_shadow_distance = 2;
		$this->wm_opacity = 50;
		$this->create_fnc = 'imagecreatetruecolor';
		$this->copy_fnc = 'imagecopyresampled';
		$this->error_msg = array();
		$this->wm_use_drop_shadow = FALSE;
		$this->wm_use_truetype = FALSE;
	}
	public function initialize($props = array()){
		if(count($props) > 0) {
			foreach($props as $key => $val) {
				if(property_exists($this, $key)) {
					if(in_array($key, array('wm_font_color', 'wm_shadow_color'), TRUE)) {
						if(preg_match('/^#?([0-9a-f]{3}|[0-9a-f]{6})$/i', $val, $matches)) {
							$val = (strlen($matches[1]) === 6) ? '#' . $matches[1] : '#' . $matches[1][0] . $matches[1][0] . $matches[1][1] . $matches[1][1] . $matches[1][2] . $matches[1][2];
						} else {
							continue;
						}
					} elseif(in_array($key, array('width', 'height'), TRUE) && !ctype_digit((string)$val)) {
						continue;
					}
					$this->$key = $val;
				}
			}
		}
		if($this->source_image === '') {
			$this->set_error('imglib_source_image_required');
			return FALSE;
		}
		if(!function_exists('getimagesize')) {
			$this->set_error('imglib_gd_required_for_props');
			return FALSE;
		}
		$this->image_library = strtolower($this->image_library);
		if(($full_source_path = realpath($this->source_image)) !== FALSE) {
			$full_source_path = str_replace('\\', '/', $full_source_path);
		} else {
			$full_source_path = $this->source_image;
		}
		$x = explode('/', $full_source_path);
		$this->source_image = end($x);
		$this->source_folder = str_replace($this->source_image, '', $full_source_path);
		if(!$this->get_image_properties($this->source_folder . $this->source_image)) {
			return FALSE;
		}
		if($this->new_image === '') {
			$this->dest_image = $this->source_image;
			$this->dest_folder = $this->source_folder;
		} elseif(strpos($this->new_image, '/') === FALSE && strpos($this->new_image, '\\') === FALSE) {
			$this->dest_image = $this->new_image;
			$this->dest_folder = $this->source_folder;
		} else {
			if(!preg_match('#\.(jpg|jpeg|gif|png)$#i', $this->new_image)) {
				$this->dest_image = $this->source_image;
				$this->dest_folder = $this->new_image;
			} else {
				$x = explode('/', str_replace('\\', '/', $this->new_image));
				$this->dest_image = end($x);
				$this->dest_folder = str_replace($this->dest_image, '', $this->new_image);
			}
			$this->dest_folder = realpath($this->dest_folder) . '/';
		}
		if($this->create_thumb === FALSE OR $this->thumb_marker === '') {
			$this->thumb_marker = '';
		}
		$xp = $this->explode_name($this->dest_image);
		$filename = $xp['name'];
		$file_ext = $xp['ext'];
		$this->full_src_path = $this->source_folder . $this->source_image;
		$this->full_dst_path = $this->dest_folder . $filename . $this->thumb_marker . $file_ext;
		if($this->maintain_ratio === TRUE && ($this->width !== 0 OR $this->height !== 0)) {
			$this->image_reproportion();
		}
		if($this->width === '') {
			$this->width = $this->orig_width;
		}
		if($this->height === '') {
			$this->height = $this->orig_height;
		}
		$this->quality = trim(str_replace('%', '', $this->quality));
		if($this->quality === '' OR $this->quality === 0 OR !ctype_digit($this->quality)) {
			$this->quality = 90;
		}
		is_numeric($this->x_axis) OR $this->x_axis = 0;
		is_numeric($this->y_axis) OR $this->y_axis = 0;
		if($this->wm_overlay_path !== '') {
			$this->wm_overlay_path = str_replace('\\', '/', realpath($this->wm_overlay_path));
		}
		if($this->wm_shadow_color !== '') {
			$this->wm_use_drop_shadow = TRUE;
		} elseif($this->wm_use_drop_shadow === TRUE && $this->wm_shadow_color === '') {
			$this->wm_use_drop_shadow = FALSE;
		}
		if($this->wm_font_path !== '') {
			$this->wm_use_truetype = TRUE;
		}
		return TRUE;
	}
	public function resize(){
		$protocol = ($this->image_library === 'gd2') ? 'image_process_gd' : 'image_process_' . $this->image_library;
		return $this->$protocol('resize');
	}
	public function crop(){
		$protocol = ($this->image_library === 'gd2') ? 'image_process_gd' : 'image_process_' . $this->image_library;
		return $this->$protocol('crop');
	}
	public function rotate(){
		$degs = array(90, 180, 270, 'vrt', 'hor');
		if($this->rotation_angle === '' OR !in_array($this->rotation_angle, $degs)) {
			$this->set_error('imglib_rotation_angle_required');
			return FALSE;
		}
		if($this->rotation_angle === 90 OR $this->rotation_angle === 270) {
			$this->width = $this->orig_height;
			$this->height = $this->orig_width;
		} else {
			$this->width = $this->orig_width;
			$this->height = $this->orig_height;
		}
		if($this->image_library === 'imagemagick' OR $this->image_library === 'netpbm') {
			$protocol = 'image_process_' . $this->image_library;
			return $this->$protocol('rotate');
		}
		return ($this->rotation_angle === 'hor' OR $this->rotation_angle === 'vrt') ? $this->image_mirror_gd() : $this->image_rotate_gd();
	}
	public function image_process_gd($action = 'resize'){
		$v2_override = FALSE;
		if($this->dynamic_output === FALSE && $this->orig_width === $this->width && $this->orig_height === $this->height) {
			if($this->source_image !== $this->new_image && @copy($this->full_src_path, $this->full_dst_path)) {
				chmod($this->full_dst_path, $this->file_permissions);
			}
			return TRUE;
		}
		if($action === 'crop') {
			$this->orig_width = $this->width;
			$this->orig_height = $this->height;
			if($this->gd_version() !== FALSE) {
				$gd_version = str_replace('0', '', $this->gd_version());
				$v2_override = ($gd_version == 2);
			}
		} else {
			$this->x_axis = 0;
			$this->y_axis = 0;
		}
		if(!($src_img = $this->image_create_gd())) {
			return FALSE;
		}
		if($this->image_library === 'gd2' && function_exists('imagecreatetruecolor')) {
			$create = 'imagecreatetruecolor';
			$copy = 'imagecopyresampled';
		} else {
			$create = 'imagecreate';
			$copy = 'imagecopyresized';
		}
		$dst_img = $create($this->width, $this->height);
		if($this->image_type === 3) {
			imagealphablending($dst_img, FALSE);
			imagesavealpha($dst_img, TRUE);
		}
		$copy($dst_img, $src_img, 0, 0, $this->x_axis, $this->y_axis, $this->width, $this->height, $this->orig_width, $this->orig_height);
		if($this->dynamic_output === TRUE) {
			$this->image_display_gd($dst_img);
		} elseif(!$this->image_save_gd($dst_img)) {
			return FALSE;
		}
		imagedestroy($dst_img);
		imagedestroy($src_img);
		chmod($this->full_dst_path, $this->file_permissions);
		return TRUE;
	}
	public function image_process_imagemagick($action = 'resize'){
		if($this->library_path === '') {
			$this->set_error('imglib_libpath_invalid');
			return FALSE;
		}
		if(!preg_match('/convert$/i', $this->library_path)) {
			$this->library_path = rtrim($this->library_path, '/') . '/convert';
		}
		$cmd = $this->library_path . ' -quality ' . $this->quality;
		if($action === 'crop') {
			$cmd .= ' -crop ' . $this->width . 'x' . $this->height . '+' . $this->x_axis . '+' . $this->y_axis;
		} elseif($action === 'rotate') {
			$cmd .= ($this->rotation_angle === 'hor' OR $this->rotation_angle === 'vrt') ? ' -flop' : ' -rotate ' . $this->rotation_angle;
		} else {
			if($this->maintain_ratio === TRUE) {
				$cmd .= ' -resize ' . $this->width . 'x' . $this->height;
			} else {
				$cmd .= ' -resize ' . $this->width . 'x' . $this->height . '\!';
			}
		}
		$cmd .= ' ' . escapeshellarg($this->full_src_path) . ' ' . escapeshellarg($this->full_dst_path) . ' 2>&1';
		$retval = 1;
		if(function_usable('exec')) {
			@exec($cmd, $output, $retval);
		}
		if($retval > 0) {
			$this->set_error('imglib_image_process_failed');
			return FALSE;
		}
		chmod($this->full_dst_path, $this->file_permissions);
		return TRUE;
	}
	public function image_process_netpbm($action = 'resize'){
		if($this->library_path === '') {
			$this->set_error('imglib_libpath_invalid');
			return FALSE;
		}
		switch($this->image_type) {
			case 1 :
				$cmd_in = 'giftopnm';
				$cmd_out = 'ppmtogif';
				break;
			case 2 :
				$cmd_in = 'jpegtopnm';
				$cmd_out = 'ppmtojpeg';
				break;
			case 3 :
				$cmd_in = 'pngtopnm';
				$cmd_out = 'ppmtopng';
				break;
		}
		if($action === 'crop') {
			$cmd_inner = 'pnmcut -left ' . $this->x_axis . ' -top ' . $this->y_axis . ' -width ' . $this->width . ' -height ' . $this->height;
		} elseif($action === 'rotate') {
			switch($this->rotation_angle) {
				case 90:
					$angle = 'r270';
					break;
				case 180:
					$angle = 'r180';
					break;
				case 270:
					$angle = 'r90';
					break;
				case 'vrt':
					$angle = 'tb';
					break;
				case 'hor':
					$angle = 'lr';
					break;
			}
			$cmd_inner = 'pnmflip -' . $angle . ' ';
		} else {
			$cmd_inner = 'pnmscale -xysize ' . $this->width . ' ' . $this->height;
		}
		$cmd = $this->library_path . $cmd_in . ' ' . $this->full_src_path . ' | ' . $cmd_inner . ' | ' . $cmd_out . ' > ' . $this->dest_folder . 'netpbm.tmp';
		$retval = 1;
		if(function_usable('exec')) {
			@exec($cmd, $output, $retval);
		}
		if($retval > 0) {
			$this->set_error('imglib_image_process_failed');
			return FALSE;
		}
		copy($this->dest_folder . 'netpbm.tmp', $this->full_dst_path);
		unlink($this->dest_folder . 'netpbm.tmp');
		chmod($this->full_dst_path, $this->file_permissions);
		return TRUE;
	}
	public function image_rotate_gd(){
		if(!($src_img = $this->image_create_gd())) {
			return FALSE;
		}
		$white = imagecolorallocate($src_img, 255, 255, 255);
		$dst_img = imagerotate($src_img, $this->rotation_angle, $white);
		if($this->dynamic_output === TRUE) {
			$this->image_display_gd($dst_img);
		} elseif(!$this->image_save_gd($dst_img)) {
			return FALSE;
		}
		imagedestroy($dst_img);
		imagedestroy($src_img);
		chmod($this->full_dst_path, $this->file_permissions);
		return TRUE;
	}
	public function image_mirror_gd(){
		if(!$src_img = $this->image_create_gd()) {
			return FALSE;
		}
		$width = $this->orig_width;
		$height = $this->orig_height;
		if($this->rotation_angle === 'hor') {
			for($i = 0; $i < $height; $i++) {
				$left = 0;
				$right = $width - 1;
				while($left < $right) {
					$cl = imagecolorat($src_img, $left, $i);
					$cr = imagecolorat($src_img, $right, $i);
					imagesetpixel($src_img, $left, $i, $cr);
					imagesetpixel($src_img, $right, $i, $cl);
					$left++;
					$right--;
				}
			}
		} else {
			for($i = 0; $i < $width; $i++) {
				$top = 0;
				$bottom = $height - 1;
				while($top < $bottom) {
					$ct = imagecolorat($src_img, $i, $top);
					$cb = imagecolorat($src_img, $i, $bottom);
					imagesetpixel($src_img, $i, $top, $cb);
					imagesetpixel($src_img, $i, $bottom, $ct);
					$top++;
					$bottom--;
				}
			}
		}
		if($this->dynamic_output === TRUE) {
			$this->image_display_gd($src_img);
		} elseif(!$this->image_save_gd($src_img)) {
			return FALSE;
		}
		imagedestroy($src_img);
		chmod($this->full_dst_path, $this->file_permissions);
		return TRUE;
	}
	public function watermark(){
		return ($this->wm_type === 'overlay') ? $this->overlay_watermark() : $this->text_watermark();
	}
	public function overlay_watermark(){
		if(!function_exists('imagecolortransparent')) {
			$this->set_error('imglib_gd_required');
			return FALSE;
		}
		$this->get_image_properties();
		$props = $this->get_image_properties($this->wm_overlay_path, TRUE);
		$wm_img_type = $props['image_type'];
		$wm_width = $props['width'];
		$wm_height = $props['height'];
		$wm_img = $this->image_create_gd($this->wm_overlay_path, $wm_img_type);
		$src_img = $this->image_create_gd($this->full_src_path);
		$this->wm_vrt_alignment = strtoupper($this->wm_vrt_alignment[0]);
		$this->wm_hor_alignment = strtoupper($this->wm_hor_alignment[0]);
		if($this->wm_vrt_alignment === 'B') $this->wm_vrt_offset = $this->wm_vrt_offset * -1;
		if($this->wm_hor_alignment === 'R') $this->wm_hor_offset = $this->wm_hor_offset * -1;
		$x_axis = $this->wm_hor_offset + $this->wm_padding;
		$y_axis = $this->wm_vrt_offset + $this->wm_padding;
		if($this->wm_vrt_alignment === 'M') {
			$y_axis += ($this->orig_height / 2) - ($wm_height / 2);
		} elseif($this->wm_vrt_alignment === 'B') {
			$y_axis += $this->orig_height - $wm_height;
		}
		if($this->wm_hor_alignment === 'C') {
			$x_axis += ($this->orig_width / 2) - ($wm_width / 2);
		} elseif($this->wm_hor_alignment === 'R') {
			$x_axis += $this->orig_width - $wm_width;
		}
		if($wm_img_type === 3 && function_exists('imagealphablending')) {
			@imagealphablending($src_img, TRUE);
		}
		$rgba = imagecolorat($wm_img, $this->wm_x_transp, $this->wm_y_transp);
		$alpha = ($rgba & 0x7F000000) >> 24;
		if($alpha > 0) {
			imagecopy($src_img, $wm_img, $x_axis, $y_axis, 0, 0, $wm_width, $wm_height);
		} else {
			imagecolortransparent($wm_img, imagecolorat($wm_img, $this->wm_x_transp, $this->wm_y_transp));
			imagecopymerge($src_img, $wm_img, $x_axis, $y_axis, 0, 0, $wm_width, $wm_height, $this->wm_opacity);
		}
		if($this->image_type === 3) {
			imagealphablending($src_img, FALSE);
			imagesavealpha($src_img, TRUE);
		}
		if($this->dynamic_output === TRUE) {
			$this->image_display_gd($src_img);
		} elseif(!$this->image_save_gd($src_img)) {
			return FALSE;
		}
		imagedestroy($src_img);
		imagedestroy($wm_img);
		return TRUE;
	}
	public function text_watermark(){
		if(!($src_img = $this->image_create_gd())) {
			return FALSE;
		}
		if($this->wm_use_truetype === TRUE && !file_exists($this->wm_font_path)) {
			$this->set_error('imglib_missing_font');
			return FALSE;
		}
		$this->get_image_properties();
		if($this->wm_vrt_alignment === 'B') {
			$this->wm_vrt_offset = $this->wm_vrt_offset * -1;
		}
		if($this->wm_hor_alignment === 'R') {
			$this->wm_hor_offset = $this->wm_hor_offset * -1;
		}
		if($this->wm_use_truetype === TRUE) {
			if(empty($this->wm_font_size)) {
				$this->wm_font_size = 17;
			}
			if(function_exists('imagettfbbox')) {
				$temp = imagettfbbox($this->wm_font_size, 0, $this->wm_font_path, $this->wm_text);
				$temp = $temp[2] - $temp[0];
				$fontwidth = $temp / strlen($this->wm_text);
			} else {
				$fontwidth = $this->wm_font_size - ($this->wm_font_size / 4);
			}
			$fontheight = $this->wm_font_size;
			$this->wm_vrt_offset += $this->wm_font_size;
		} else {
			$fontwidth = imagefontwidth($this->wm_font_size);
			$fontheight = imagefontheight($this->wm_font_size);
		}
		$x_axis = $this->wm_hor_offset + $this->wm_padding;
		$y_axis = $this->wm_vrt_offset + $this->wm_padding;
		if($this->wm_use_drop_shadow === FALSE) {
			$this->wm_shadow_distance = 0;
		}
		$this->wm_vrt_alignment = strtoupper($this->wm_vrt_alignment[0]);
		$this->wm_hor_alignment = strtoupper($this->wm_hor_alignment[0]);
		if($this->wm_vrt_alignment === 'M') {
			$y_axis += ($this->orig_height / 2) + ($fontheight / 2);
		} elseif($this->wm_vrt_alignment === 'B') {
			$y_axis += $this->orig_height - $fontheight - $this->wm_shadow_distance - ($fontheight / 2);
		}
		if($this->wm_hor_alignment === 'R') {
			$x_axis += $this->orig_width - ($fontwidth * strlen($this->wm_text)) - $this->wm_shadow_distance;
		} elseif($this->wm_hor_alignment === 'C') {
			$x_axis += floor(($this->orig_width - ($fontwidth * strlen($this->wm_text))) / 2);
		}
		if($this->wm_use_drop_shadow) {
			$x_shad = $x_axis + $this->wm_shadow_distance;
			$y_shad = $y_axis + $this->wm_shadow_distance;
			$drp_color = str_split(substr($this->wm_shadow_color, 1, 6), 2);
			$drp_color = imagecolorclosest($src_img, hexdec($drp_color[0]), hexdec($drp_color[1]), hexdec($drp_color[2]));
			if($this->wm_use_truetype) {
				imagettftext($src_img, $this->wm_font_size, 0, $x_shad, $y_shad, $drp_color, $this->wm_font_path, $this->wm_text);
			} else {
				imagestring($src_img, $this->wm_font_size, $x_shad, $y_shad, $this->wm_text, $drp_color);
			}
		}
		$txt_color = str_split(substr($this->wm_font_color, 1, 6), 2);
		$txt_color = imagecolorclosest($src_img, hexdec($txt_color[0]), hexdec($txt_color[1]), hexdec($txt_color[2]));
		if($this->wm_use_truetype) {
			imagettftext($src_img, $this->wm_font_size, 0, $x_axis, $y_axis, $txt_color, $this->wm_font_path, $this->wm_text);
		} else {
			imagestring($src_img, $this->wm_font_size, $x_axis, $y_axis, $this->wm_text, $txt_color);
		}
		if($this->image_type === 3) {
			imagealphablending($src_img, FALSE);
			imagesavealpha($src_img, TRUE);
		}
		if($this->dynamic_output === TRUE) {
			$this->image_display_gd($src_img);
		} else {
			$this->image_save_gd($src_img);
		}
		imagedestroy($src_img);
		return TRUE;
	}
	public function image_create_gd($path = '', $image_type = ''){
		if($path === '') {
			$path = $this->full_src_path;
		}
		if($image_type === '') {
			$image_type = $this->image_type;
		}
		switch($image_type) {
			case 1:
				if(!function_exists('imagecreatefromgif')) {
					$this->set_error(array('imglib_unsupported_imagecreate', 'imglib_gif_not_supported'));
					return FALSE;
				}
				return imagecreatefromgif($path);
			case 2:
				if(!function_exists('imagecreatefromjpeg')) {
					$this->set_error(array('imglib_unsupported_imagecreate', 'imglib_jpg_not_supported'));
					return FALSE;
				}
				return imagecreatefromjpeg($path);
			case 3:
				if(!function_exists('imagecreatefrompng')) {
					$this->set_error(array('imglib_unsupported_imagecreate', 'imglib_png_not_supported'));
					return FALSE;
				}
				return imagecreatefrompng($path);
			default:
				$this->set_error(array('imglib_unsupported_imagecreate'));
				return FALSE;
		}
	}
	public function image_save_gd($resource){
		switch($this->image_type) {
			case 1:
				if(!function_exists('imagegif')) {
					$this->set_error(array('imglib_unsupported_imagecreate', 'imglib_gif_not_supported'));
					return FALSE;
				}
				if(!@imagegif($resource, $this->full_dst_path)) {
					$this->set_error('imglib_save_failed');
					return FALSE;
				}
				break;
			case 2:
				if(!function_exists('imagejpeg')) {
					$this->set_error(array('imglib_unsupported_imagecreate', 'imglib_jpg_not_supported'));
					return FALSE;
				}
				if(!@imagejpeg($resource, $this->full_dst_path, $this->quality)) {
					$this->set_error('imglib_save_failed');
					return FALSE;
				}
				break;
			case 3:
				if(!function_exists('imagepng')) {
					$this->set_error(array('imglib_unsupported_imagecreate', 'imglib_png_not_supported'));
					return FALSE;
				}
				if(!@imagepng($resource, $this->full_dst_path)) {
					$this->set_error('imglib_save_failed');
					return FALSE;
				}
				break;
			default:
				$this->set_error(array('imglib_unsupported_imagecreate'));
				return FALSE;
				break;
		}
		return TRUE;
	}
	public function image_display_gd($resource){
		header('Content-Disposition: filename=' . $this->source_image . ';');
		header('Content-Type: ' . $this->mime_type);
		header('Content-Transfer-Encoding: binary');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
		switch($this->image_type) {
			case 1    :
				imagegif($resource);
				break;
			case 2    :
				imagejpeg($resource, NULL, $this->quality);
				break;
			case 3    :
				imagepng($resource);
				break;
			default:
				echo 'Unable to display the image';
				break;
		}
	}
	public function image_reproportion(){
		if(($this->width === 0 && $this->height === 0) OR $this->orig_width === 0 OR $this->orig_height === 0 OR (!ctype_digit((string)$this->width) && !ctype_digit((string)$this->height)) OR !ctype_digit((string)$this->orig_width) OR !ctype_digit((string)$this->orig_height)) {
			return;
		}
		$this->width = (int)$this->width;
		$this->height = (int)$this->height;
		if($this->master_dim !== 'width' && $this->master_dim !== 'height') {
			if($this->width > 0 && $this->height > 0) {
				$this->master_dim = ((($this->orig_height / $this->orig_width) - ($this->height / $this->width)) < 0) ? 'width' : 'height';
			} else {
				$this->master_dim = ($this->height === 0) ? 'width' : 'height';
			}
		} elseif(($this->master_dim === 'width' && $this->width === 0) OR ($this->master_dim === 'height' && $this->height === 0)) {
			return;
		}
		if($this->master_dim === 'width') {
			$this->height = (int)ceil($this->width * $this->orig_height / $this->orig_width);
		} else {
			$this->width = (int)ceil($this->orig_width * $this->height / $this->orig_height);
		}
	}
	public function get_image_properties($path = '', $return = FALSE){
		if($path === '') {
			$path = $this->full_src_path;
		}
		if(!file_exists($path)) {
			$this->set_error('imglib_invalid_path');
			return FALSE;
		}
		$vals = getimagesize($path);
		if($vals === FALSE) {
			$this->set_error('imglib_invalid_image');
			return FALSE;
		}
		$types = array(1 => 'gif', 2 => 'jpeg', 3 => 'png');
		$mime = isset($types[$vals[2]]) ? 'image/' . $types[$vals[2]] : 'image/jpg';
		if($return === TRUE) {
			return array('width' => $vals[0], 'height' => $vals[1], 'image_type' => $vals[2], 'size_str' => $vals[3], 'mime_type' => $mime);
		}
		$this->orig_width = $vals[0];
		$this->orig_height = $vals[1];
		$this->image_type = $vals[2];
		$this->size_str = $vals[3];
		$this->mime_type = $mime;
		return TRUE;
	}
	public function size_calculator($vals){
		if(!is_array($vals)) {
			return;
		}
		$allowed = array('new_width', 'new_height', 'width', 'height');
		foreach($allowed as $item) {
			if(empty($vals[$item])) {
				$vals[$item] = 0;
			}
		}
		if($vals['width'] === 0 OR $vals['height'] === 0) {
			return $vals;
		}
		if($vals['new_width'] === 0) {
			$vals['new_width'] = ceil($vals['width'] * $vals['new_height'] / $vals['height']);
		} elseif($vals['new_height'] === 0) {
			$vals['new_height'] = ceil($vals['new_width'] * $vals['height'] / $vals['width']);
		}
		return $vals;
	}
	public function explode_name($source_image){
		$ext = strrchr($source_image, '.');
		$name = ($ext === FALSE) ? $source_image : substr($source_image, 0, -strlen($ext));
		return array('ext' => $ext, 'name' => $name);
	}
	public function gd_loaded(){
		if(!extension_loaded('gd')) {
			return (function_exists('dl') && @dl('gd.so'));
		}
		return TRUE;
	}
	public function gd_version(){
		if(function_exists('gd_info')) {
			$gd_version = @gd_info();
			return preg_replace('/\D/', '', $gd_version['GD Version']);
		}
		return FALSE;
	}
	public function set_error($msg){
		$CI =& get_instance();
		$CI->lang->load('imglib');
		if(is_array($msg)) {
			foreach($msg as $val) {
				$msg = ($CI->lang->line($val) === FALSE) ? $val : $CI->lang->line($val);
				$this->error_msg[] = $msg;
				log_message('error', $msg);
			}
		} else {
			$msg = ($CI->lang->line($msg) === FALSE) ? $msg : $CI->lang->line($msg);
			$this->error_msg[] = $msg;
			log_message('error', $msg);
		}
	}
	public function display_errors($open = '<p>', $close = '</p>'){
		return (count($this->error_msg) > 0) ? $open . implode($close . $open, $this->error_msg) . $close : '';
	}
}
