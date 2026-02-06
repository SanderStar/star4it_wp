#!/usr/bin/env php
<?php
// zip_plugin.php: Create a zip of kerk_pdf_event_importer plugin with all dependencies
$pluginDir = __DIR__;
$zipFile = $pluginDir . '/kerk_pdf_event_importer.zip';
$vendorDir = $pluginDir . '/vendor';

$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    exit("Cannot open $zipFile\n");
}
function addDirToZip($zip, $dir, $base) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = "$dir/$file";
        $local = "$base/$file";
        if (is_dir($path)) {
            addDirToZip($zip, $path, $local);
        } else {
            $zip->addFile($path, $local);
        }
    }
}
addDirToZip($zip, $pluginDir, 'kerk_pdf_event_importer');
if (is_dir($vendorDir)) {
    addDirToZip($zip, $vendorDir, 'kerk_pdf_event_importer/vendor');
}
$zip->close();
echo "Created $zipFile\n";
