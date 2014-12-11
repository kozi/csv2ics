<?php header("Content-Type: text/html; charset=utf-8"); ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link href='http://fonts.googleapis.com/css?family=Fira+Sans:400,300,700' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="assets/style.css">
    <meta charset="utf-8">
    <META NAME="ROBOTS" CONTENT="INDEX,NOFOLLOW">
    <title><?=$this->e($title)?></title>
</head>
<body>
    <main>
        <h1><?=$this->e($title)?></h1>
        <?php if($errorMessage): ?>
            <div class="error"><?=$this->e($errorMessage)?></div>
        <?php endif; ?>
        <?=$this->section('content')?>
    </main>
    <footer>
        <h1><?=$this->e($copyright)?></h1>
    </footer>
</body>
</html>



