<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
    @include('includes.admin.style')
</head>

<body>
    <div id="wrapper">
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">

                @yield('content')
            </div>
        </div>
    </div>
    @include('includes.admin.script')
</body>

</html>
