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

// Function to get course list, count users, calculate file size, and get teacher info by category ID
function getCoursesAndFilesInfo($categoryID) {
    global $DB;

    $courses = get_courses('c.sortorder ASC', 'c.id, c.fullname');

    $courseList = array();
    $totalFilesSize = 0;

    foreach ($courses as $course) {
        $usersCount = count_enrolled_users(context_course::instance($course->id));

        // Get teacher info
        $teacherNames = array();
        $teacherEmails = array();
        $context = context_course::instance($course->id);

        // Find role ID for 'editingteacher' role
        $editingTeacherRoleId = $DB->get_field('role', 'id', ['shortname' => 'editingteacher']);

        // Get users with the 'editingteacher' role
        $teachers = get_role_users($editingTeacherRoleId, $context);

        foreach ($teachers as $teacher) {
            $teacherNames[] = fullname($teacher);
            $teacherEmails[] = $teacher->email;
        }

        // Calculate total file size for each course
        $courseFilesSize = 0;
        $courseFiles = $DB->get_records_sql("SELECT contenthash, filesize FROM {files} WHERE contextid = :contextid", array('contextid' => $context->id));

        foreach ($courseFiles as $file) {
            $courseFilesSize += $file->filesize;
        }

        // Format file size
        $formattedFileSize = format_size($courseFilesSize);

        $courseList[] = array(
            'id' => $course->id,
            'fullname' => $course->fullname,
            'usersCount' => $usersCount,
            'teacherNames' => implode(', ', $teacherNames),
            'teacherEmails' => implode(', ', $teacherEmails),
            'filesSize' => $formattedFileSize
        );

        // Accumulate the file size for total calculation
        if ($usersCount > 0) {
            $totalFilesSize += $courseFilesSize;
        }
    }

    // Format total file size
    $formattedTotalFileSize = format_size($totalFilesSize);

    return array('courses' => $courseList, 'totalFilesSize' => $formattedTotalFileSize);
}

// Provide your course category ID here
$categoryID = 301; // Replace with your actual category ID

// Get courses, users count, files info, and teacher info for the specified category
$result = getCoursesAndFilesInfo($categoryID);
$courseList = $result['courses'];
$totalFilesSize = $result['totalFilesSize'];

// Output the list or a message if no courses found
echo '<h2>Courses in Category:</h2>';

if (empty($courseList)) {
    echo '<p>No courses found in this category.</p>';
} else {
    echo '<table border="1">';
    echo '<thead><tr><th>Serial Number</th><th>Course Name</th><th>Users Count</th><th>Teacher Name(s)</th><th>Teacher Email(s)</th><th>Files Size</th></tr></thead>';
    echo '<tbody>';
    $serialNumber = 1;
    foreach ($courseList as $course) {
        if ($course['usersCount'] > 0) {
            echo '<tr>';
            echo '<td>' . $serialNumber++ . '</td>';
            echo '<td>' . $course['fullname'] . '</td>';
            echo '<td>' . $course['usersCount'] . '</td>';
            echo '<td>' . $course['teacherNames'] . '</td>';
            echo '<td>' . $course['teacherEmails'] . '</td>';
            echo '<td>' . $course['filesSize'] . '</td>';
            echo '</tr>';
        }
    }
    echo '</tbody>';
    echo '</table>';

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
