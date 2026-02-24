<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function refactor($dir)
{
    $files = glob($dir . '/*.php');
    foreach ($files as $file) {
        if (is_dir($file)) {
            refactor($file);
            continue;
        }
        $content = file_get_contents($file);
        $changed = false;

        // 1. Replace <?= with <?php echo
        if (strpos($content, '<?=') !== false) {
            $content = str_replace('<?=', '<?php echo ', $content);
            $changed = true;
        }

        // 2. Replace ?? with isset() ? :
        // Simple version: $var ?? 'default' -> (isset($var) ? $var : 'default')
        $new_content = preg_replace('/(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(?:\[[^\]]+\])*)\s*\?\?\s*([^\);, \r\n]+)/', '(isset($1) ? $1 : $2)', $content);
        if ($new_content !== $content) {
            $content = $new_content;
            $changed = true;
        }

        // 3. Replace [] with array()
        // This is hard to do safely because [] is used for indexing.
        // We only replace [] when it's NOT following a variable or closing bracket/paren.
        // $new_content = preg_replace('/(?<![a-zA-Z0-9_\$\]\)])\[\s*\]/', 'array()', $content);
        // For now let's focus on ?? and <?= which are more common causes of white screen in 5.6 vs 7.0+

        if ($changed) {
            file_put_contents($file, $content);
            echo "Refactored: $file\n";
        }
    }

    $subdirs = glob($dir . '/*', GLOB_ONLYDIR);
    foreach ($subdirs as $subdir) {
        refactor($subdir);
    }
}

refactor('.');
?>
