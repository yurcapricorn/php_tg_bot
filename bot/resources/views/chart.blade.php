<html>
<head>
    <title>App Name - @yield('title')</title>
</head>
<body>
@section('sidebar')
    Это - главная боковая панель.
@show
{{--@include('chart.html');--}}
{{--@component('chart.html')--}}
{{--@endcomponent--}}
{!! $chart !!}

<div class="container">
    @yield('content')
</div>
</body>
</html>