<?php
include_once("MailAnalysis.php");
$ma = new MailAnalysis($_POST['source'], true);

if( isset($_POST['source']) ) {
    $source = $_POST['source'];
}
else{
    $source = <<<__SAMPLE__
Delivered-To: delivered-to-mailaddress@xxxxxxxx.sample
Return-Path: <return-path-mailaddress@xxxxxxxx.sample>
Date: Mon, 2 Aug 2021 12:00:00 +0900 (JST)
From: From-Name <from-mailaddress@xxxxxxxx.sample>
To: test <to-mailaddress@xxxxxxxx.sample>
Subject: MailTest
Content-Transfer-Encoding: 7bit
Content-Type: text/plain; charset=UTF-8

mail
text
__SAMPLE__;
}

?><!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>PHP サンプル</title>
    </head>
    <body style="text-align: left;">
        <form action="<?= basename(__FILE__); ?>" method="POST">
            <p>
                メールのソース貼り付けエリア<br />
                <textarea name="source" cols="80" rows="40"><?= $source; ?></textarea>
            </p>
            <p>
                <input type="submit" value="解析">
            </p>
        </form>
        <hr />

        <p style="text-align: left;">
        <?php if( isset($_POST['source']) ): ?>
            <h2></h2>解析結果</h2><br />
            送信アドレス(From): <?= $ma->GetFrom(); ?><br />
            送信者名: <?= $ma->GetFromName(); ?><br />
            受信アドレス(To): <?= $ma->GetTo(); ?><br />
            受信者名: <?= $ma->GetToName(); ?><br />
            件名(Subject): <?= $ma->GetSubject(); ?><br />
            <?php else: ?>
            <p style="text-align: left;">
            <h2>解析結果はここに表示</h2><br />
            <?php endif; ?>
        </p>
    </body>
</html>
<?= $ma->PrintDebug(); ?>
