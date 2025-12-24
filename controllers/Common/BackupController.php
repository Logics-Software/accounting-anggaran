<?php
namespace Controllers\Common;

use Controller;
use Auth;
use Message;
use Database;
use Session;
use Sanitizer;
use Models\Common\BackupLog;
use PDO;
use PDOException;

class BackupController extends Controller {
    
    /**
     * Show backup form
     */
    public function index() {
        Auth::requireRole(['admin']);
        
        // Get saved backup folder from session or use default
        $backupFolder = Session::get('backup_folder', __DIR__ . '/../../backups');
        
        $data = [
            'backupFolder' => $backupFolder
        ];
        
        $this->view('Common/backup/index', $data);
    }
    
    /**
     * Process backup and save to folder
     */
    public function process() {
        Auth::requireRole(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postData = Sanitizer::post($_POST);
            $backupFolder = Sanitizer::string($postData['backup_folder'] ?? '');
            
            // Validate folder path
            if (empty($backupFolder)) {
                Message::error('Folder backup harus diisi');
                $this->redirect('/backup');
                return;
            }
            
            // Normalize folder path
            $backupFolder = rtrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $backupFolder), DIRECTORY_SEPARATOR);
            
            // Create folder if not exists
            if (!is_dir($backupFolder)) {
                if (!mkdir($backupFolder, 0755, true)) {
                    Message::error('Gagal membuat folder backup: ' . $backupFolder);
                    $this->redirect('/backup');
                    return;
                }
            }
            
            // Check if folder is writable
            if (!is_writable($backupFolder)) {
                Message::error('Folder backup tidak dapat ditulis: ' . $backupFolder);
                $this->redirect('/backup');
                return;
            }
            
            // Save folder to session for next time
            Session::set('backup_folder', $backupFolder);
            
            // Generate backup
            $config = require __DIR__ . '/../../config/database.php';
            $filename = 'backup_' . $config['dbname'] . '_' . date('Y-m-d_His') . '.sql';
            $filepath = $backupFolder . DIRECTORY_SEPARATOR . $filename;
            
            // Try to use mysqldump if available (faster and more reliable)
            $sqlDump = $this->generateDumpWithMysqldump($config);
            
            // Fallback to manual dump if mysqldump is not available
            if ($sqlDump === false) {
                $sqlDump = $this->generateDumpManually($config);
            }
            
            if ($sqlDump === false) {
                Message::error('Gagal membuat backup database');
                $this->redirect('/backup');
                return;
            }
            
            // Save to file
            if (file_put_contents($filepath, $sqlDump) === false) {
                Message::error('Gagal menyimpan file backup');
                $this->redirect('/backup');
                return;
            }
            
            // Save backup log
            $this->saveBackupLog($backupFolder);
            
            Message::success('Backup database berhasil disimpan ke: ' . $filepath);
            $this->redirect('/backup');
            return;
        }
        
        $this->redirect('/backup');
    }
    
    /**
     * Download database backup as SQL dump (direct download)
     */
    public function download() {
        Auth::requireRole(['admin']);
        
        $config = require __DIR__ . '/../../config/database.php';
        
        // Generate filename with timestamp
        $filename = 'backup_' . $config['dbname'] . '_' . date('Y-m-d_His') . '.sql';
        
        // Try to use mysqldump if available (faster and more reliable)
        $sqlDump = $this->generateDumpWithMysqldump($config);
        
        // Fallback to manual dump if mysqldump is not available
        if ($sqlDump === false) {
            $sqlDump = $this->generateDumpManually($config);
        }
        
        if ($sqlDump === false) {
            Message::error('Gagal membuat backup database');
            $this->redirect('/dashboard');
            return;
        }
        
        // Save backup log (folder_backup = "downloads" for direct download)
        $this->saveBackupLog('downloads');
        
        // Set headers for download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($sqlDump));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        // Output SQL dump
        echo $sqlDump;
        exit;
    }
    
    /**
     * Save backup log to database
     */
    private function saveBackupLog($folderBackup) {
        try {
            $user = Auth::user();
            $userId = $user ? $user['id'] : null;
            
            // Get IP address
            $ipAddress = $this->getClientIpAddress();
            
            // Get user agent
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            $backupLogModel = new BackupLog();
            $backupLogModel->create([
                'tanggal_backup' => date('Y-m-d H:i:s'),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'user_id' => $userId,
                'folder_backup' => $folderBackup
            ]);
        } catch (\Exception $e) {
            // Log error but don't break backup process
            error_log('Failed to save backup log: ' . $e->getMessage());
        }
    }
    
    /**
     * Get client IP address
     */
    private function getClientIpAddress() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Generate SQL dump using mysqldump command (if available)
     */
    private function generateDumpWithMysqldump($config) {
        // Check if mysqldump is available
        $mysqldumpPath = $this->findMysqldump();
        
        if ($mysqldumpPath === false) {
            return false;
        }
        
        // Build mysqldump command
        $command = sprintf(
            '%s --host=%s --user=%s --password=%s --single-transaction --routines --triggers --no-create-info=false --add-drop-table %s 2>&1',
            escapeshellarg($mysqldumpPath),
            escapeshellarg($config['host']),
            escapeshellarg($config['username']),
            escapeshellarg($config['password']),
            escapeshellarg($config['dbname'])
        );
        
        // Execute command
        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);
        
        if ($returnVar !== 0) {
            // Log error but don't expose password
            error_log('mysqldump failed: ' . implode("\n", $output));
            return false;
        }
        
        return implode("\n", $output);
    }
    
    /**
     * Find mysqldump executable path
     */
    private function findMysqldump() {
        $paths = [
            'mysqldump', // Try direct command first
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/opt/homebrew/bin/mysqldump', // macOS Homebrew
            'C:\\xampp\\mysql\\bin\\mysqldump.exe', // Windows XAMPP
            'C:\\wamp\\bin\\mysql\\mysql5.7.17\\bin\\mysqldump.exe', // Windows WAMP
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe', // Windows MySQL
            'C:\\Program Files\\MariaDB\\bin\\mysqldump.exe', // Windows MariaDB
        ];
        
        foreach ($paths as $path) {
            if (is_executable($path) || ($path === 'mysqldump' && $this->commandExists('mysqldump'))) {
                return $path;
            }
        }
        
        return false;
    }
    
    /**
     * Check if command exists in PATH
     */
    private function commandExists($command) {
        $whereIsCommand = (PHP_OS == 'WINNT') ? 'where' : 'which';
        $process = proc_open(
            "$whereIsCommand $command",
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        );
        
        if ($process !== false) {
            $stdout = stream_get_contents($pipes[1]);
            $returnCode = proc_close($process);
            return $returnCode === 0 && !empty($stdout);
        }
        
        return false;
    }
    
    /**
     * Generate SQL dump manually using PHP (fallback method)
     */
    private function generateDumpManually($config) {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            
            $sqlDump = "-- SQL Dump Generated: " . date('Y-m-d H:i:s') . "\n";
            $sqlDump .= "-- Database: {$config['dbname']}\n";
            $sqlDump .= "-- Host: {$config['host']}\n\n";
            $sqlDump .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
            $sqlDump .= "SET time_zone = \"+00:00\";\n\n";
            $sqlDump .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
            $sqlDump .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
            $sqlDump .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
            $sqlDump .= "/*!40101 SET NAMES {$config['charset']} */;\n";
            $sqlDump .= "/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;\n";
            $sqlDump .= "/*!40103 SET TIME_ZONE='+00:00' */;\n";
            $sqlDump .= "/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;\n";
            $sqlDump .= "/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;\n";
            $sqlDump .= "/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;\n";
            $sqlDump .= "/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;\n\n";
            
            // Get all tables
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($tables as $table) {
                $sqlDump .= "\n-- --------------------------------------------------------\n";
                $sqlDump .= "-- Table structure for table `{$table}`\n";
                $sqlDump .= "-- --------------------------------------------------------\n\n";
                
                // Get CREATE TABLE statement
                $createTable = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
                if ($createTable) {
                    $sqlDump .= "DROP TABLE IF EXISTS `{$table}`;\n";
                    $sqlDump .= $createTable['Create Table'] . ";\n\n";
                }
                
                // Get table data
                $sqlDump .= "-- Dumping data for table `{$table}`\n\n";
                
                $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($rows) > 0) {
                    foreach ($rows as $row) {
                        $values = [];
                        foreach ($row as $value) {
                            if ($value === null) {
                                $values[] = 'NULL';
                            } else {
                                $values[] = $pdo->quote($value);
                            }
                        }
                        $sqlDump .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $sqlDump .= "\n";
                }
            }
            
            $sqlDump .= "\n/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;\n";
            $sqlDump .= "/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;\n";
            $sqlDump .= "/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;\n";
            $sqlDump .= "/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;\n";
            $sqlDump .= "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
            $sqlDump .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
            $sqlDump .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";
            $sqlDump .= "/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;\n";
            
            return $sqlDump;
            
        } catch (\Exception $e) {
            error_log('Manual dump failed: ' . $e->getMessage());
            return false;
        }
    }
}

