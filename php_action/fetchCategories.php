<?php 	
require_once 'core.php';

// Ensure no output before headers
ob_start();

try {
    $sql = "SELECT categories_id, categories_name, categories_active, categories_status FROM categories WHERE categories_status = 1";
    $result = $connect->query($sql);

    $output = array('data' => array());

    if($result && $result->num_rows > 0) { 
        while($row = $result->fetch_array()) {
            $categoriesId = $row[0];
            
            // active 
            if($row[2] == 1) {
                $activeCategories = "<label class='label label-success'>Available</label>";
            } else {
                $activeCategories = "<label class='label label-danger'>Not Available</label>";
            }

            $button = '<!-- Single button -->
            <div class="btn-group">
              <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Action <span class="caret"></span>
              </button>
              <ul class="dropdown-menu">
                <li><a type="button" data-toggle="modal" id="editCategoriesModalBtn" data-target="#editCategoriesModal" onclick="editCategories('.$categoriesId.')"> <i class="glyphicon glyphicon-edit"></i> Edit</a></li>
                <li><a type="button" data-toggle="modal" data-target="#removeCategoriesModal" id="removeCategoriesModalBtn" onclick="removeCategories('.$categoriesId.')"> <i class="glyphicon glyphicon-trash"></i> Remove</a></li>       
              </ul>
            </div>';

            $output['data'][] = array( 		
                $row[1],        // categories name
                $activeCategories,  // status
                $button        // button
            ); 	
        }
    }

    $connect->close();

    // Clear any previous output
    ob_clean();

    // Set JSON header
    header('Content-Type: application/json; charset=utf-8');

    // Return JSON response
    echo json_encode($output, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Clear output buffer
    ob_clean();
    
    // Set error response
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: application/json; charset=utf-8');
    
    echo json_encode(array(
        'error' => true,
        'message' => 'Error: ' . $e->getMessage()
    ));
}

// End output buffer
ob_end_flush();