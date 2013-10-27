<?php

function image_data2id($data, $ext) { // 16 chars digest
    $sha1 = sha1($data, true);
    $base64 = base64_encode(substr($sha1, 0, 12));
    return strtr($base64, '+/', '-_').'.'.$ext;
}

function image_id2dir($id) {
    return "img/".substr($id, 0, 2);
}

function image_id2path($id) {
   return image_id2dir($id)."/".$id; 
}
function image_id2origpath($id) {
   return image_id2dir($id)."/_".$id; 
}

function image_id_valid($id) {
    if (preg_match('/^[A-Za-z0-9\-\_]{16}\.(png|gif|jpg)$/', $id, $dummy) === 1) {
        return true;
    }
    return false;
}

function alphabrend($data, $ext, $outfile) { // to white
    $im = ImageCreateFromString($data);
    imagefilter ($im, IMG_FILTER_BRIGHTNESS, 140);
    switch ($ext) {
        case 'jpg':
            imagejpeg($im, $outfile);
            break;
        case 'png':
            imagepng($im, $outfile);
            break;
        case 'gif':
            imagegif($im, $outfile);
            break;
    }
    ImageDestroy($im);
}
