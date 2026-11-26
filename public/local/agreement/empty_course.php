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

echo $OUTPUT->header();

// Display the report of user file uploads
// Function to get course list, count users, and calculate file size by category ID
function getCoursesAndFilesInfo($categoryID) {
    global $DB;

    $courses = get_courses($categoryID, 'c.sortorder ASC', 'c.id, c.fullname');

    $courseList = array();
    $totalFilesSize = 0;

    foreach ($courses as $course) {
        $usersCount = count_enrolled_users(context_course::instance($course->id));

        // Calculate total file size for each course
        $courseFilesSize = 0;
        $courseFiles = $DB->get_records_sql("SELECT contenthash, filesize FROM {files} WHERE contextid = :contextid", array('contextid' => context_course::instance($course->id)->id));

        foreach ($courseFiles as $file) {
            $courseFilesSize += $file->filesize;
        }

        // Format file size
        $formattedFileSize = format_size($courseFilesSize);

        $courseList[] = array(
            'id' => $course->id,
            'fullname' => $course->fullname,
            'usersCount' => $usersCount,
            'filesSize' => $formattedFileSize
        );

        // Accumulate the file size for total calculation
        $totalFilesSize += $courseFilesSize;
    }

    // Format total file size
    $formattedTotalFileSize = format_size($totalFilesSize);

    return array('courses' => $courseList, 'totalFilesSize' => $formattedTotalFileSize);
}

// Provide your course category ID here
$categoryID = 301; // Replace with your actual category ID

// Get courses, users count, and files info for the specified category
$result = getCoursesAndFilesInfo($categoryID);
$courseList = $result['courses'];
$totalFilesSize = $result['totalFilesSize'];

// Output the list or a message if no courses found
echo '<h2>Courses in Category:</h2>';

if (empty($courseList)) {
    echo '<p>No courses found in this category.</p>';
} else {
    echo '<ul>';
    foreach ($courseList as $course) {
        echo '<li>' . $course['fullname'] . ' - Users: ' . $course['usersCount'] . ' - Files Size: ' . $course['filesSize'] . '</li>';
    }
    echo '</ul>';

    // Output total file size for all courses
    echo '<p>Total Files Size for all Courses: ' . $totalFilesSize . '</p>';
}

function format_size($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes, 1024));
    return round($bytes / (1024 ** $i), 2) . ' ' . $units[$i];
}



echo $OUTPUT->footer();




?>
<style>
    .pagination a {
  padding: 10px;
}
</style>
