<?php
// modules/download_deps.php

echo "Starting dependency download...\n";

$assetsDir = __DIR__ . '/../assets/js/lib';
if (!is_dir($assetsDir)) {
    mkdir($assetsDir, 0755, true);
}

function download_file($url, $dest) {
    $dir = dirname($dest);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    echo "  Downloading " . basename($dest) . "... ";
    
    // Check if file exists and has content
    if (file_exists($dest) && filesize($dest) > 0) {
        echo "[Skipped] (already exists)\n";
        return true;
    }

    // Try curl command first
    $cmd = sprintf('curl -L -o "%s" "%s" 2>&1', $dest, $url);
    exec($cmd, $output, $return_var);
    
    if ($return_var === 0 && file_exists($dest) && filesize($dest) > 0) {
        echo "OK (curl)\n";
        return true;
    }
    
    // Fallback to file_get_contents
    $context = stream_context_create([
        "ssl" => [
            "verify_peer" => false,
            "verify_peer_name" => false,
        ],
    ]);
    
    $content = @file_get_contents($url, false, $context);
    if ($content !== false) {
        file_put_contents($dest, $content);
        echo "OK (php)\n";
        return true;
    }
    
    echo "FAILED! (Error: " . implode("\n", $output) . ")\n";
    return false;
}

// --- 1. Pyodide (Existing) ---
echo "\n--- Pyodide v0.23.4 ---\n";
$pyodideDir = $assetsDir . '/pyodide';
$pyodideBaseUrl = 'https://cdn.jsdelivr.net/pyodide/v0.23.4/full/';
$pyodideFiles = [
    'pyodide.js',
    'pyodide.asm.js',
    'pyodide.asm.wasm',
    'repodata.json',
    'python_stdlib.zip'
];

foreach ($pyodideFiles as $file) {
    download_file($pyodideBaseUrl . $file, $pyodideDir . '/' . $file);
}

// --- 2. Monaco Editor (Expanded) ---
echo "\n--- Monaco Editor v0.34.1 ---\n";
$monacoDir = $assetsDir . '/monaco-editor/min/vs';
$monacoBaseUrl = 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.34.1/min/vs/';

// Essential files for Python editing
$monacoFiles = [
    // Loader
    'loader.js',
    
    // Core Editor
    'editor/editor.main.js',
    'editor/editor.main.css',
    'editor/editor.main.nls.js',
    
    // Workers
    'base/worker/workerMain.js',
    'editor/editor.worker.js',
    
    // Icons (Critical for UI)
    'base/browser/ui/codicons/codicons/codicons.ttf',
    
    // Python Language Support
    'basic-languages/python/python.js',
    
    // JSON (Commonly used)
    'language/json/jsonMode.js',
    'language/json/jsonWorker.js'
];

foreach ($monacoFiles as $file) {
    download_file($monacoBaseUrl . $file, $monacoDir . '/' . $file);
}

// --- 3. WebTorrent (New) ---
echo "\n--- WebTorrent v2.4.8 ---\n";
$webtorrentDir = $assetsDir . '/webtorrent';
$webtorrentUrl = 'https://unpkg.com/webtorrent@2.4.8/dist/webtorrent.min.js';
download_file($webtorrentUrl, $webtorrentDir . '/webtorrent.min.js');

// --- 4. FFmpeg.wasm (New) ---
echo "\n--- FFmpeg.wasm v0.11.6 ---\n";
$ffmpegDir = $assetsDir . '/ffmpeg';
$ffmpegFiles = [
    'ffmpeg.min.js' => 'https://unpkg.com/@ffmpeg/ffmpeg@0.11.6/dist/ffmpeg.min.js',
    'ffmpeg-core.js' => 'https://unpkg.com/@ffmpeg/core@0.11.0/dist/ffmpeg-core.js',
    'ffmpeg-core.wasm' => 'https://unpkg.com/@ffmpeg/core@0.11.0/dist/ffmpeg-core.wasm',
    'ffmpeg-core.worker.js' => 'https://unpkg.com/@ffmpeg/core@0.11.0/dist/ffmpeg-core.worker.js'
];

foreach ($ffmpegFiles as $name => $url) {
    download_file($url, $ffmpegDir . '/' . $name);
}

// --- 5. Tesseract.js (New) ---
echo "\n--- Tesseract.js v5.0.3 ---\n";
$ocrDir = $assetsDir . '/tesseract';
$ocrUrl = 'https://unpkg.com/tesseract.js@5.0.3/dist/tesseract.min.js';
download_file($ocrUrl, $ocrDir . '/tesseract.min.js');

// --- 6. v86 (x86 Emulator) ---
echo "\n--- v86 (x86 Emulator) ---\n";
$v86Dir = $assetsDir . '/v86';
$v86Files = [
    'libv86.js' => 'https://copy.sh/v86/build/libv86.js',
    'v86.wasm' => 'https://copy.sh/v86/build/v86.wasm',
    'seabios.bin' => 'https://copy.sh/v86/bios/seabios.bin',
    'vgabios.bin' => 'https://copy.sh/v86/bios/vgabios.bin'
];
foreach ($v86Files as $name => $url) {
    download_file($url, $v86Dir . '/' . $name);
}

echo "\nDone! Dependencies updated.\n";
?>
