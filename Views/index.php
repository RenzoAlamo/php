<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="<?= $_STYLES["index.style"]; ?>">
</head>

<body>
  <code lang="php">
    <pre>
<?php

use Core\View;

echo View::getContent(root . "/main/index.php");
?>
    </pre>
  </code>
  <img src="<?= $_MULTIMEDIA["index.image"]; ?>" alt="">
  <script src="<?= $_SCRIPTS["index.script"]; ?>"></script>
</body>

</html>