<?php

// Disable output buffering
ini_set('output_buffering', 'off');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the sitemap URL from the form data
    $sitemap_url = $_POST['sitemap-url'];

    // Get the domain name of the sitemap URL
    $sitemap_domain = parse_url($sitemap_url, PHP_URL_HOST);

    // Read the authorization code from the external URL
    $auth_code_url = "https://$sitemap_domain/cache_warmup_auth.txt";

    try {
        $auth_code = @file_get_contents($auth_code_url);
    } catch (Exception $e) {
        echo '<div style="color: red;">Error: Could not read authorization code from ' . $auth_code_url . '</div>';
        exit;
    }

    // Check if the authorization code and the sitemap are on the same domain
    if ($auth_code === false || $sitemap_domain !== parse_url($auth_code_url, PHP_URL_HOST)) {
        echo '<div style="color: red;">Error: Sitemap is not on the authorized domain. Please make sure the cache_warmup_auth.txt file is on the same domain as the sitemap URL.</div>';
        exit;
    }

    // Check if the authorization code is correct
    if (trim($auth_code) !== '') {
        echo '<div style="color: red;">Error: Authorization code is incorrect.</div>';
        exit;
    }

    if (isset($_POST['sitemap-url'])) {
        // Extract the sitemap URL from the form data
        $sitemap_url = $_POST['sitemap-url'];

        // Start the Bash script in the background and get the process ID
        $cmd = "bash /var/www/cache-warmup.eu/htdocs/external/nunndpweehopnvsdpugygkbxmccpmvmx.sh \"$sitemap_url\" > /dev/null 2>&1 & echo $!";
        $output = shell_exec($cmd);
        $pid = trim($output);

        // Check if the process ID is valid
        if (is_numeric($pid)) {
            echo "Cache WarmUP has been started in Background. You can close this window.";
        } else {
            echo "There was an error starting the Cache WarmUP.";
        }
    }

}
?>