<!doctype html>
<html lang="en">
<html>
<head>
    <title><?= $_ENV['APP_NAME'] ?></title>

    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="description" content="Tripwire is a wormhole mapping tool built for use with EVE Online. It fully supports the EVE in-game browser and the latest Chrome, Firefox and Internet Exporer. Using the latest in internet security standards it is the most secure tool in New Eden." />
    <meta property="og:type" content="article"/>
    <meta property="og:url" content="https://tripwire.eve-apps.com/"/>
    <meta property="og:title" content="The greatest wormhole mapper ever."/>
    <meta property="og:image" content="//<?= $_ENV['STATIC_HOST'] ?>/images/landing/thumbnail.jpg" />
    <meta property="og:locale" content="en_US"/>
    <meta property="og:site_name" content=""/>

    <!-- Stylesheets -->
    <link rel="stylesheet" type="text/css" href="//<?= $_ENV['STATIC_HOST'] ?>/css/landing/base.css" />
    <link rel="stylesheet" type="text/css" href="//<?= $_ENV['STATIC_HOST'] ?>/css/landing/dark.css" />
    <link rel="stylesheet" type="text/css" href="//<?= $_ENV['STATIC_HOST'] ?>/css/landing/media.queries.css" />
    <link rel="stylesheet" type="text/css" href="//<?= $_ENV['STATIC_HOST'] ?>/css/landing/tipsy.css" />
    <link rel="stylesheet" type="text/css" href="//<?= $_ENV['STATIC_HOST'] ?>/js/landing/fancybox/jquery.fancybox-1.3.4.css" />
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Nothing+You+Could+Do|Quicksand:400,700,300">

    <!-- Favicons -->
    <link rel="shortcut icon" href="//<?= $_ENV['STATIC_HOST'] ?>/images/favicon.png" />
</head>
<body>
    @yield('content')
</body>
</html>
