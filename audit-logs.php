<?php
require_once 'php_action/db_connect.php';
require_once 'php_action/AuditLogger.php';

// Initialize audit logger
$auditLogger = AuditLogger::getInstance();

// Get filters from request
$filters = [
    'user_id' => $_GET['user_id'] ?? null,
    'action' => $_GET['action'] ?? null,
    'entity_type' => $_GET['entity_type'] ?? null,
    'date_from' => $_GET['date_from'] ?? null,
    'date_to' => $_GET['date_to'] ?? null
];

// Get current page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;

// Get logs
$logs = $auditLogger->getAuditLogs($filters, $page, $perPage);
$totalLogs = $auditLogger->getTotalLogsCount($filters);
$totalPages = ceil($totalLogs / $perPage);
?>

<?php include('./includes/header.php'); ?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Audit Logs</h3>
            </div>
            <div class="panel-body">
                <!-- Filters -->
                <form method="GET" class="form-inline mb-3">
                    <div class="form-group mx-2">
                        <label for="action">Action:</label>
                        <select name="action" id="action" class="form-control">
                            <option value="">All</option>
                            <option value="login" <?php echo $filters['action'] === 'login' ? 'selected' : ''; ?>>Login</option>
                            <option value="logout" <?php echo $filters['action'] === 'logout' ? 'selected' : ''; ?>>Logout</option>
                            <option value="create" <?php echo $filters['action'] === 'create' ? 'selected' : ''; ?>>Create</option>
                            <option value="update" <?php echo $filters['action'] === 'update' ? 'selected' : ''; ?>>Update</option>
                            <option value="delete" <?php echo $filters['action'] === 'delete' ? 'selected' : ''; ?>>Delete</option>
                        </select>
                    </div>
                    <div class="form-group mx-2">
                        <label for="entity_type">Entity Type:</label>
                        <select name="entity_type" id="entity_type" class="form-control">
                            <option value="">All</option>
                            <option value="user" <?php echo $filters['entity_type'] === 'user' ? 'selected' : ''; ?>>User</option>
                            <option value="product" <?php echo $filters['entity_type'] === 'product' ? 'selected' : ''; ?>>Product</option>
                            <option value="order" <?php echo $filters['entity_type'] === 'order' ? 'selected' : ''; ?>>Order</option>
                        </select>
                    </div>
                    <div class="form-group mx-2">
                        <label for="date_from">From:</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" value="<?php echo $filters['date_from']; ?>">
                    </div>
                    <div class="form-group mx-2">
                        <label for="date_to">To:</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" value="<?php echo $filters['date_to']; ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="audit-logs.php" class="btn btn-default">Reset</a>
                </form>

                <!-- Logs Table -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Entity Type</th>
                                <th>Entity ID</th>
                                <th>IP Address</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($log['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($log['username']); ?></td>
                                <td><?php echo htmlspecialchars($log['action']); ?></td>
                                <td><?php echo htmlspecialchars($log['entity_type']); ?></td>
                                <td><?php echo htmlspecialchars($log['entity_id']); ?></td>
                                <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                <td>
                                    <?php if ($log['old_values'] || $log['new_values']): ?>
                                    <button type="button" class="btn btn-sm btn-info" 
                                            data-toggle="modal" 
                                            data-target="#detailsModal<?php echo $log['log_id']; ?>">
                                        View Details
                                    </button>
                                    
                                    <!-- Details Modal -->
                                    <div class="modal fade" id="detailsModal<?php echo $log['log_id']; ?>" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    <h4 class="modal-title">Log Details</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <?php if ($log['old_values']): ?>
                                                    <h5>Previous Values:</h5>
                                                    <pre><?php echo htmlspecialchars(json_encode($log['old_values'], JSON_PRETTY_PRINT)); ?></pre>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($log['new_values']): ?>
                                                    <h5>New Values:</h5>
                                                    <pre><?php echo htmlspecialchars(json_encode($log['new_values'], JSON_PRETTY_PRINT)); ?></pre>
                                                    <?php endif; ?>
                                                    
                                                    <h5>Additional Information:</h5>
                                                    <p><strong>User Agent:</strong> <?php echo htmlspecialchars($log['user_agent']); ?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                        <li>
                            <a href="?page=<?php echo ($page - 1); ?>&<?php echo http_build_query(array_filter($filters)); ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="<?php echo $i === $page ? 'active' : ''; ?>">
                            <a href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_filter($filters)); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                        <li>
                            <a href="?page=<?php echo ($page + 1); ?>&<?php echo http_build_query(array_filter($filters)); ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include('./includes/footer.php'); ?>
