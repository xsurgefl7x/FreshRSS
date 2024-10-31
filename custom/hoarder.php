<?php
// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Environment variables
$api_key = getenv('HOARDER_API_KEY') ?: '';
$server_addr = getenv('HOARDER_SERVER_ADDR') ?: '';
$hoarder_dashboard_url = "";

// Check if hoarder-cli is installed
$output = [];
$return_var = 0;
exec('which hoarder', $output, $return_var);

if ($return_var !== 0) {
    // hoarder-cli is not installed
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Hoarder CLI is not installed. Please install it following the instructions in the documentation.']);
    exit;
}

// Check for URL parameter
if (!isset($_GET['url']) || empty($_GET['url'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'URL parameter is missing.']);
    exit;
}

$url = $_GET['url'];

// Prepare the command
$command = "bash -c 'hoarder --api-key \"$api_key\" --server-addr \"$server_addr\" bookmarks add --link \"$url\"'";

// Execute the command and capture the output
$output = [];
$return_var = 0;
exec($command . ' 2>&1', $output, $return_var); // Capture standard error output as well
$output_str = implode("\n", $output);

// Log the command and its output for debugging
error_log("Executed command: $command");
error_log("Command output: $output_str");

// Check the return status
if ($return_var === 0) {
    // Successful execution, redirect to Hoarder dashboard
    header("Location: $hoarder_dashboard_url", true, 302);
    exit;
} else {
    // Command failed, return the output in a simple way
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Command failed.', 'raw_output' => $output_str]);
}
?>
