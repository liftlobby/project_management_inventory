<?php 	
require_once 'core.php';
require_once 'security_utils.php';

try {
    $sql = "SELECT brand_id, brand_name, brand_active, brand_status FROM brands WHERE brand_status = 1";
    $stmt = SecurityUtils::prepareAndExecute($sql, "", []);
    $result = $stmt->get_result();

    $output = array('data' => array());

    if($result->num_rows > 0) { 
        while($row = $result->fetch_array()) {
            $brandId = $row[0];
            // active 
            if($row[2] == 1) {
                $activeBrands = "<label class='label label-success'>Available</label>";
            } else {
                $activeBrands = "<label class='label label-danger'>Not Available</label>";
            }

            $button = '<!-- Single button -->
            <div class="btn-group">
              <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Action <span class="caret"></span>
              </button>
              <ul class="dropdown-menu">
                <li><a type="button" data-toggle="modal" data-target="#editBrandModal" onclick="editBrands('.htmlspecialchars($brandId, ENT_QUOTES).')"> <i class="glyphicon glyphicon-edit"></i> Edit</a></li>
                <li><a type="button" data-toggle="modal" data-target="#removeMemberModal" onclick="removeBrands('.htmlspecialchars($brandId, ENT_QUOTES).')"> <i class="glyphicon glyphicon-trash"></i> Remove</a></li>       
              </ul>
            </div>';

            $output['data'][] = array( 		
                htmlspecialchars($row[1]), 		
                $activeBrands,
                $button
            ); 	
        }
    }

    echo json_encode($output);
} catch (Exception $e) {
    error_log("Error in fetchBrand.php: " . $e->getMessage());
    echo json_encode(array('data' => array(), 'error' => 'An error occurred while fetching brands'));
}