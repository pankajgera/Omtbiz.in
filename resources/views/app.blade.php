<!DOCTYPE html>
<html lang="en">
<head>
    <title>Omtbiz</title>
    <script src="/assets/js/pace.js"></script>
    <link href="{{mix("/assets/css/omtbiz.css")}}" rel="stylesheet" type="text/css">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600&display=swap" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/img/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/img/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/img/favicons/favicon-16x16.png">
    <link rel="manifest" href="/assets/img/favicons/site.webmanifest">
    <link rel="mask-icon" href="/assets/img/favicons/safari-pinned-tab.svg" color="#f54c4c">
    <link rel="shortcut icon" href="/assets/img/favicons/favicon.ico">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-config" content="/assets/img/favicons/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="layout-default skin-omtbiz">
<div id="app" class="template-container">
    <div class="mobile-menu-overlay" @click.prevent="onOverlayClick"></div>
    <transition name="fade" mode="out-in">
        <router-view></router-view>
    </transition>
</div>
<script type="text/javascript" src="{{mix('/assets/js/app.js')}}"></script>
<script type="text/javascript" src="/assets/js/print.min.js"></script>
<link rel="stylesheet" type="text/css" href="/assets/css/print.min.css">
<link rel="stylesheet" type="text/css" href="/assets/css/font-poppins.css">
</body>
</html>
