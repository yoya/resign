<?php

require('image.php');
require('yoyapdf.php');

function date2japanize($date) { // range: 0 - 99
    $from = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
    $to = array('〇', '一', '二', '三', '四', '五', '六', '七', '八', '九');
    $dates = split('-', $date);
    $dates[0] -= 1988;
    foreach (range(0, 2) as $idx) {
        $d = $dates[$idx];
        if ($d < 10) {
            $d = mb_substr($d, 1);
        } elseif ($d == 10) {
            $d = '十';
        } elseif ($d < 20) {
            $d = '十'.mb_substr($d, 1);
        } elseif (($d % 10) === 0) {
            $d = mb_substr($d, 0, 1).'十';
        } else {
            $d = mb_substr($d, 0, 1).'十'.mb_substr($d, 1);
        }
        $dates[$idx] = str_replace($from, $to, $d);
    }
    return '平成'.$dates[0].'年'.$dates[1].'月'.$dates[2].'日';
}

$type = $_REQUEST['type'];
$reason = $_REQUEST['reason'];
$resign_date = date2japanize($_REQUEST['resign_date']);
$commit_date = date2japanize($_REQUEST['commit_date']);
$mypart = $_REQUEST['mypart'];
$myname = $_REQUEST['myname'];
$campany = $_REQUEST['campany'];
$president = $_REQUEST['president'];
$note = $_REQUEST['note'];
$image_id = $_REQUEST['image_id'];
$qrcode = $_REQUEST['qrcode'];

$pdf=new YoyaPDF();
$pdf->AddSJISFont();
$pdf->AddPage();

if (($image_id !== '') && image_id_valid($image_id)) {

   $path = image_id2path($image_id);
   if (file_exists($path)) {
       $pdf->Image($path, 30, 50, 140);
   }
}
if ($qrcode === 'yes') {
   require('phpqrcode.php');
   $url = (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
   $url = substr($url, 0, strlen($url) - strlen('&ext=.pdf'));
   $tmpfile = tempnam('tmp', 'qr');

   QRcode::png($url, $tmpfile, 'L', 4, 0);
   $pdf->Image($tmpfile, 25, 220, 40, 40, 'png');
   unlink($tmpfile);
}

$pdf->SetFont('SJIS','',52);
$x = 170;
if ($type === 'wish')  {
    $pdf->TategakiText($x, 110, '退職願', 40);
} else {
    $pdf->TategakiText($x, 110, '退職届', 40);
}
$pdf->SetFont('SJIS','',20);
$x -= 20;
$pdf->TategakiText($x, 240, '私儀', 9);
$message1 = $resign_date."をもって退職致したく、";
$message2 = "ここにお願い申し上げます。";
$x -= 20;
$pdf->TategakiText($x, 30, $reason, 9);
$x -= 10;
$pdf->TategakiText($x, 30, $message1, 9);
$x -= 10;
$pdf->TategakiText($x, 30, $message2, 9);
$x -= 15;
$pdf->TategakiText($x, 115, $commit_date, 8);
$x -= 12;
$pdf->TategakiText($x, 135, $mypart, 8);

$pdf->SetFont('SJIS','',24);
$x -= 15;
$y = 290 - mb_strlen($myname)*12;
$pdf->TategakiText($x, $y, $myname, 12);

$pdf->SetFont('SJIS','',18);
if ($note !== '') {
    $pdf->TategakiText($x-15, 50, $note, 8);
    $pdf->SetFont('SJIS','',24);
    $x -= 35;
    $pdf->TategakiText($x, 35, $campany, 10);
    $x -= 15;
    $pdf->TategakiText($x, 35, $president, 10);
    
} else {
    $pdf->SetFont('SJIS','',24);
    $x -= 30;
    $pdf->TategakiText($x, 35, $campany, 10);
    $x -= 15;
    $pdf->TategakiText($x, 35, $president, 10);
}
header('Content-Type: application/pdf;');
header('Content-Disposition: attachment; filename=resign.pdf');

$pdf->Output();
exit(0);
