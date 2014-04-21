<!DOCTYPE html>
<html>
<head>
    <title>Moss / <?= $method ?></title>
    <style>
        body, code { font: medium/1.4em monospace; }
        code { display: block; background: #eee; }
        .error { color: red; }
    </style>
</head>
<body>
<h1><?= $method ?> <small>&lt;-- this is namespaced controller class and its currently executed action</small></h1>

<p>Controller source is protected with credentials (just type <var>login</var> / <var>password</var>)</p>

<?php while($message = $flash->retrieve()): ?>
    <p class="<?= $message['type'] ?>"><?= $message['message'] ?></p>
<?php endwhile ?>

<form action="<?= $url('login') ?>" method="post">
    <input type="text" name="login" placeholder="login"/>
    <input type="text" name="password" placeholder="password"/>
    <button type="submit">Submit</button>
</form>

</body>
</html>