<?php 	

// include database and object files
require_once 'core.php';

// query products
$sql = "SELECT p.product_id, p.product_name, p.product_image, p.brand_id,
        p.categories_id, p.quantity, p.rate, p.active, p.status, 
        b.brand_name, c.categories_name 
        FROM product p
        INNER JOIN brands b ON p.brand_id = b.brand_id 
        INNER JOIN categories c ON p.categories_id = c.categories_id  
        WHERE p.status = 1 AND p.quantity > 0";

$result = $connect->query($sql);

// initialize output array
$output = array('data' => array());

// check if products exist
if($result->num_rows > 0) { 
    // loop through products
    while($row = $result->fetch_array()) {
        $productId = $row[0];
        
        // active status
        $active = ($row[7] == 1) 
            ? "<label class='label label-success'>Available</label>"
            : "<label class='label label-danger'>Not Available</label>";

        // product image
        $imageUrl = substr($row[2], 3);
        $productImage = "<img src='".$imageUrl."' style='height:30px; width:30px;'>";

        // action buttons
        $button = '
        <div class="btn-group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Action <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li><a type="button" data-toggle="modal" id="editProductModalBtn" data-target="#editProductModal" onclick="editProduct('.$productId.')"> <i class="glyphicon glyphicon-edit"></i> Edit</a></li>
                <li><a type="button" data-toggle="modal" data-target="#removeProductModal" id="removeProductModalBtn" onclick="removeProduct('.$productId.')"> <i class="glyphicon glyphicon-trash"></i> Remove</a></li>       
            </ul>
        </div>';

        $output['data'][] = array( 		
            $productImage,
            $row[1], // product name
            $row[9], // brand name
            $row[10], // category name
            $row[5], // quantity
            $row[6], // rate
            $active,
            $button
        );
    }
}

// close database connection
$connect->close();

// output products in json format
echo json_encode($output);