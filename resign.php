<?php

$query_string = $_SERVER['QUERY_STRING'];
//var_dump($query_string);

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

function build_queryparam($params) {
   foreach ($params as $key => $value) {    
       $param_peers[] = urlencode($key)."=".urlencode($value);
   }
   return join('&', $param_peers);
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

if (isset($_FILES['image_file']) && ($_FILES['image_file'] !== '')) {
   $image_file = $_FILES['image_file'];
   $tmp_name = $image_file['tmp_name'];
   $image_data = file_get_contents($tmp_name);
   if (strncmp($image_data, "\xff\xd8\xff", 3) == 0) {
       $ext = 'jpg';
   } else if (strncmp($image_data, "\x89PNG", 4) == 0) {
       $ext = 'png';
   } else if (strncmp($image_data, 'GIF', 3) == 0) {
       $ext = 'gif';
   }
   $image_id = image_data2id($image_data, $ext);
   $dir = image_id2dir($image_id);
   if (file_exists($dir) === false) {
        mkdir($dir);
   }
   $origpath = image_id2origpath($image_id);
   $path = image_id2path($image_id);
   if (file_exists($path) === false) {
       file_put_contents($orig_path, $image_data);
        alphabrend($image_data, $ext, $path);
   }
   //
   $params = $_GET;
   $params['image_id'] = $image_id;
   $params['do'] = 'make';
   $param_str = build_queryparam($params);
   header("Location: ?$param_str");
   exit(0);
} elseif (isset($_REQUEST['ext']) && ($_REQUEST['ext'] === '.pdf')) {
    function date2japanize($date) {
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

//    echo "$resign_date"; exit(0)
    require('yoyapdf.php');
    $pdf=new YoyaPDF();
    $pdf->AddSJISFont(); // XXX
    $pdf->AddPage();

    if (($image_id !== '') && image_id_valid($image_id)) {
       $path = image_id2path($image_id);
       if (file_exists($path)) {
           $pdf->RotatedImage($path, 30, 50, 140, 200,0);
       }
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
    header('Content-type: application/pdf;');
    $pdf->Output();
    exit(0);
}

echo <<< HEAD
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title> 退職届け PDF メーカー </title>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">
<link href="http://getbootstrap.com/examples/jumbotron/jumbotron.css" rel="stylesheet">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
</head>

<body style="background-color:#f0ffe0; ">

    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="?">退職届け PDF メーカー</a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li><a href="#">Home</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#contact">Contact</a></li>
            </li>
          </ul>
        </div><!--/.navbar-collapse -->
      </div>
    </div>

<h1 align="center" id="home"> 退職届け PDF メーカー </h1>

<table width="100%" height="100%">
<tr><td align="center">
<form>
<hr>
<p>
<input type="submit" name="do" value="make" class="btn btn-primary" />&nbsp;&nbsp;<input type="submit" name="do" value="reset" class="btn btn-warning" />
</p>
<table border="1">
HEAD;
$t = time();
$resign_date = date("Y-m-d", $t + 14 * 24 * 3600);
$commit_date = date("Y-m-d", $t);

$form = array(
    array('種類', 'radio', 'type', 'notification', 'notification', '退職届', 'wish', '退職願'),
    array('理由', 'textarea', 'reason', 'このたび一身上の都合により、'),
    array('退職日', 'date', 'resign_date', $resign_date),
    array('提出日', 'date', 'commit_date', $commit_date),
    array('所属部署', 'textarea', 'mypart', '庶務二課'),
    array('自分の名前', 'text', 'myname', ''),
    array('会社名', 'text', 'campany', 'ダミー株式会社'),
    array('社長', 'text', 'president', '代表取締役 山田太郎様'),
    array('画像ID', 'text', 'image_id', ''),
    array('申し送り', 'textarea', 'note', ''),
);

if (isset($_REQUEST['do']) && ($_REQUEST['do'] === 'make')) {
    foreach ($form as $idx => $form_elem) {
        list($title, $type, $label, $value) = $form_elem;
        if (array_key_exists($label, $_REQUEST)) {
            $form[$idx][3] = $_REQUEST[$label];
        }
    }
}

foreach ($form as $form_elem) {
    list($title, $type, $label, $value) = $form_elem;
    if ($type === 'textarea') {
echo "<tr><th>$title</th> <td>
<textarea name=\"$label\" rows=\"4\" cols=\"30\" style=\"width:100%\">$value</textarea></td></tr>\n";
    } elseif ($type === 'radio') {
        $chcked_value = $form_elem[3];
        $type_list = array_slice($form_elem, 4);
        echo "<tr><th>$title</th> <td>";
        for ($i = 0 ; $i < count($type_list) ; $i+= 2) {
            $value = $type_list[$i];
            $name = $type_list[$i+1];
            if ($value === $chcked_value) {
                echo "<input type=\"radio\" name=\"$label\" value=\"$value\" checked>$name\n";
            } else {
                echo "<input type=\"radio\" name=\"$label\" value=\"$value\">$name\n";
            }
        }
        echo " </td></tr>\n";
    } else {
        echo "<tr><th>$title</th>
<td><input type=\"$type\" name=\"$label\" value=\"$value\" style=\"width:100%\" />";
        echo "</td></tr>\n";
    }
}

echo <<< MIDDLE1
</table>
<br/>
<p>
<input type="submit" name="do" value="make" class="btn btn-primary" />&nbsp;&nbsp;<input type="submit" name="do" value="reset" class="btn btn-warning" />
</p>
</form>
<hr>
MIDDLE1;
echo "<form enctype=\"multipart/form-data\" action=\"?".$query_string."\" method=\"POST\">\n";
echo <<< MIDDLE2
<input type="hidden" name="MAX_FILE_SIZE" value="100000000" />
 壁紙用の画像ファイルをアップロード: <input name="image_file" type="file" class="btn btn-success" />
<input type="submit" value="画像ファイル送信" class="btn btn-primary" />
</form>
<hr>
※ 名前は空欄にして直筆でのサインを推奨します。</br>
※ 名前の下に印鑑を忘れないでね☆</br>
※ 申し送りは省略可能です。</br>
<hr>
</td>
<td width="540px" height="800px">
MIDDLE2;

$params = array();
foreach ($form as $idx => $form_elem) {    
    list($title, $type, $label, $value) = $form_elem;
    $params[$label] = $value;
}
$params['ext'] = '.pdf';
$param_str = build_queryparam($params);
echo "<iframe src=\"?$param_str\" name=\"pdf\" width=\"100%\" height=\"100%\"></iframe>\n";

echo <<< FOOT
</td></tr>
</table>

<h1 id="about"> About </h1>
<ul>
<li> 貴方の退職をサポート致します。ボタン１つで素早く退職届けを生成！
<li> URL から会社と社長名以外を外せば、特定企業向けテンプレートも作れます。
<li> 画像を投稿して得られる ID を指定する事で壁紙が貼れます。思いの丈を埋めて下さい。
<li> QRコードを埋められます。印刷した紙から URL に戻り、プリンタで印刷して紙へと、エコサイクルが出来上がるでしょう。
</ul>

<ul>
<li> PDF もどき(フォントを埋め込まない)を出力するので、Macintosh での利用を推奨します。
<li> 半角のレイアウトは未対応なので、数字やアルファベットは全角でお願いします。
</ul>

<ul>
<li> http://good-bye.biz/ を参考にさせて頂きました。よりカジュアルな操作を目指しています。
<li> 僕が無職になって収入がなくなったらアフィリエイトを貼るかもしれません。その時はご容赦を。
</ul>

<hr>
<h1 id="contact"> Contact </h1>
<address> <a href="mailto:yoya@awm.jp"> yoya@awm.jp </a> </address>
改善要望承ります！
</body> </html>

FOOT;
