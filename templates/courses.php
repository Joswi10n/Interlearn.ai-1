<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

$sql = "SELECT * FROM user WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$class = $row['class'];
$syllabus = $row['syllabus'];
$stmt->close();

$sql_chapters = "SELECT subject, chapter_name, path FROM chapters WHERE class = ? AND syllabus = ?";
$stmt_chapters = $conn->prepare($sql_chapters);
$stmt_chapters->bind_param("ss", $class, $syllabus);
$stmt_chapters->execute();
$result_chapters = $stmt_chapters->get_result();

$chapters_by_subject = array();

while ($chapter = $result_chapters->fetch_assoc()) {
    $subject = ucwords($chapter['subject']);
    $chapter_name = $chapter['chapter_name'];
    $path = $chapter['path'];
    
    if (!isset($chapters_by_subject[$subject])) {
        $chapters_by_subject[$subject] = array();
    }
    
    $chapters_by_subject[$subject][] = array('name' => $chapter_name, 'path' => $path);
}
$stmt_chapters->close();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <title>Interlearn - Courses</title>
    <link rel="stylesheet" href="courses2.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div class="container">
        <div class="banner">
            <div class="navbar">
                <img src="images/logo.png" class="logo">
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="courses.php">Courses</a></li>
                    <li><a href="about.html">About</a></li>
                </ul>
            </div>
            <div class="content">
                <h3>Explore our available subjects and start learning today!</h3>
                <?php foreach ($chapters_by_subject as $subject => $chapters) { ?>
                    <section class="subject-section">
                        <h2><?php echo strtoupper($subject); ?></h2>
                        <ul>
                            <?php foreach ($chapters as $chapter) { ?>
                                <li><a href="topiclearn.php?chapter=<?php echo urlencode($chapter['name']); ?>"><?php echo $chapter['name']; ?></a></li>
                            <?php } ?>
                        </ul>
                    </section>
                <?php } ?>
            </div>
        </div>
    </div>
</body>

</html>
