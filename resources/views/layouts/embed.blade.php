<!DOCTYPE html>
<html style="background-color: #a7a7a7; min-height: 100vh;">
<head>
    <title>@yield('title')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        html, body { min-height: 100%; background: #a7a7a7 !important; padding: 0 !important; margin: 0 !important; }
        /* Hilangkan elemen yang tidak perlu saat mode embed */
        .workflow-stepper,
        .viewer-sticky-actions,
        .ai-floating-info { display: none !important; }
        .document-viewer { padding: 30px 0 !important; box-shadow: none !important; min-height: 100vh; }
    </style>
    @stack('css')
</head>
<body style="background-color: #a7a7a7; min-height: 100vh; margin: 0; padding: 0;">
    @yield('content')
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('js')
</body>
</html>
