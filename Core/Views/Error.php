<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $status; ?></title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    .container {
      width: 100%;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: rgba(26, 32, 44);
    }

    .error {
      display: flex;
    }

    .error .statusCode,
    .error .status {
      font-family: system-ui, sans-serif;
      text-transform: uppercase;
      font-weight: bold;
      font-size: 1.3rem;
      color: rgba(160, 174, 192);
    }

    .error .statusCode {
      padding: 0.125rem 0.5rem;
      border-right: 1px solid;
    }

    .error .status {
      padding: 0.125rem 0.5rem;
      border-left: 1px solid;
    }
  </style>
</head>

<body>
  <div class=" container">
    <div class="error">
      <div class="statusCode"><?= $statusCode; ?></div>
      <div class="status"><?= $status; ?></div>
    </div>
  </div>
</body>

</html>