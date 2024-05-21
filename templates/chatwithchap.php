<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['chapter'])) {
    header("Location: courses.php");
    exit;
}

$selected_chapter = $_GET['chapter'];

// Retrieve chapter path from the database based on the selected chapter
$sql_path = "SELECT path FROM chapters WHERE chapter_name = ?";
$stmt = $conn->prepare($sql_path);
$stmt->bind_param("s", $selected_chapter);
$stmt->execute();
$result = $stmt->get_result();
$path_row = $result->fetch_assoc();
$chapter_path = $path_row['path'];
$stmt->close();

// Function to send question via cURL
function sendQuestion($question, $chapter_path, $response_length) {
    $url = "http://127.0.0.1:5000/handle_chat";
    $data = array('question' => $question, 'chapter_path' => $chapter_path, 'response_length' => $response_length);
    $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => array('Content-Type: application/json')
    );

    $curl = curl_init($url);
    curl_setopt_array($curl, $options);
    $response = curl_exec($curl);
    curl_close($curl);

    return $response;
}

$explanation = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['question']) && !empty($_POST['question'])) {
    $question = $_POST['question'];
    $response_length = $_POST['response_length']; // Added response length selection
    $answer = sendQuestion($question, $chapter_path, $response_length);
    // Append the question and answer to the chat box
    $explanation .= '<div class="question">' . htmlspecialchars($question) . '</div>';
    $explanation .= '<div class="answer">' . htmlspecialchars($answer) . '</div>';
    // Store question-response pair in the qa dictionary
    $qa[] = array('question' => $question, 'response' => $answer);
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <title>Interlearn - Chat with your Chapter</title>
    <link rel="stylesheet" href="chatwithchap.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div class="container">
        <div class="title">Chat with Chapter: <?php echo $selected_chapter; ?></div>
        <div class="content">
            <!-- Chat box -->
            <div id="chat-box">
                <?php
                if (!empty($qa)) {
                    foreach ($qa as $qa_pair) {
                        // Parse JSON response
                        $response_data = json_decode($qa_pair['response'], true);
                        // Loop through response data and format questions and answers
                        foreach ($response_data as $index => $qa_data) {
                            echo '<div class="question">' . $index . '. ' . htmlspecialchars($qa_data[0]) . '</div>';
                            echo '<div class="answer">- ' . htmlspecialchars($qa_data[1]) . '</div>';
                        }
                    }
                }
                ?>
            </div>
            <!-- Form to submit questions -->
            <form id="question-form" method="post">
                <input type="text" id="question-input" name="question" placeholder="Enter your question...">
                <!-- Added response length selection -->
                <select name="response_length">
                    <option value="short">Short</option>
                    <option value="medium">Medium</option>
                    <option value="long">Long</option>
                </select>
                <button type="submit">Send</button>
            </form>
        </div>
    </div>
    <div class="back">
        <a href="courses.php">Back to Courses</a>
    </div>
</body>

</html>
