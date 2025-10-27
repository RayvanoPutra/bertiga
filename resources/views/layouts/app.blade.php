<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.partials.head') 
    <title>
        @yield('title', 'Dashboard | Admin') 
    </title>
</head>

<body class="g-sidenav-show bg-gray-100">
    <div class="min-height-300 bg-dark position-absolute w-100"></div>
    
    @include('layouts.partials.sidebar')

    <main class="main-content position-relative border-radius-lg ">
        
        @include('layouts.partials.navbar')
        
        <div class="container-fluid py-4">
            @yield('content') 
            
            @include('layouts.partials.footer')
        </div>
    </main>
    
    @include('layouts.partials.scripts')
    
</body>
</html>