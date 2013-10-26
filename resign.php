<?php

if ($_REQUEST['ext'] === '.pdf') {
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

//    echo "$resign_date"; exit(0)
    require('yoyapdf.php');
    $pdf=new YoyaPDF();
    $pdf->AddSJISFont(); // XXX
    $pdf->AddPage();
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

<body bgcolor="#e0fff0">

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
    array('申し送り', 'textarea', 'note', ''),
);

if ($_REQUEST['do'] === 'make') {
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
<textarea name=\"$label\" rows=\"4\" cols=\"30\">$value</textarea></td></tr>\n";
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
<td><input type=\"$type\" name=\"$label\"  value=\"$value\" /></td></tr>\n";
    }
}

echo <<< MIDDLE
</table>
<br/>

<p>
<input type="submit" name="do" value="make" class="btn btn-primary" />&nbsp;&nbsp;<input type="submit" name="do" value="reset" class="btn btn-warning" />
</p>
<hr>
※ 名前は空欄にして直筆でのサインを推奨します。</br>
※ 名前の下に印鑑を忘れないでね☆</br>
※ 申し送りは省略可能です。</br>
<hr>
</td>
<td width="540px" height="800px">
MIDDLE;

$params = array();
foreach ($form as $idx => $form_elem) {    
    list($title, $type, $label, $value) = $form_elem;
    $params[] = $label."=".urlencode($value);
}
$params[] = 'ext=.pdf';
$param_str = join('&', $params);
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
<li> 尚、PDF もどき(フォントを埋め込まない)を出力するので、Macintosh での利用を推奨します。
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
