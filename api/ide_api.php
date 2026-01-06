<?php
// Define constant to signal AJAX context to db.php
define('DOING_AJAX', true);

// Start output buffering to catch any accidental output
ob_start();

// Disable error display to prevent breaking JSON output
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Ensure we always return JSON
header('Content-Type: application/json');

// --- IV. Environment Header Protection ---
// Force sending COOP and COEP headers to ensure Wasm SharedArrayBuffer works in HTTPS production
header('Cross-Origin-Opener-Policy: same-origin');
header('Cross-Origin-Embedder-Policy: require-corp');

// Shutdown function to handle fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_COMPILE_ERROR)) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'PHP Fatal Error: ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']
        ]);
        exit;
    }
});

// Global Exception Handler
set_exception_handler(function($e) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Uncaught Exception: ' . $e->getMessage()
    ]);
    exit;
});

// --- Zero-Config Environment Alignment ---
$isWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
$dockerPath = 'docker'; // Default assumption: it's in PATH

// 1. Path Resolution (No Guessing)
if (!$isWindows) {
    // On Linux, try to find absolute path for robustness, but fallback to 'docker'
    $detected = trim(shell_exec('which docker 2>/dev/null'));
    if ($detected && file_exists($detected)) {
        $dockerPath = $detected;
    } elseif (file_exists('/usr/bin/docker')) {
        $dockerPath = '/usr/bin/docker';
    }
}
// On Windows, we trust 'docker' command is available in PATH via Docker Desktop

// 2. Resource Constraints (Adaptive)
// Windows Docker Desktop (WSL2 backend) handles limits differently or ignores them if not configured in .wslconfig
// Linux production needs strict limits
$resourceFlags = '';
if (!$isWindows) {
    $resourceFlags = '--memory="256m" --memory-swap="256m" --cpus="0.5" --pids-limit 15';
} else {
    // Relaxed for local Windows dev to avoid "memory limit not supported" errors
    $resourceFlags = '--memory="512m"'; 
}

// 3. Diagnostics Info
$dockerDebug = [
    'os' => PHP_OS,
    'is_windows' => $isWindows,
    'resolved_docker_path' => $dockerPath,
    'php_user' => trim(shell_exec('whoami') ?: 'unknown'),
    'env_path' => getenv('PATH') ?: trim(shell_exec('echo %PATH%') ?: shell_exec('echo $PATH'))
];

// Define workspace directory
$BASE_DIR = realpath(__DIR__ . '/../workspace');
if (!$BASE_DIR) {
    if (!file_exists(__DIR__ . '/../workspace')) {
        @mkdir(__DIR__ . '/../workspace', 0777, true);
    }
    $BASE_DIR = realpath(__DIR__ . '/../workspace');
}

// Helper to send JSON response
function send_response($data, $success = true, $message = '') {
    echo json_encode(['success' => $success, 'data' => $data, 'message' => $message]);
    exit;
}

if (!$BASE_DIR) {
    send_response(null, false, 'Workspace directory not found or could not be created at ' . (__DIR__ . '/../workspace'));
}

// Security: Validate path is within workspace
function get_safe_path($path) {
    global $BASE_DIR;
    $path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
    $path = str_replace(chr(0), '', $path);
    $fullPath = $BASE_DIR . DIRECTORY_SEPARATOR . $path;
    $realPath = realpath($fullPath);

    if ($realPath) {
        if (strpos($realPath, $BASE_DIR) === 0) {
            return $realPath;
        }
    } else {
        $dir = dirname($fullPath);
        $realDir = realpath($dir);
        if ($realDir && strpos($realDir, $BASE_DIR) === 0) {
            return $fullPath;
        }
    }
    return false;
}

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($action) {
        case 'list':
            $files = [];
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($BASE_DIR, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($iterator as $file) {
                $path = $file->getPathname();
                $relativePath = substr($path, strlen($BASE_DIR) + 1);
                $relativePath = str_replace('\\', '/', $relativePath);
                
                $files[] = [
                    'id' => $relativePath,
                    'parent' => dirname($relativePath) === '.' ? '#' : dirname($relativePath),
                    'text' => $file->getFilename(),
                    'type' => $file->isDir() ? 'folder' : 'file',
                    'icon' => $file->isDir() ? 'fa fa-folder' : 'fa fa-file-code'
                ];
            }
            send_response($files);
            break;

        case 'read':
            $path = $_GET['path'] ?? '';
            $fullPath = get_safe_path($path);
            
            if ($fullPath && file_exists($fullPath) && !is_dir($fullPath)) {
                $content = file_get_contents($fullPath);
                send_response(['content' => $content]);
            } else {
                send_response(null, false, 'File not found or invalid path');
            }
            break;

        case 'save':
            $path = $input['path'] ?? '';
            $content = $input['content'] ?? '';
            $fullPath = get_safe_path($path);
            
            if ($fullPath) {
                if (file_put_contents($fullPath, $content) !== false) {
                    send_response(['path' => $path]);
                } else {
                    send_response(null, false, 'Failed to write file');
                }
            } else {
                send_response(null, false, 'Invalid path');
            }
            break;

        case 'create':
            $path = $input['path'] ?? '';
            $type = $input['type'] ?? 'file';
            $fullPath = get_safe_path($path);
            
            if ($fullPath) {
                if (file_exists($fullPath)) {
                    send_response(null, false, 'Path already exists');
                }
                
                if ($type === 'directory') {
                    if (mkdir($fullPath, 0777, true)) {
                        send_response(['path' => $path]);
                    } else {
                        send_response(null, false, 'Failed to create directory');
                    }
                } else {
                    if (file_put_contents($fullPath, '') !== false) {
                        send_response(['path' => $path]);
                    } else {
                        send_response(null, false, 'Failed to create file');
                    }
                }
            } else {
                send_response(null, false, 'Invalid path');
            }
            break;

        case 'delete':
            $path = $input['path'] ?? '';
            $fullPath = get_safe_path($path);
            
            if ($fullPath && file_exists($fullPath)) {
                if (is_dir($fullPath)) {
                    $files = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($fullPath, RecursiveDirectoryIterator::SKIP_DOTS),
                        RecursiveIteratorIterator::CHILD_FIRST
                    );
                    foreach ($files as $fileinfo) {
                        $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                        $todo($fileinfo->getRealPath());
                    }
                    rmdir($fullPath);
                } else {
                    unlink($fullPath);
                }
                send_response(['path' => $path]);
            } else {
                send_response(null, false, 'File not found');
            }
            break;
            
        case 'rename':
            $oldPath = $input['oldPath'] ?? '';
            $newPath = $input['newPath'] ?? '';
            $fullOld = get_safe_path($oldPath);
            $fullNew = get_safe_path($newPath);
            
            if ($fullOld && $fullNew && file_exists($fullOld) && !file_exists($fullNew)) {
                if (rename($fullOld, $fullNew)) {
                    send_response(['oldPath' => $oldPath, 'newPath' => $newPath]);
                } else {
                    send_response(null, false, 'Rename failed');
                }
            } else {
                send_response(null, false, 'Invalid paths');
            }
            break;

        // --- I. Backend Execution Engine Security & Resource Limits ---
        case 'run_code':
            $code = $input['code'] ?? '';
            $lang = $input['lang'] ?? 'python'; // Default to python
            
            // 4. Docker Availability Check (Pre-flight)
            // We check this BEFORE trying to lock resources or run anything.
            $checkCmd = sprintf('%s -v 2>&1', $isWindows ? '"'.$dockerPath.'"' : $dockerPath); 
            $checkOutput = [];
            $checkReturn = 0;
            exec($checkCmd, $checkOutput, $checkReturn);
            
            if ($checkReturn !== 0) {
                // Return detailed debug info
                $errData = [
                    'output' => "System Configuration Error: Docker not available.",
                    'debug_info' => $dockerDebug,
                    'check_command' => $checkCmd,
                    'check_output' => implode("\n", $checkOutput)
                ];
                $msg = "Backend environment not configured for Docker or permission denied.";
                echo json_encode([
                    'success' => false, 
                    'data' => $errData, 
                    'message' => $msg
                ]);
                exit;
            }

            // 1. Zero Trust Audit: Deep Regex Scan
            if ($lang === 'java' || $lang === 'php') {
                $risky_patterns = [
                    'Runtime' => '/Runtime\.getRuntime/',
                    'ProcessBuilder' => '/ProcessBuilder/',
                    'Reflect' => '/java\.lang\.reflect/',
                    'Socket' => '/Socket|ServerSocket/',
                    'File_System' => '/FileOutputStream|FileInputStream/',
                    'Exec' => '/exec\(|system\(|passthru\(|shell_exec\(/'
                ];
                
                foreach ($risky_patterns as $name => $pattern) {
                    if (preg_match($pattern, $code)) {
                         send_response(null, false, "Security Block: High risk keyword/class '$name' detected.");
                    }
                }
            }

            // 2. Concurrency & Cleanup Logic
            $lockFile = sys_get_temp_dir() . '/ide_cpu.lock';
            $lockFp = fopen($lockFile, 'w+');
            if (!$lockFp || !flock($lockFp, LOCK_EX | LOCK_NB)) {
                 send_response(null, false, "Resource Busy: Server is processing another task. Please try again.");
            }

            $uuid = uniqid('ide_task_', true);
            $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $uuid;
            
            try {
                if (!mkdir($tmpDir, 0777, true)) {
                    throw new Exception("Failed to create temporary directory.");
                }

                $filename = 'app.py';
                $image = 'python:3.9-slim';
                
                // OS-Adaptive Path Logic
                if ($isWindows) {
                     $mountPath = $tmpDir; 
                } else {
                     $mountPath = $tmpDir;
                }

                // Command structure inside container (Linux)
                $shellCmd = 'cp /input/* . && python app.py'; 
                
                if ($lang === 'java') {
                    $filename = 'Main.java';
                    $image = 'openjdk:11-jdk-slim';
                    $shellCmd = 'cp /input/* . && ' . 
                               'javac Main.java 2> compile.err; ' .
                               'if [ $? -ne 0 ]; then ' .
                               '  echo "COMPILATION_ERROR"; cat compile.err; exit 100; ' .
                               'fi; ' .
                               'java Main';
                } elseif ($lang === 'cpp' || $lang === 'c') {
                    $filename = 'main.cpp';
                    $image = 'gcc:latest';
                    $shellCmd = 'cp /input/* . && ' .
                               'g++ -o app main.cpp 2> compile.err; ' .
                               'if [ $? -ne 0 ]; then ' .
                               '  echo "COMPILATION_ERROR"; cat compile.err; exit 100; ' .
                               'fi; ' .
                               './app';
                }
                
                file_put_contents("$tmpDir" . DIRECTORY_SEPARATOR . "$filename", $code);
                
                // 3. Docker Execution Command
                // We use sprintf with quotes for paths to handle spaces in Windows paths
                // Note: --network none is essential for security
                $dockerCmd = sprintf(
                    '%s run --rm ' .
                    '--label ide_task=%s ' .
                    '%s ' . // Resource flags
                    '--network none ' .
                    '-v "%s":/input:ro ' .
                    '--tmpfs /app:rw,size=64m ' .
                    '-w /app ' .
                    '%s sh -c %s',
                    $isWindows ? '"'.$dockerPath.'"' : $dockerPath, 
                    $uuid,
                    $resourceFlags,
                    $mountPath, // Host path
                    escapeshellarg($image),
                    escapeshellarg($shellCmd)
                );

                $startTime = microtime(true);
                $descriptorspec = [
                    1 => ['pipe', 'w'], // stdout
                    2 => ['pipe', 'w']  // stderr
                ];
                
                $process = proc_open($dockerCmd, $descriptorspec, $pipes);
                
                $output = "";
                $status = 0;
                $resourceUsage = [
                    'time_ms' => 0,
                    'memory_peak' => 'N/A'
                ];

                if (is_resource($process)) {
                    $timeout = 10; 
                    $startP = time();
                    
                    do {
                        $statusInfo = proc_get_status($process);
                        if (!$statusInfo['running']) break;
                        if (time() - $startP > $timeout) {
                            proc_terminate($process);
                            $status = 124; // Timeout
                            break;
                        }
                        usleep(100000); 
                    } while (true);
                    
                    $output = stream_get_contents($pipes[1]);
                    $err = stream_get_contents($pipes[2]);
                    
                    if ($err && strpos($output, 'COMPILATION_ERROR') === false) {
                         $output .= "\n[STDERR]\n" . $err;
                    }
                    
                    fclose($pipes[1]);
                    fclose($pipes[2]);
                    
                    if ($status === 0) {
                        $status = proc_close($process);
                    } else {
                        proc_close($process); 
                    }
                    
                    $resourceUsage['time_ms'] = round((microtime(true) - $startTime) * 1000, 2);
                }
                
                // 4. Exception Handling Alignment
                $responseStatus = 'success';
                $responseMsg = 'Execution completed';
                $compileError = null;
                
                if (strpos($output, 'COMPILATION_ERROR') !== false) {
                    $status = 100;
                    $output = str_replace('COMPILATION_ERROR', '', $output);
                    $compileError = trim($output);
                    $responseMsg = 'Compilation Failed';
                    $responseStatus = 'error';
                } elseif ($status === 137) {
                    $responseStatus = 'error';
                    $responseMsg = 'OOM: Out of Memory (Limit 256MB)';
                } elseif ($status === 124) {
                    $responseStatus = 'error';
                    $responseMsg = 'Timeout: Execution exceeded time limit';
                } elseif ($status !== 0) {
                    $responseStatus = 'error';
                    $responseMsg = "Runtime Error (Exit Code: $status)";
                }

                $responseData = [
                    'output' => $output,
                    'status_code' => $status,
                    'resource_usage' => $resourceUsage,
                    'compile_error' => $compileError, 
                    'debug_info' => $dockerDebug
                ];
                
                flock($lockFp, LOCK_UN);
                fclose($lockFp);
                
                echo json_encode(['success' => ($responseStatus === 'success'), 'data' => $responseData, 'message' => $responseMsg]);
                
                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                } else {
                    flush();
                }
                
                // Cleanup: Relying on --rm in docker run for container cleanup.
                // We only need to clean up the temp directory on host.
                
                if ($isWindows) {
                    exec(sprintf('rmdir /s /q "%s"', $tmpDir));
                } else {
                    exec(sprintf('rm -rf %s', escapeshellarg($tmpDir)));
                }
                exit;

            } catch (Exception $e) {
                if ($lockFp) {
                    flock($lockFp, LOCK_UN);
                    fclose($lockFp);
                }
                send_response(null, false, "Server Error: " . $e->getMessage());
            }
            break;

        default:
            send_response(null, false, 'Invalid action');
    }
} catch (Exception $e) {
    send_response(null, false, 'Server Error: ' . $e->getMessage());
}
