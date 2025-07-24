<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class CheckLibreOffice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:libreoffice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if LibreOffice is installed and accessible';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking LibreOffice installation...');

        try {
            // Detect OS
            $isWindows = PHP_OS === 'WINNT' || PHP_OS === 'WIN32';
            
            if ($isWindows) {
                $this->info('Detected Windows environment...');
                
                // Path to Windows LibreOffice
                $libreOfficePath = 'C:\\Program Files\\LibreOffice\\program\\soffice.exe';
                
                if (file_exists($libreOfficePath)) {
                    $this->info('✅ LibreOffice found in Windows at: ' . $libreOfficePath);
                    
                    // Test if we can run it
                    $command = '"' . $libreOfficePath . '" --version';
                    exec($command, $output, $returnCode);
                    
                    if ($returnCode === 0) {
                        $versionOutput = implode("\n", $output);
                        $this->info('Version information: ' . $versionOutput);
                        
                        $this->info('Testing PDF conversion in Windows...');
                        
                        // Create a test document in the current directory
                        $testDir = getcwd();
                        $testFile = $testDir . '\\test_libreoffice.txt';
                        file_put_contents($testFile, 'Test conversion file');
                        
                        // Try to convert it
                        $command = '"' . $libreOfficePath . '" --headless --convert-to pdf --outdir "' . $testDir . '" "' . $testFile . '"';
                        exec($command, $output, $returnCode);
                        
                        if ($returnCode === 0 && file_exists($testDir . '\\test_libreoffice.pdf')) {
                            $this->info('✅ PDF conversion is working properly in Windows mode');
                            
                            // Clean up test files
                            @unlink($testDir . '\\test_libreoffice.txt');
                            @unlink($testDir . '\\test_libreoffice.pdf');
                            
                            $this->info("\n✅ Your LibreOffice setup is ready to use with Laravel in Windows!");
                            return 0;
                        } else {
                            $this->error('❌ PDF conversion failed in Windows mode');
                            $this->error('Command used: ' . $command);
                            $this->error('Output: ' . implode("\n", $output));
                        }
                    } else {
                        $this->error('❌ Found LibreOffice but could not execute it');
                        $this->error('Output: ' . implode("\n", $output));
                    }
                } else {
                    $this->error('❌ LibreOffice not found in the expected Windows location');
                    $this->info('Please make sure LibreOffice is installed in Windows at: ' . $libreOfficePath);
                }
            } else {
                // Try to run LibreOffice with --version flag
                $process = new Process(['soffice', '--version']);
                $process->run();
                
                if ($process->isSuccessful()) {
                    $versionOutput = trim($process->getOutput());
                    $this->info('✅ LibreOffice is installed!');
                    $this->info('Version information: ' . $versionOutput);
                    
                    // Check if headless mode works
                    $this->info('Testing headless mode...');
                    $headlessProcess = new Process(['soffice', '--headless', '--version']);
                    $headlessProcess->run();
                    
                    if ($headlessProcess->isSuccessful()) {
                        $this->info('✅ Headless mode is working properly');
                    } else {
                        $this->error('❌ Headless mode is not working: ' . $headlessProcess->getErrorOutput());
                    }
                    
                    // Check if we can convert a test file
                    $this->info('Testing PDF conversion capabilities...');
                    
                    // Create a test document
                    $testDir = storage_path('app/temp');
                    if (!file_exists($testDir)) {
                        mkdir($testDir, 0755, true);
                    }
                    
                    $testFile = $testDir . '/test.txt';
                    file_put_contents($testFile, 'Test conversion file');
                    
                    // Try to convert it
                    $convertProcess = new Process([
                        'soffice',
                        '--headless',
                        '--convert-to', 'pdf',
                        '--outdir', $testDir,
                        $testFile
                    ]);
                    
                    $convertProcess->setTimeout(30);
                    $convertProcess->run();
                    
                    if ($convertProcess->isSuccessful() && file_exists($testDir . '/test.pdf')) {
                        $this->info('✅ PDF conversion is working properly');
                        
                        // Clean up test files
                        @unlink($testDir . '/test.txt');
                        @unlink($testDir . '/test.pdf');
                        
                        return 0;
                    } else {
                        $this->error('❌ PDF conversion failed: ' . $convertProcess->getErrorOutput());
                    }
                } else {
                    $this->error('❌ LibreOffice is not accessible: ' . $process->getErrorOutput());
                    
                    // Try to check if it's installed but not in PATH
                    $this->info('Checking common LibreOffice installation locations...');
                    
                    $commonPaths = [
                        '/usr/bin/soffice',
                        '/usr/local/bin/soffice',
                        '/opt/libreoffice/program/soffice',
                        'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
                        'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe'
                    ];
                    
                    foreach ($commonPaths as $path) {
                        if (file_exists($path)) {
                            $this->info("Found LibreOffice at: $path");
                            $this->info("You may need to add this directory to your PATH or use the full path in your code.");
                        }
                    }
                    
                    $this->info("\nTo install LibreOffice:");
                    $this->info("- Ubuntu/Debian: sudo apt-get install libreoffice");
                    $this->info("- CentOS/RHEL: sudo yum install libreoffice");
                    $this->info("- macOS: brew install libreoffice");
                    $this->info("- Windows: Download from https://www.libreoffice.org/download/");
                    
                    return 1;
                }
            }
        } catch (\Exception $e) {
            $this->error('Error checking LibreOffice: ' . $e->getMessage());
            return 1;
        }
    }
}