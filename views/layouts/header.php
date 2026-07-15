<?php
// Calculate base URL path dynamically for subdirectory hosting support
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Mesa de Ayuda'); ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Local Static Assets -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>/assets/css/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>/assets/css/simple-datatables.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>/assets/css/main.css">
    <style>
        :root {
            --primary-color: <?php echo htmlspecialchars($theme_color ?? '#0d6efd'); ?> !important;
            --primary-rgb: <?php 
                // Hex to RGB parser for bootstrap shadow compatibility
                $hex = str_replace('#', '', $theme_color ?? '#0d6efd');
                if (strlen($hex) == 3) {
                    $r = hexdec(substr($hex,0,1).substr($hex,0,1));
                    $g = hexdec(substr($hex,1,1).substr($hex,1,1));
                    $b = hexdec(substr($hex,2,1).substr($hex,2,1));
                } else {
                    $r = hexdec(substr($hex,0,2));
                    $g = hexdec(substr($hex,2,2));
                    $b = hexdec(substr($hex,4,2));
                }
                echo "$r, $g, $b";
            ?> !important;
        }
    </style>
    <!-- Theme Manager Initializer script to avoid flashing -->
    <script>
        const storedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', storedTheme);
    </script>
</head>
<body>

<?php
$currentUser = $_SESSION['user'] ?? null;
?>

<div class="wrapper">
    <!-- End block when layouts/footer.php is loaded -->
