<?php

/*
	resign.php
	2013/10/22- (c) yoya@awm.jp
*/

require_once('form.php');
require_once('image.php');

$query_string = $_SERVER['QUERY_STRING'];
// var_dump($query_string);
// var_dump($_REQUEST);

function build_queryparam($params) {
   foreach ($params as $key => $value) {    
       $param_peers[] = urlencode($key)."=".urlencode($value);
   }
   return join('&', $param_peers);
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
       file_put_contents($origpath, $image_data);
        alphabrend($image_data, $ext, $path);
   }
   //
   $params = $_GET;
   $params['image_id'] = $image_id;
   $params['do'] = 'make';
   $param_str = build_queryparam($params);
   header("Location: ?$param_str");
   exit(0);
}

header('Content-Type: text/html; charset=UTF-8');
echo <<< HEAD
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title> 退職届け PDF メーカー </title>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="./css/bootstrap.min.css">
<link href="./css/jumbotron.css" rel="stylesheet">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="./js/bootstrap.min.js"></script>
</head>

<body style="background-color:#f0ffe0; ">

    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="?">退職届け PDF メーカー</a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li><a href="#">Home</a></li>
            <li><a href="#notice">Notice</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#contact">Contact</a></li>
            </li>
          </ul>
        </div><!--/.navbar-collapse -->
      </div>
    </div>

<h1 align="center" id="home"> 退職届け PDF メーカー </h1>

<p>
<div id="fb-root"></div>
<script>(function(d, s, id) {
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) return;
	js = d.createElement(s); js.id = id;
	js.src = "//connect.facebook.net/ja_JP/all.js#xfbml=1";
	fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<div class="fb-like" data-href="http://app.awm.jp/resign/" data-colorscheme="light" data-layout="button_count" data-action="like" data-show-faces="true" data-send="false"></div> 
<a href="http://b.hatena.ne.jp/entry/http://app.awm.jp/resign/" class="hatena-bookmark-button" data-hatena-bookmark-title="退職届け PDF メーカー" data-hatena-bookmark-layout="simple-balloon" title="このエントリーをはてなブックマークに追加"><img src="http://b.st-hatena.com/images/entry-button/button-only@2x.png" alt="このエントリーをはてなブックマークに追加" width="20" height="20" style="border: none;" /></a><script type="text/javascript" src="http://b.st-hatena.com/js/bookmark_button.js" charset="utf-8" async="async"></script>
<a href="https://twitter.com/share" class="twitter-share-button" data-url="http://app.awm.jp/resign/" data-via="yoya" data-lang="ja" data-hashtags="退職届">ツイート</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
</p>

<table width="100%" height="100%">
<tr><td align="center">
<form>
<hr>
<p>
<input type="submit" name="do" value="make" class="btn btn-primary" />&nbsp;&nbsp;<input type="submit" name="do" value="reset" class="btn btn-warning" />
</p>
<table border="1">
HEAD;

$form = get_formData();

//if (isset($_REQUEST['do']) && ($_REQUEST['do'] === 'make')) {
    foreach ($form as $idx => $form_elem) {
        list($title, $type, $label, $value) = $form_elem;
        if (array_key_exists($label, $_REQUEST)) {
            $form[$idx][3] = $_REQUEST[$label];
        }
    }
//} var_dump($form);

foreach ($form as $form_elem) {
    list($title, $type, $label, $value) = $form_elem;
    $value = htmlspecialchars($value); // escape
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
※ 名前は空欄にして直筆でのサインを推奨します。</br>
※ 名前の下に印鑑を忘れないでね☆</br>
※ 申し送りは省略可能です。</br>

<hr>
MIDDLE1;
echo "<form enctype=\"multipart/form-data\" action=\"?".$query_string."\" method=\"POST\">\n";
echo <<< MIDDLE2
<input type="hidden" name="MAX_FILE_SIZE" value="100000000" />
 壁紙用の画像ファイルをアップロード: <input name="image_file" type="file" class="btn btn-success" />
<input type="submit" value="画像ファイル送信" class="btn btn-primary" />
</form>
※ 送信すると画像ID の欄に ID文字列が埋まります。<br />
次回からその ID を指定して下さい。
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
echo "<iframe src=\"pdf.php?$param_str\" name=\"pdf\" width=\"100%\" height=\"100%\"></iframe>\n";

echo <<< FOOT
</td></tr>
</table>

<h1 id="notice"> Notice </h1>
<ul>
<li> 「退職届」は退職の意志を一方的に会社へ通知するものです。穏便に進めたい場合は「退職願」を選択して下さい。
<li> 退職願/退職届のコピーを取っておいて離職票や源泉徴収票が送られてくるまできちんと保管しておきましょう。
<li> 偽造の疑いを減らす為に、名前の欄は直筆が良いでしょう。
</ul>

<hr>

<h1 id="about"> About </h1>
<ul>
<li> 貴方の退職をサポートします。ボタン１つで素早く退職届けを生成！
<li> URL に会社と社長だけ残せば、特定企業向けテンプレートになります。
<li> 画像を投稿して得られる ID を指定する事で壁紙が貼れます。思いの丈を埋めて下さい。
<li> QRコードを埋められます。印刷した紙から URL に戻り、プリンタで印刷して紙へと、(反?)エコサイクル！
</ul>

<ul>
<li> フォントを埋め込まない PDF もどきを出力するので、Macintosh での利用を推奨します。
<li> 半角文字は横書きとしてレイアウトします。縦書きにしたいアルファベットは全角で入力して下さい。
</ul>

<ul>
<li> 参考) <a href="http://pwiki.awm.jp/~yoya/?resign" target="_blink"> http://pwiki.awm.jp/~yoya/?resign </a>
<li> http://good-bye.biz/ を参考にさせて頂きました。よりカジュアルな操作を目指します。
<li> 僕が無職になって収入がなくなったらアフィリエイトを貼るかもしれません。その時はご容赦を。m(_ _)m
</ul>

<hr>

<h1 id="contact"> Contact </h1>
<address> <a href="mailto:yoya@awm.jp"> yoya@awm.jp </a> </address>
改善要望承ります！
</body> </html>

FOOT;

