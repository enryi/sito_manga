<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "manga";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT id, title, image_url FROM manga WHERE approved = 1 ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $mangaItems = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $mangaItems[] = $row;
        }
    }
    $conn->close();

    $columns = 4;
    $totalManga = count($mangaItems);

    if ($totalManga > 0) {
        $emptyDivCount = $columns - ($totalManga % $columns);
        foreach ($mangaItems as $manga) {
            $mangaTitleSlug = strtolower(str_replace(' ', '_', $manga['title']));
            $mangaPageUrl = "series/" . $mangaTitleSlug;
            echo '<div class="manga-item" onclick="window.location.href=\'' . htmlspecialchars($mangaPageUrl) . '\'">';
            echo '<img src="' . htmlspecialchars($manga['image_url']) . '" alt="' . htmlspecialchars($manga['title']) . '">';
            echo '<p class="manga-title">' . htmlspecialchars($manga['title']) . '</p>';
            echo '</div>';
        }
        if ($emptyDivCount < $columns) {
            for ($i = 0; $i < $emptyDivCount; $i++) {
                echo '<div class="manga-item-fake"></div>';
            }
        }
    } else {
        echo '<p class="no-data">No manga found.</p>';
    }
?>