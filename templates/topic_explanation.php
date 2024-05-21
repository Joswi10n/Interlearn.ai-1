<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['topic']) || !isset($_GET['chapter']) || !isset($_GET['path'])) {
    header("Location: courses.php");
    exit;
}

$selected_topic = $_GET['topic'];
$selected_chapter = $_GET['chapter'];
$path = $_GET['path'];
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <title>Interlearn - Topic Explanation</title>
    <link rel="stylesheet" href="topicexplanation1.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div class="banner">
        <div class="container">
            <div class="title">Topic Explanation: <?php echo $selected_topic; ?></div>
            <div class="content">
                <h1>Chapter: <?php echo $selected_chapter; ?></h1>
                <form id="explanationForm" action="display_explanation.php" method="post" onsubmit="showLoader()">
                    <label for="difficulty">Select Difficulty:</label>
                    <select name="difficulty" id="difficulty">
                        <option value="1">Beginner</option>
                        <option value="2">Intermediate</option>
                        <option value="3">Advanced</option>
                    </select>
                    <input type="hidden" name="topic" value="<?php echo $selected_topic; ?>">
                    <input type="hidden" name="chapter" value="<?php echo $selected_chapter; ?>">
                    <input type="hidden" name="path" value="<?php echo $path; ?>">
                    <input type="submit" value="Submit">
                </form>
            </div>
            <div id="loading" class="loader-container" style="display:none;">
                <div class="loader"></div>
                <p>Video is being generated....</p>
            </div>
            <div class='content1'>
                <h3>Beginner level:Simple words,not in depth explanation</h3>
                <h3>Intermediate level:Moderate level of explanation</h3>
                <h3>Advanced level:In depth explanation</h3>
            </div>
        </div>
    </div>
    <div class="back">
        <a href="topicby.php?chapter=<?php echo urlencode($selected_chapter); ?>">Back to Topics</a>
    </div> 
</body>
<script>
function showLoader() {
    document.getElementById("loading").style.display = "flex";
}

window.onload = function() {
    document.getElementById("loading").style.display = "none";
};

document.getElementById('explanationForm').addEventListener('submit', showLoader);
</script>

</html>
