<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "manga";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Fix Existing Manga submitted_by Field</h1>";

if (!isset($_SESSION['user_id'])) {
    die("<p style='color: red;'>You need to be logged in!</p>");
}

$current_user_id = $_SESSION['user_id'];
echo "<p>Current user ID: $current_user_id</p>";

$mangaQuery = "SELECT id, title, submitted_by FROM manga WHERE submitted_by IS NULL ORDER BY created_at DESC";
$mangaResult = $conn->query($mangaQuery);

if ($mangaResult && $mangaResult->num_rows > 0) {
    echo "<h3>Manga with NULL submitted_by field:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Manga ID</th><th>Title</th><th>Current submitted_by</th><th>Action</th></tr>";
    
    while ($manga = $mangaResult->fetch_assoc()) {
        $mangaId = $manga['id'];
        $mangaTitle = htmlspecialchars($manga['title']);
        
        echo "<tr>";
        echo "<td>$mangaId</td>";
        echo "<td>$mangaTitle</td>";
        echo "<td>" . ($manga['submitted_by'] ?? 'NULL') . "</td>";
        echo "<td><a href='?fix_manga=$mangaId' style='background: #007bff; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;'>Fix This</a></td>";
        echo "</tr>";
    }
    echo "</table>";

    if (isset($_GET['fix_manga'])) {
        $mangaIdToFix = intval($_GET['fix_manga']);
        
        $updateQuery = "UPDATE manga SET submitted_by = ? WHERE id = ? AND submitted_by IS NULL";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("ii", $current_user_id, $mangaIdToFix);
        
        if ($updateStmt->execute()) {
            if ($updateStmt->affected_rows > 0) {
                echo "<p style='color: green;'>✅ Successfully updated manga ID $mangaIdToFix with your user ID ($current_user_id)!</p>";
                echo "<script>setTimeout(function(){ window.location.href = window.location.pathname; }, 2000);</script>";
            } else {
                echo "<p style='color: orange;'>⚠️ No rows were updated. Manga might already be fixed or doesn't exist.</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Failed to update manga: " . $updateStmt->error . "</p>";
        }
        $updateStmt->close();
    }
    
} else {
    echo "<p style='color: green;'>✅ All manga have submitted_by field set!</p>";
}

echo "<h3>Current manga state:</h3>";
$allMangaQuery = "SELECT id, title, submitted_by FROM manga ORDER BY created_at DESC LIMIT 10";
$allMangaResult = $conn->query($allMangaQuery);

if ($allMangaResult && $allMangaResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Manga ID</th><th>Title</th><th>submitted_by</th></tr>";
    
    while ($manga = $allMangaResult->fetch_assoc()) {
        $highlight = ($manga['submitted_by'] == $current_user_id) ? " style='background-color: #d4edda;'" : "";
        echo "<tr$highlight>";
        echo "<td>" . $manga['id'] . "</td>";
        echo "<td>" . htmlspecialchars($manga['title']) . "</td>";
        echo "<td>" . ($manga['submitted_by'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

$conn->close();
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { margin: 10px 0; width: 100%; }
    th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
    th { background-color: #f0f0f0; }
</style>