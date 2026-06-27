<?php
// error.php - Styled 404/Error Page
$pageTitle = "Page Not Found | RT Chocos";
$pageDescription = "The page you are looking for does not exist or has been moved.";
$pathPrefix = isset($pathPrefix) ? $pathPrefix : "";
include __DIR__ . '/includes/header.php';
?>

<div id="page-error" class="page active" style="padding: 120px 20px 80px; text-align: center; background: var(--cream, #F6F2EA); min-height: 70vh; display: flex; align-items: center; justify-content: center; flex-direction: column;">
    <div style="max-width: 600px; padding: 40px; background: #FEFDFB; border-radius: 12px; border: 1px solid var(--cream-dark, #EDE7DB); box-shadow: 0 10px 30px rgba(59,42,34,0.03);">
        <div style="font-size: 72px; color: var(--gold, #C7A66A); font-family: 'Cormorant Garamond', serif; font-weight: 700; margin-bottom: 20px; line-height: 1;">404</div>
        <h1 style="font-family: 'Cormorant Garamond', serif; font-size: 28px; font-weight: 600; color: var(--brown, #3B2A22); margin-bottom: 16px;">Article or Page Not Found</h1>
        <p style="font-family: 'Jost', sans-serif; font-size: 16px; color: var(--brown-light, #5C4A40); line-height: 1.6; margin-bottom: 30px;">
            The cocoa bean you're looking for seems to have escaped. It might have been devoured, or the URL might be mistyped.
        </p>
        
        <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
            <a href="<?php echo $pathPrefix; ?>index.php" class="btn-primary" style="text-decoration: none;">Return Home</a>
            <a href="<?php echo $pathPrefix; ?>blog.php" class="btn-outline-dark" style="text-decoration: none;">Read the Blog</a>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/includes/footer.php';
?>
