<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $DB, $PAGE, $OUTPUT, $USER;

// Your existing CSS and JS includes remain unchanged

/* Check user is logged_in */
if (!isloggedin()) {
    redirect(get_login_url());
}

/* Check user is admin */
$is_admin = is_siteadmin();
if (!$is_admin) {
    redirect($CFG->wwwroot . '/local/agreement/index.php', 'You do not have permission to view this page');
    exit();
}

// Number of records per page
$recordsPerPage = 100;
$maxUsers = 1000;
// Get the current page number from the URL parameter
$page = optional_param('page', 1, PARAM_INT);

// Calculate the offset for the SQL query based on the current page and number of records per page
$offset = ($page - 1) * $recordsPerPage;

// Modify your SQL query to include LIMIT and OFFSET clauses for pagination
// $sql = "SELECT u.id, u.username, SUM(f.filesize) AS total_upload_size
//         FROM {user} u
//         LEFT JOIN {files} f ON u.id = f.userid
//         GROUP BY u.id, u.username
//         ORDER BY total_upload_size DESC";

$sql = "SELECT u.id, u.username, FROM_UNIXTIME(u.timecreated) as created_time, SUM(f.filesize) AS total_upload_size
        FROM {user} u
        LEFT JOIN {files} f ON u.id = f.userid
        WHERE u.timecreated <= UNIX_TIMESTAMP('2020-01-01 00:00:00')
        GROUP BY u.id, u.username
        ORDER BY created_time DESC";


$allrecords = $DB->get_records_sql($sql);


// Fetch total records count
$totalRecords = count($DB->get_records_sql($sql));

// Calculate total pages
$totalPages = ceil($totalRecords / $recordsPerPage);

// Fetch records for the current page
$sqlPaginated = $sql . " LIMIT $recordsPerPage OFFSET $offset";
$records = $DB->get_records_sql($sqlPaginated);

echo $OUTPUT->header();

// Display the report of user file uploads
$totalFileSize = 0;

foreach ($allrecords as $upload) {
    if(gettype($upload->total_upload_size)!=NULL){
        $totalFileSize += $upload->total_upload_size;
    }
}

echo "<h3>Total File size". formatSize($totalFileSize)."</h3>";

echo '<h2>User File Uploads</h2>';
echo '<table class="table">';
echo '<thead><tr><th>User ID</th><th>Username</th><th>Total Uploads</th><th>Created Time</th></tr></thead>';
echo '<tbody>';


// ================






$totalUploadSize = 0; // Initialize a variable to keep track of the total upload size

foreach ($records as $upload) {
    echo '<tr>';
    echo '<td>' . $upload->id . '</td>';
    echo '<td>' . $upload->username . '</td>';
    echo '<td>' . formatSize($upload->total_upload_size) . '</td>';
    echo '<td>' . $upload->created_time . '</td>';
    echo '</tr>';

    $totalUploadSize += $upload->total_upload_size; // Update the total upload size
}

echo '</tbody>';
echo '<tfoot>';
echo '<tr>';
echo '<td colspan="2">Total:</td>';
echo '<td>' . formatSize($totalUploadSize) . '</td>';
echo '<td></td>'; // Leave the "Created Time" column empty for the total row
echo '</tr>';
echo '</tfoot>';
echo '</table>';

// Display pagination links
echo '<div class="pagination">';
for ($i = 1; $i <= $totalPages; $i++) {

    echo '<a href="?page=' . $i . '">' . $i . '</a>';
}
echo '</div>';

echo $OUTPUT->footer();

function formatSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes, 1024));
    return round($bytes / (1024 ** $i), 2) . ' ' . $units[$i];
}



?>
<style>
    .pagination a {
        padding: 10px;
    }
</style>
