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
<h1><?= $method ?>
    <small>&lt;-- this is namespaced controller class and its currently executed action</small>
</h1>

<p>Moss sample controller and <a href="<?= $url('source') ?>">it looks like this</a></p>

<p>Sample bundle uses <em>plain PHP</em> templates by default, but also includes
    <a href="http://twig.sensiolabs.org/">Twig</a> templates for <var>moss/bridge</var></p>

<p>This can be easily changed, just add
    <var>"moss/bridge": "*"</var> to required packages in composer and install/update dependencies.</p>

<p>Last step is to replace default
    <em>view</em> definition in bootstrap with that in bridges readme.<br>No further changes in controllers need to be made.
</p>

</body>
</html>