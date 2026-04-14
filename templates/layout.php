<?php

use function Csv2ics\escape;
header("Content-Type: text/html; charset=utf-8");

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="//brick.a.ssl.fastly.net/Roboto+Slab:400,700">
    <link rel="stylesheet" href="assets/style.css">
    <meta charset="utf-8">
    <META NAME="ROBOTS" CONTENT="INDEX,NOFOLLOW">
    <title><?=escape($title)?></title>
</head>
<body>
    <main>
        <h1><a href="/"><?=escape($title)?></a></h1>
        <?php if ($errorMessage): ?>
            <div class="error"><?=escape($errorMessage)?></div>
        <?php endif; ?>
        <?=$content?>
    </main>
    <footer>
        <h1><?=escape($copyright)?></h1>
    </footer>
    
    <script>
        // open external links in new tab
        var i, links = document.links, length = links.length;
        for(i=0;i<length;i++) {
            (links[i].hostname==window.location.hostname) || links[i].setAttribute("target", "_blank"); 
        }
    </script>
</body>
</html>



