<?php declare(strict_types=1);

use App\Database\DB;
use App\Api\Response;
use App\Api\Checks;
use App\Logs\SystemLog;

$checks = new Checks($vars, $_POST);

// Special XOR check for the delete records endpoint
if (!isset($_POST['table'], $_POST['id']) xor isset($_POST['deleteRecords'], $_POST['row'])) {
    Response::output('Incorrect arguments', 400);
}

$checks->apiChecks();

$db = new DB();

$pdo = $db->getConnection();

// If a single delete somes from the button
if (isset($_POST['table'], $_POST['id'])) {

    // Step 2: Delete the record
    $stmt = $pdo->prepare("DELETE FROM " . $_POST['table'] . " WHERE id = ?");
    try {
        $stmt->execute([$_POST['id']]);
        $rowsAffected = $stmt->rowCount(); // Check how many rows were deleted
        
        if ($rowsAffected > 0) {
            SystemLog::write('Record with id ' . $_POST['id'] . ' deleted from ' . $_POST['table'], 'DataGrid Delete');
            Response::output('Successfully deleted the record with id ' . $_POST['id'] . ' from ' . $_POST['table']);
        } else {
            SystemLog::write('No record found with id ' . $_POST['id'] . ' in ' . $_POST['table'], 'DataGrid Delete');
            echo Response::output('No record was deleted; it might not exist', 404);
        }
    } catch (\PDOException $e) {
        Response::output('Failed to delete the record', 400);
    }
} elseif (isset($_POST['deleteRecords'], $_POST['row'])) {

    $ids = implode(',', array_fill(0, count($_POST['row']), '?'));

    // Construct the DELETE statement
    $sql = "DELETE FROM " . $_POST['deleteRecords'] . " WHERE id IN ($ids)";

    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute($_POST['row']);
        SystemLog::write('Records with ids ' . implode(',', $_POST['row']) . ' deleted from ' . $_POST['deleteRecords'], 'DataGrid Delete');
        Response::output('Successfully deleted the records');
    } catch (\PDOException $e) {
        Response::output('Failed to delete the records', 400);
    }
       
} else {
    Response::output('Incorrect arguments', 400);
}
