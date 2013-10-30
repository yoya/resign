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

function  filterValue(&$value, $total, $maxvalue) {
    $value = (255 * 6 + $value) / 7;
}

function  filterPixel(&$red, &$green, &$blue, $maxvalue) {
    $total = $red + $green + $blue;
    filterValue($red,   $total, $maxvalue);
    filterValue($green, $total, $maxvalue);
    filterValue($blue,  $total, $maxvalue);
}


function alphabrend($data, $ext, $outfile) { // to white
    $im = ImageCreateFromString($data);


    $sx = imagesx($im);  $sy = imagesy($im);
    if (imageistruecolor($im)) { // true color
        $maxvalue = 0;
        for ($y = 0 ; $y < $sy; $y++) {
            for ($x = 0 ; $x < $sx; $x++) {
                $c = imagecolorat($im, $x, $y);
                $red   = ($c >> 16) & 0xff;
                $green = ($c >>  8) & 0xff;
                $blue  =  $c        & 0xff;
                $maxvalue = MAX($maxvalue, $red, $green, $blue);
            }
        }
        for ($y = 0 ; $y < $sy; $y++) {
            for ($x = 0 ; $x < $sx; $x++) {
                $c = imagecolorat($im, $x, $y);
                $alpha =  $c >> 24;
                $red   = ($c >> 16) & 0xff;
                $green = ($c >>  8) & 0xff;
                $blue  =  $c        & 0xff;
                filterPixel($red, $green, $blue, $maxvalue);
                $c2 = ($alpha << 24) + ($red << 16) + ($green << 8) + ($blue << 0);
                imagesetpixel($im, $x, $y, $c2);
            }
        }
    } else { // palette color
        $ct = imagecolorstotal($im);
        $maxvalue = 0;
        for ($i = 0 ; $i < $ct; $i++) {
            $c = imagecolorsforindex($im, $i);
            $red   = $c['red'];
            $green = $c['green'];
            $blue  = $c['blue'];
            $maxvalue = MAX($maxvalue, $red, $green, $blue);
        }
        for ($i = 0 ; $i < $ct; $i++) {
            $c = imagecolorsforindex($im, $i);
            $alpha = $c['alpha'];
            $red   = $c['red'];
            $green = $c['green'];
            $blue  = $c['blue'];
            filterPixel($red, $green, $blue, $maxvalue);
            if (($alpha < 0) || (PHP_VERSION_ID < 50400)) {
                imagecolorset($im, $i, $red, $green, $blue);
            } else {
                imagecolorset($im, $i, $red, $green, $blue, $alpha); // >= 5.4.0
            }
        }
    }
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
