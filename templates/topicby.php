<?php
session_start();
include 'connect.php';

// Enable detailed error reporting
ini_set('error_reporting', E_ALL);

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['chapter'])) {
    header("Location: courses.php");
    exit;
}

$selected_chapter = $_GET['chapter'];

// Retrieve path from the database based on the selected chapter name
$sql_path = "SELECT path FROM chapters WHERE chapter_name = ?";
$stmt = $conn->prepare($sql_path);
$stmt->bind_param("s", $selected_chapter);
$stmt->execute();
$result = $stmt->get_result();
$path_row = $result->fetch_assoc();
$path = $path_row['path'];
$stmt->close();

// Check for cURL errors
if (!function_exists('curl_init')) {
    $error_message = "cURL is not installed or enabled. Please install cURL for this script to function.";
} else {
    // Call the Flask application using cURL
    $url = "http://127.0.0.1:5000/display_topics/" . urlencode($path);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $url
    ));

    $output = curl_exec($curl);

    // Check for cURL execution errors
    if ($output === false) {
        $error_message = "Error during cURL execution: " . curl_error($curl);
    } else {
        curl_close($curl);
        $response = json_decode($output, true);
        $topics = isset($response['topics']) ? $response['topics'] : []; // Check if topics exist, otherwise initialize as empty array
    }
}

// Check for PHP errors and send them (optional)
if (count(get_included_files()) && error_get_last()) {
    $error_message = "PHP Error: " . error_get_last()['message'] . " in " . error_get_last()['file'] . " on line " . error_get_last()['line'];

    // Send the error message to an email address or logging service (modify as needed)
    // For example:
    // mail('admin@example.com', 'PHP Error in Topic Learning Script', $error_message);
    // You can also log the error to a file using error_log() function.
}

// Assign the error message to a variable for HTML display
$html_error_message = isset($error_message) ? "<p class='error-message'>" . $error_message . "</p>" : "";
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <title>Interlearn - Topic Learning</title>
    <link rel="stylesheet" href="topicby1.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .error-message {
            color: red;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="banner">
        <div class="container">
            <div class="title">Topic by Topic Learning</div>
            <div class="content">
                <?php echo $html_error_message; ?> <h3>Topics:</h3>
                <div class="button-section">
                    <?php if (!empty($topics)) {
                        foreach ($topics as $topic) { ?>
                            <button class="btn" onclick="window.location.href='topic_explanation.php?topic=<?php echo urlencode($topic); ?>&chapter=<?php echo urlencode($selected_chapter); ?>&path=<?php echo urlencode($path); ?>';">
                                <?php echo $topic; ?>
                            </button>
                        <?php }
                    } else { ?>
                        <p>No topics found.</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
