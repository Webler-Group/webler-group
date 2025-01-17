<?php
// header.php

// Adjust the base directory to be two levels up from the current directory
$baseDir = realpath(__DIR__ . '/../..');

// Initialize an empty array to store feature links
$featureLinks = [];

// Open the base directory
if ($handle = opendir($baseDir)) {
    // Loop through the directory entries
    while (false !== ($entry = readdir($handle))) {
        // Skip '.' and '..'
        if ($entry !== '.' && $entry !== '..' && is_dir($baseDir . '/' . $entry)) {
            // Check if an index.php file exists in the directory
            $featureIndex = $baseDir . '/' . $entry . '/index.php';
            if (file_exists($featureIndex)) {
                // Create a link for this feature with an absolute URL path
                $featureLinks[] = [
                    'name' => ucfirst($entry),
                    'url' => '/' . $entry . '/index.php'
                ];
            }
        }
    }
    closedir($handle);
}
?>

<nav>
    <div class="nav-container">
        <img src="/Webler/assets/images/logo.png" alt="Webler Logo" class="logo">
        <div class="nav-toggle">Menu</div>
    </div>
    <ul>
        <?php foreach ($featureLinks as $link): ?>
            <li><a href="<?php echo htmlspecialchars($link['url']); ?>"><?php echo htmlspecialchars($link['name']); ?></a></li>
        <?php endforeach; ?>
    </ul>
</nav>