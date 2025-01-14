<?php 
// Prevent any output before headers
ob_start();

// Set error reporting to exclude deprecation notices
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

require_once 'core.php';
require_once 'csrf_utils.php';
require_once '../libraries/phpexcel/PHPExcel.php';
require_once '../libraries/phpexcel/PHPExcel/IOFactory.php';

// Clear any previous output
ob_clean();

// Set JSON headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

$valid['success'] = array('success' => false, 'messages' => array());

try {
    // Debug log for request
    error_log("Received request: " . print_r($_POST, true));
    error_log("Files: " . print_r($_FILES, true));

    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    // Validate CSRF token
    if (!isset($_POST['csrf_token'])) {
        throw new Exception("CSRF token is missing");
    }

    if (!CSRFProtection::validateToken()) {
        throw new Exception("Invalid CSRF token");
    }

    if(!isset($_FILES['brandfile']) || !is_uploaded_file($_FILES['brandfile']['tmp_name'])) {
        throw new Exception("No file was uploaded or invalid file upload");
    }

    // Validate file type
    $allowed_types = array('csv', 'xls', 'xlsx');
    $file_info = pathinfo($_FILES['brandfile']['name']);
    $extension = strtolower($file_info['extension']);

    if (!in_array($extension, $allowed_types)) {
        throw new Exception("Invalid file type. Only CSV, XLS, and XLSX files are allowed.");
    }

    // Create upload directory if it doesn't exist
    $upload_dir = '../assests/images/stock/';
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            throw new Exception("Failed to create upload directory");
        }
    }

    // Generate safe filename
    $filename = 'brand_' . uniqid() . '.' . $extension;
    $filepath = $upload_dir . $filename;

    // Move uploaded file
    if (!move_uploaded_file($_FILES['brandfile']['tmp_name'], $filepath)) {
        throw new Exception("Failed to move uploaded file");
    }

    // Suppress deprecation warnings for PHPExcel
    $errorReporting = error_reporting();
    error_reporting($errorReporting & ~E_DEPRECATED & ~E_STRICT);
    
    try {
        // Load Excel file
        $objPHPExcel = PHPExcel_IOFactory::load($filepath);
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = 'B';
        
        // Prepare statements
        $select_stmt = $connect->prepare("SELECT * FROM brands WHERE brand_name = ?");
        $insert_stmt = $connect->prepare("INSERT INTO brands (brand_name, brand_active, brand_status) VALUES (?, ?, ?)");

        $success_count = 0;
        $error_count = 0;

        // Process each row
        for ($row = 2; $row <= $highestRow; $row++) {
            try {
                $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, FALSE, TRUE);
                
                if(!isset($rowData[0][0]) || empty($rowData[0][0]) || !isset($rowData[0][1])) {
                    $error_count++;
                    continue;
                }

                $brand_name = trim($rowData[0][0]);
                $brand_status = intval($rowData[0][1]);

                // Validate brand status
                if($brand_status !== 1 && $brand_status !== 2) {
                    $error_count++;
                    continue;
                }

                // Check if brand exists
                $select_stmt->bind_param("s", $brand_name);
                $select_stmt->execute();
                $result = $select_stmt->get_result();

                if($result->num_rows == 0) {
                    // Insert new brand
                    $insert_stmt->bind_param("sii", $brand_name, $brand_status, $brand_status);
                    if($insert_stmt->execute()) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                } else {
                    // Brand already exists, count as success
                    $success_count++;
                }
            } catch (Exception $e) {
                error_log("Error processing row $row: " . $e->getMessage());
                $error_count++;
            }
        }
    } finally {
        // Restore error reporting
        error_reporting($errorReporting);
    }

    // Clean up
    $select_stmt->close();
    $insert_stmt->close();
    unlink($filepath); // Delete the temporary file

    if($success_count > 0) {
        $valid['success'] = true;
        $valid['messages'] = "Successfully imported $success_count brands" . 
            ($error_count > 0 ? " ($error_count errors encountered)" : "");
    } else {
        throw new Exception("No brands were imported successfully" . 
            ($error_count > 0 ? " ($error_count errors encountered)" : ""));
    }

} catch (Exception $e) {
    error_log("Error in createBrandImport.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    $valid['success'] = false;
    $valid['messages'] = $e->getMessage();
}

// Clear any buffered output
while (ob_get_level()) {
    ob_end_clean();
}

// Send JSON response
echo json_encode($valid);
exit();