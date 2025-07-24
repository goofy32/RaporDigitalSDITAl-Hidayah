<?php
// test-libreoffice-fullpath.php

// Get the full current directory path
$currentDir = __DIR__;
$testFile = $currentDir . '\\test_libreoffice.txt';

// Create a test file in the current directory
file_put_contents($testFile, 'Test conversion file');
echo "Created test file at: $testFile\n";

// Path to Windows LibreOffice
$libreOfficePath = 'C:\\Program Files\\LibreOffice\\program\\soffice.exe';

// Command to run with full absolute paths
$command = '"' . $libreOfficePath . '" --headless --convert-to pdf --outdir "' . $currentDir . '" "' . $testFile . '"';
echo "Running command: $command\n";

// Execute command
exec($command, $output, $returnCode);

echo "Return code: $returnCode\n";
echo "Output: " . implode("\n", $output) . "\n";

// Check if PDF was created
$pdfFile = $currentDir . '\\' . pathinfo($testFile, PATHINFO_FILENAME) . '.pdf';
if (file_exists($pdfFile)) {
    echo "Success! PDF created at: {$pdfFile}\n";
} else {
    echo "Failed! PDF not created at: {$pdfFile}\n";
}