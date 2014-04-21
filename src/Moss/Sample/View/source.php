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

<p>Below is controllers source, to logout click <a href="<?= $url('logout') ?>">here</a></p>
<code><?= $controller ?></code>
<p>As you can see, there is no actual authorisation. Login/password are tokenized in <var>security</var> component. The rest happens inside that component and in bootstrap</p>
<p>And this is bundles bootstrap file</p>
<code><?= $bootstrap ?></code>

</body>
</html>