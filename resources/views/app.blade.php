<!DOCTYPE html>
<html style="background-color: #1a202c">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    @routes
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    @inertiaHead
</head>

<body style="padding: 0; margin: 0; height: 100vh; width: 100%;">
    @inertia
</body>

</html>