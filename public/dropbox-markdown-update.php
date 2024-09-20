<?php

// Dropbox-URL with ?dl=1 parameter to force direct download
// https://www.dropbox.com/xxxxxxxx&dl=1
$dropboxUrl = '';

// Local path to save the downloaded ZIP file
$zipFilePath = './downloaded.zip';

// Target directory to extract the ZIP file
$extractTo = __Dir__ . '/extracted';

function downloadZipFile($url, $filePath) {
    $ch = curl_init($url);
    $fp = fopen($filePath, 'w');

    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
    if (filesize($filePath) === 0) {
        echo "Error downloading: $filePath<br>";
    } else {
        echo "Download completed: $filePath<br>";
    }
}

function unzipFile($zipFilePath, $extractTo) {
    if (!is_dir($extractTo)) {
        mkdir($extractTo, 0777, true);
    }

    $zip = new \ZipArchive;
    if ($zip->open($zipFilePath) === TRUE) {
        for ($idx = 0; $s = $zip->statIndex($idx); $idx++) {
            $filePath = $s['name'];

            $dir = dirname($filePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            if ($filePath !== '/') {
                if ($zip->extractTo($extractTo, $filePath)) {
                    if ($zip->getExternalAttributesIndex($idx, $opsys, $attr) && $opsys == \ZipArchive::OPSYS_UNIX) {
                        chmod($filePath, ($attr >> 16) & 0777);
                    }
                }
            }
        }
    $zip->close();
        echo "Extraction completed: $zipFilePath<br>";
} else {
        echo "Error extracting: $zipFilePath<br>";
    }
}

function removeFile($filePath) {
    if (file_exists($filePath)) {
        unlink($filePath);
        if (file_exists($filePath)) {
            echo "Error deleting: $filePath<br>";
        } else {
            echo "File deleted: $filePath<br>";
        }
    } else {
        echo "File not found: $filePath<br>";
    }
}

try {
    downloadZipFile($dropboxUrl, $zipFilePath);
    unzipFile($zipFilePath, $extractTo);
    removeFile($zipFilePath);
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
