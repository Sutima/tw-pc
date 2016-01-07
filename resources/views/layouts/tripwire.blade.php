<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="system" content="<?= $systemName ?>">
	<meta name="systemID" content="<?= $systemID ?>">
	<meta name="server" content="<?= $_ENV['STATIC_HOST'] ?>">
	<link rel="shortcut icon" href="//<?= $_ENV['STATIC_HOST'] ?>/images/favicon.png" />

	<link rel="stylesheet" type="text/css" href="//<?= $_ENV['STATIC_HOST'] ?>/css/combine.css">
	<link rel="stylesheet" type="text/css" href="//<?= $_ENV['STATIC_HOST'] ?>/css/style.css">

	<title><?= $systemName ?> - <?= $_ENV['APP_NAME'] ?></title>
</head>
<body class="transition">
    @yield('content')
</body>
</html>
