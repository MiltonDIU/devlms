<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $DB, $PAGE, $OUTPUT, $USER;

// Ensure the user is logged in
require_login();

// Check if the user is an admin
if (!is_siteadmin()) {
    throw new moodle_exception('nopermissions', 'error', '', null, 'You do not have permission to view this page.');
}

// SQL query to retrieve backup files along with associated user information
$sql = "
    SELECT f.id AS file_id, 
           f.userid AS backup_creator, 
           u.username AS creator_username, 
           f.itemid AS course_id, 
           c.fullname AS course_name, 
           f.filename AS backup_filename, 
           FROM_UNIXTIME(f.timecreated, '%Y-%m-%d %H:%i:%s') AS backup_creation_time, 
           f.filesize AS backup_size 
    FROM {files} f 
    JOIN {user} u ON f.userid = u.id 
    JOIN {course} c ON f.itemid = c.id 
    ORDER BY f.filesize DESC";

// Execute the SQL query
$backupRecords = $DB->get_records_sql($sql);

// Print the results
echo $OUTPUT->header();

if (empty($backupRecords)) {
    echo "<p>No backup records found.</p>";
} else {
    $newArrays = [];

    foreach ($backupRecords as $backup) {
        // Create a unique key based on creator username
        $key = $backup->creator_username;

        // If the key exists in the new array, increment the backup size
        if (isset($newArrays[$key])) {
            $newArrays[$key]['total_backup_size'] += $backup->backup_size; // Sum the sizes
        } else {
            // Initialize a new entry in the array
            $newArrays[$key] = [
                'backup_creator' => $backup->creator_username,
                'total_backup_size' => $backup->backup_size, // Set initial size
            ];
        }
    }

    // Initialize an array to hold the total backup size for each user
    $totalBackupSizes = [];

    // Loop through the new array to aggregate sizes by username
    foreach ($newArrays as $backup) {
        // If the user already exists in the array, add to their total size
        if (isset($totalBackupSizes[$backup['backup_creator']])) {
            $totalBackupSizes[$backup['backup_creator']] += $backup['total_backup_size'];
        } else {
            // Otherwise, initialize their total size
            $totalBackupSizes[$backup['backup_creator']] = $backup['total_backup_size'];
        }
    }

    // Sort the total backup sizes in descending order
    arsort($totalBackupSizes);

    // Print the results
    echo "<h3>User Backups</h3>";
    echo '<table class="table">';
    echo '<thead><tr>
            <th>Username</th>
            <th>Total Backup Size</th>
        </tr></thead>';
    echo '<tbody>';

    // Loop through the total sizes and print each user
    foreach ($totalBackupSizes as $username => $totalSize) {
        echo '<tr>';
        echo '<td>' . $username . '</td>';
        echo '<td>' . formatSize($totalSize) . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
}

echo $OUTPUT->footer();

/* Helper function to format file sizes */
function formatSize($bytes) {
    if ($bytes < 1024) return $bytes . ' B';
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes, 1024));
    return round($bytes / (1024 ** $i), 2) . ' ' . $units[$i];
}
?>
