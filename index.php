<?php
header('Content-Type: text/html; charset=utf-8');

// --- DATABASE CONFIGURATION ---
$dbHost = "localhost:3306";
$dbUser = "USERNAME";
$dbPass = "PASSWORD";
$dbName = "DBNAME";
// -----------------------------

// --- OTHER CONFIGURATION ---
$servername = "YOUR SERVER NAME";
$mainColor = "#e94560";
$discordIcon = YOUR DISCORD ICON URL
$discordUrl= YOUR DISCORD URL
$playersPerPage = 50;
// ----------------------------------


function connectDB() {
    global $dbHost, $dbUser, $dbPass, $dbName;
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }
    return $conn;
}

function formatDuration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    return sprintf('%02dh %02dm %02ds', $hours, $minutes, $secs);
}

function formatTimeAgo($timestamp) {
    if (!$timestamp) return 'Unknown';

    $now = new DateTime();
    $then = new DateTime($timestamp);
    $diff = $now->diff($then);

    if ($diff->y > 0) {
        return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    } elseif ($diff->m > 0) {
        return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    } elseif ($diff->d >= 7) {
        $weeks = floor($diff->d / 7);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    } elseif ($diff->d > 0) {
        return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    } elseif ($diff->h > 0) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    } elseif ($diff->i > 0) {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    } else {
        return 'just now';
    }
}


$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;


$offset = ($page - 1) * $playersPerPage;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arma Reforger Stats</title>
    <style>
        :root {
            --main-color: <?php echo $mainColor; ?>;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #ffffff;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .search-section {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            backdrop-filter: blur(5px);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .search-instructions {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            margin-bottom: 10px; 
            text-align: center;
        }

        .search-box {
            max-width: 500px;
            width: 100%; 
        }

        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 4px solid var(--main-color);
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 16px;
            transition: background 0.3s ease; 
        }

        input[type="text"]::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        input[type="text"]:focus {
            background: rgba(255, 255, 255, 0.3); 
            outline: none;
        }

        .player-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        .player-card {
            background: rgba(255, 255, 255, 0.12);
            padding: 20px;
            border-left: 4px solid var(--main-color);
            border-radius: 10px;
            backdrop-filter: blur(5px);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .player-card.hidden {
            display: none;
        }
        .player-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.18);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .player-card h3 {
            color: var(--main-color);
            margin-bottom: 10px;
        }
        .player-card .stats-preview {
            margin-top: 10px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
        }
        .player-card .date-info {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 8px;
        }
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            opacity: 0;
            transition: opacity 0.5s;
        }
        .stats-container.show {
            opacity: 1;
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.12);
            padding: 20px;
            border-left: 4px solid var(--main-color);
            border-radius: 10px;
            backdrop-filter: blur(5px);
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.18);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .stat-card h3 {
            color: var(--main-color);
            margin-bottom: 15px;
        }
        .stat-item {
            margin: 8px 0;
            display: flex;
            justify-content: space-between;
        }
        .stat-label {
            color: rgba(255, 255, 255, 0.8);
        }
        .stat-value {
            font-weight: bold;
        }
        
        .stat-value-small {
            font-weight: normal; 
            font-size: 10px;
        }
		
		.floating-goat {
			position: fixed;
			bottom: 5px;
			right: 5px;
			z-index: 1000;
			cursor: pointer;
			transition: transform 0.2s;
		}

		.floating-goat img {
			width: 50px;
			height: auto;
			border-radius: 50%;
		}

		.floating-goat:hover {
			transform: scale(1.1);
		}

        .player-name {
            text-align: center;
            margin-bottom: 20px;
            font-size: 32px; 
            color: var(--main-color);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5); 
        }

        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background: var(--main-color);
            border-radius: 5px;
            color: #fff;
            text-decoration: none;
            margin-bottom: 20px;
            transition: background 0.3s;
        }
        .back-button:hover {
            background: #ff6b6b;
        }

        /* Styles for Top Players Section */
        .top-players-section {
            margin-bottom: 30px;
        }
        .top-players-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .top-players-card {
            background: rgba(255, 255, 255, 0.12);
            padding: 15px;
            border-left: 4px solid var(--main-color);
            border-radius: 10px;
            backdrop-filter: blur(5px);
            transition: all 0.3s ease;
            height: 100%; 
            display: flex;
            flex-direction: column;
        }
        .top-players-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.18);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .top-players-card h3 {
            color: var(--main-color);
            margin-bottom: 10px;
            text-align: center;
        }
        .top-players-card ol {
            list-style: none;
            padding-left: 0;
            flex-grow: 1;
            overflow: auto;
        }

        .top-players-card li {
            padding: 8px 0;
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .top-players-card li:last-child {
            border-bottom: none;
        }
        .player-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .player-link:hover {
            color: var(--main-color);
        }

        .stat-value {
            font-weight: bold;
            margin-left: 10px;
        }

        .player-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .player-summary-card {
            background: rgba(255, 255, 255, 0.12);
            padding: 15px;
            border-left: 4px solid var(--main-color);
            border-radius: 10px;
            backdrop-filter: blur(5px);
            text-align: center;
            transition: all 0.3s ease;
        }
        .player-summary-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.18);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .player-summary-card h4 {
            color: var(--main-color);
            margin-bottom: 5px;
            font-size: 18px;
        }
        .player-summary-card p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 16px;
        }

        /* Enhanced Player Info Style */
        .stat-card h3 {
            color: var(--main-color);
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px; 
            font-size: 1.2em;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.15); 
            padding: 25px; /* More padding */
            border-left: 5px solid var(--main-color);
            border-radius: 12px;
            backdrop-filter: blur(7px);
            transition: all 0.3s ease;
        }

        .more-players-button {
            display: inline-block;
            padding: 10px 20px;
            background: var(--main-color);
            border-radius: 5px;
            color: #fff;
            text-decoration: none;
            margin-top: 20px;
            transition: background 0.3s;
            cursor: pointer;
            border: none;
            font-size: 16px;
        }

        .more-players-button:hover {
            background: #ff6b6b;
        }

        @media (max-width: 768px) {
            .player-grid {
                grid-template-columns: 1fr;
            }
            .stats-container {
                grid-template-columns: 1fr;
            }
            .top-players-grid {
                grid-template-columns: 1fr;
            }

            .player-summary {
                grid-template-columns: 1fr;
            }

        }
        
        h1 a {
            text-decoration: none; 
            color: var(--main-color); 
            display: inline-block;
            transition: none; 
        }
        
        h1 a:hover {
            color: var(--main-color);
            text-decoration: none; 
            cursor: pointer; 
        }
        h2 {
            color: rgba(255, 255, 255, 0.8); 
            text-align: center;      
            margin-bottom: 40px;     
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        if (!isset($_GET['player'])) {
            echo "<h1 style='text-align: center; margin-bottom: 20px; color: var(--main-color);'><a href='index.php'>ARMA REFORGER PLAYER STATS</a></h1>";
            echo "<h2>$servername</h2>";
            // Top 10 Players Section
            echo "<div class='top-players-section'>";
            echo "<div class='top-players-grid'>";

            echo "<div class='top-players-card'>";
            echo "<h3>Top Kills</h3>";
             $conn = connectDB();
            if ($conn->connect_error) {
                echo "<li>Failed to load: " . $conn->connect_error . "</li>";
            } else {
                $sql = "SELECT p.playerName, SUM(s.kills + s.ai_kills) AS total_kills FROM players p LEFT JOIN stats s ON p.playerUID = s.playerUID GROUP BY p.playerName ORDER BY total_kills DESC LIMIT 10";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $playerName = htmlspecialchars($row['playerName']);
                        $totalKills = number_format($row['total_kills']);
                        echo "<li><a href='?player=" . urlencode($playerName) . "' class='player-link'>" . $playerName . "</a><span class='stat-value'>" . $totalKills . "</span></li>";
                    }
                } else {
                    echo "<li>No players found</li>";
                }
                $conn->close();
            }
            echo "</ol>";
            echo "</div>";

            echo "<div class='top-players-card'>";
            echo "<h3>Top Playtime</h3>";
            echo "<ol>";
             $conn = connectDB();
            if ($conn->connect_error) {
                echo "<li>Failed to load: " . $conn->connect_error . "</li>";
            } else {
                $sql = "SELECT p.playerName, SUM(s.session_duration) AS total_playtime FROM players p LEFT JOIN stats s ON p.playerUID = s.playerUID GROUP BY p.playerName ORDER BY total_playtime DESC LIMIT 10";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $playerName = htmlspecialchars($row['playerName']);
                        $playtime = formatDuration($row['total_playtime']);
                        echo "<li><a href='?player=" . urlencode($playerName) . "' class='player-link'>" . $playerName . "</a><span class='stat-value'>" . $playtime . "</span></li>";
                    }
                } else {
                    echo "<li>No players found</li>";
                }
                $conn->close();
            }
            echo "</ol>";
            echo "</div>";

            echo "<div class='top-players-card'>";
            echo "<h3>Top K/D Ratio</h3>";
            echo "<ol>";
             $conn = connectDB();
            if ($conn->connect_error) {
                echo "<li>Failed to load: " . $conn->connect_error . "</li>";
            } else {
                $sql = "SELECT p.playerName, SUM(s.kills) / SUM(s.deaths) AS kd_ratio FROM players p LEFT JOIN stats s ON p.playerUID = s.playerUID WHERE s.deaths > 0 GROUP BY p.playerName ORDER BY kd_ratio DESC LIMIT 10";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $playerName = htmlspecialchars($row['playerName']);
                        $kdRatio = number_format($row['kd_ratio'], 2);
                        echo "<li><a href='?player=" . urlencode($playerName) . "' class='player-link'>" . $playerName . "</a><span class='stat-value'>" . $kdRatio . "</span></li>";
                    }
                } else {
                    echo "<li>No players found</li>";
                }
                $conn->close();
            }
            echo "</ol>";
            echo "</div>";

            echo "<div class='top-players-card'>";
            echo "<h3>Top Accuracy</h3>";
            echo "<ol>";
            $conn = connectDB();
            if ($conn->connect_error) {
                echo "<li>Failed to load: " . $conn->connect_error . "</li>";
            } else {
                $sql = "SELECT p.playerName, (SUM(s.kills + s.ai_kills) / SUM(s.shots)) * 100 AS accuracy FROM players p LEFT JOIN stats s ON p.playerUID = s.playerUID WHERE s.shots > 0 GROUP BY p.playerName ORDER BY accuracy DESC LIMIT 10";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $playerName = htmlspecialchars($row['playerName']);
                        $accuracy = number_format($row['accuracy'], 2) . "%";
                        echo "<li><a href='?player=" . urlencode($playerName) . "' class='player-link'>" . $playerName . "</a><span class='stat-value'>" . $accuracy . "</span></li>";
                    }
                } else {
                    echo "<li>No players found</li>";
                }
                $conn->close();
            }
            echo "</ol>";
            echo "</div>";

            echo "</div>";
            echo "</div>";

            echo "<div class='search-section'>";
            echo "<div class='search-instructions'>Type at least 3 characters to start searching. The search is not case-sensitive.  Click on a player card to view their full stats.</div>";
            echo "<div class='search-box'>";
            echo "<input type='text' id='playerSearch' placeholder='Search Players'>";
            echo "</div>";
            echo "</div>";
            echo "<div class='player-grid'>";

            $conn = connectDB();

            if ($conn->connect_error) {
                echo "<div>Failed to load players: " . $conn->connect_error . "</div>";
            } else {
                // Update the SQL query to include the LIMIT and OFFSET
                $sql = "SELECT p.playerName, p.created as first_seen, s.created as last_seen,
                        s.kills, s.ai_kills, s.shots
                        FROM players p
                        LEFT JOIN stats s ON p.playerUID = s.playerUID
                        ORDER BY p.playerName ASC
                        LIMIT $playersPerPage OFFSET $offset";  // Add LIMIT and OFFSET

                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $name = htmlspecialchars($row['playerName']);
                        $totalKills = ($row['kills'] ?? 0) + ($row['ai_kills'] ?? 0);
                        $accuracy = ($row['shots'] > 0) ? number_format(($totalKills / $row['shots']) * 100, 2) : 0;
                        echo "<div class='player-card' data-name='" . strtolower($name) . "' onclick=\"window.location.href='?player=" . urlencode($name) . "'\">";
                        echo "<h3>" . $name . "</h3>";
                        echo "<div class='stats-preview'>";
                        echo "Total Kills: " . $totalKills . " | ";
                        echo "Accuracy: " . $accuracy . "%";
                        echo "</div>";
                        echo "<div class='date-info'>";
                        echo "First Seen: " . formatTimeAgo($row['first_seen']) . "<br>";
                        echo "Last Seen: " . formatTimeAgo($row['last_seen']);
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<div>No players found</div>";
                }
                $conn->close();
            }

            echo "</div>";

            echo "<div style='text-align: center;'>";
            echo "<a href='?page=" . ($page + 1) . "' class='more-players-button'>More Players</a>";
            echo "</div>";
        } else {
            $playerName = $_GET['player'];

            $conn = connectDB();

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $sql = "SELECT p.*, p.created as first_seen, s.*, s.created as last_seen, SUM(s.session_duration) AS total_playtime, SUM(s.kills) AS total_kills, SUM(s.ai_kills) AS total_ai_kills, SUM(s.deaths) AS total_deaths
                    FROM players p
                    LEFT JOIN stats s ON p.playerUID = s.playerUID
                    WHERE p.playerName = ?
                    GROUP BY p.playerUID";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $playerName);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();

                $totalKills = ($row['total_kills'] ?? 0) + ($row['total_ai_kills'] ?? 0);
                $totalDeaths = $row['total_deaths'] ?? 0;
                $playerKD = ($totalDeaths > 0) ? number_format($totalKills / $totalDeaths, 2) : ($totalKills > 0 ? '∞' : '0.00');
                $sessionDuration = formatDuration($row['total_playtime'] ?? 0);

                $distanceWalkedKm = number_format($row['distance_walked'] / 1000, 2);
                $distanceDrivenKm = number_format($row['distance_driven'] / 1000, 2);
                $distanceOccupantKm = number_format($row['distance_as_occupant'] / 1000, 2);
                $kickSessionDuration = formatDuration($row['kick_session_duration']);


                echo "<a href='?' class='back-button'>Back to Players</a>";
                echo "<div class='player-name'>" . htmlspecialchars($row['playerName']) . "</div>"; 


                echo "<div class='player-summary'>";

                echo "<div class='player-summary-card'>";
                echo "<h4>Playtime</h4>";
                echo "<p>" . $sessionDuration . "</p>";
                echo "</div>";

                echo "<div class='player-summary-card'>";
                echo "<h4>Kills</h4>";
                echo "<p>" . $totalKills . "</p>";
                echo "</div>";

                echo "<div class='player-summary-card'>";
                echo "<h4>Deaths</h4>";
                echo "<p>" . $totalDeaths . "</p>";
                echo "</div>";

                echo "<div class='player-summary-card'>";
                echo "<h4>K/D Ratio</h4>";
                echo "<p>" . $playerKD . "</p>";
                echo "</div>";

                echo "</div>";

                echo "<div class='stats-container show'>";

                echo "<div class='stat-card'>";
                echo "<h3>Player Info</h3>";
                echo "<div class='stat-item'><span class='stat-label'>ID:</span><span class='stat-value'>" . ($row['id'] ?? 'N/A') . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>First Seen:</span><span class='stat-value'>" . formatTimeAgo($row['first_seen']) . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>Last Seen:</span><span class='stat-value'>" . formatTimeAgo($row['last_seen']) . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>UID:</span><span class='stat-value-small'>" . ($row['playerUID'] ?? 'N/A') . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>BE GUID:</span><span class='stat-value-small'>" . ($row['beGUID'] ?? 'N/A') . "</span></div>";
                echo "</div>";

                echo "<div class='stat-card'>";
                echo "<h3>Progression</h3>";
                echo "<div class='stat-item'><span class='stat-label'>Distance Walked:</span><span class='stat-value'>" . $distanceWalkedKm . "km</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>INFANTRY XP:</span><span class='stat-value'>" . ($row['sppointss0'] ?? 0) . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>LOGISTICS XP:</span><span class='stat-value'>" . ($row['sppointss1'] ?? 0) . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>MEDICAL XP:</span><span class='stat-value'>" . ($row['sppointss2'] ?? 0) . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>Experience:</span><span class='stat-value'>" . ($row['level_experience'] ?? 0) . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>Level:</span><span class='stat-value'>" . ($row['level'] ?? 0) . "</span></div>";
                echo "</div>";

                echo "<div class='stat-card'>";
                echo "<h3>Combat Stats</h3>";
                echo "<div class='stat-item'><span class='stat-label'>Kills:</span><span class='stat-value'>" . ($row['kills'] ?? 0) . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>AI Kills:</span><span class='stat-value'>" . ($row['ai_kills'] ?? 0) . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>Friendly Kills:</span><span class='stat-value'>" . ($row['friendly_kills'] ?? 0) . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>Friendly AI Kills:</span><span class='stat-value'>" . ($row['friendly_ai_kills'] ?? 0) . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>Deaths:</span><span class='stat-value'>" . ($row['deaths'] ?? 0) . "</span></div>";
                echo "</div>";
                
                echo "<div class='stat-card'>";
                echo "<h3>Miscellaneous</h3>";
                echo "<div class='stat-item'><span class='stat-label'>Warcrimes:</span><span class='stat-value'>" . ($row['warcrimes'] ?? 0) . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>Warcrime Harming Friendlies:</span><span class='stat-value'>" . ($row['warcrime_harming_friendlies'] ?? 0) . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>Crime Acceleration:</span><span class='stat-value'>" . ($row['crime_acceleration'] ?? 0) . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>Kick Session Duration:</span><span class='stat-value'>" . $kickSessionDuration . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>Kick Streak:</span><span class='stat-value'>" . ($row['kick_streak'] ?? 0) . "</span></div>";
                echo "</div>";

                echo "<div class='stat-card'>";
                echo "<h3>Medical Stats</h3>";
                echo "<div class='stat-item'><span class='stat-label'>Self Bandages:</span><span class='stat-value'>" . ($row['bandage_self'] ?? 0) . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>Friendly Bandages:</span><span class='stat-value'>" . ($row['bandage_friendlies'] ?? 0) . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>Self Tourniquets:</span><span class='stat-value'>" . ($row['tourniquet_self'] ?? 0) . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>Friendly Tourniquets:</span><span class='stat-value'>" . ($row['tourniquet_friendlies'] ?? 0) . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>Self Saline:</span><span class='stat-value'>" . ($row['saline_self'] ?? 0) . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>Friendly Saline:</span><span class='stat-value'>" . ($row['saline_friendlies'] ?? 0) . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>Self Morphine:</span><span class='stat-value'>" . ($row['morphine_self'] ?? 0) . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>Friendly Morphine:</span><span class='stat-value'>" . ($row['morphine_friendlies'] ?? 0) . "</span></div>";
                echo "</div>";

                echo "<div class='stat-card'>";
                echo "<h3>Vehicle Stats</h3>";
                echo "<div class='stat-item'><span class='stat-label'>Distance Driven:</span><span class='stat-value'>" . $distanceDrivenKm . "km</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>Distance as Occupant:</span><span class='stat-value'>" . $distanceOccupantKm . "km</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>Points as Driver:</span><span class='stat-value'>" . ($row['points_as_driver_of_players'] ?? 0) . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>Players Died in Vehicle:</span><span class='stat-value'>" . ($row['players_died_in_vehicle'] ?? 0) . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>Roadkills:</span><span class='stat-value'>" . ($row['roadkills'] ?? 0) . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>Friendly Roadkills:</span><span class='stat-value'>" . ($row['friendly_roadkills'] ?? 0) . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>AI Roadkills:</span><span class='stat-value'>" . ($row['ai_roadkills'] ?? 0) . "</span></div>";
                echo "<div class='stat-item'><span class='stat-label'>Friendly AI Roadkills:</span><span class='stat-value'>" . ($row['friendly_ai_roadkills'] ?? 0) . "</span></div>";
                echo "</div>";

                

                echo "</div>";
            } else {
                echo "<a href='?' class='back-button'>Back to Players</a>";
                echo "<div class='player-name'>No player found with name: " . htmlspecialchars($playerName) . "</div>";
            }

            $stmt->close();
            $conn->close();
        }
        ?>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('playerSearch');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.trim().toLowerCase();
                    const playerCards = document.querySelectorAll('.player-card');
                    
                    playerCards.forEach(card => {
                        const playerName = card.getAttribute('data-name');
                        if (playerName.includes(searchTerm)) {
                            card.classList.remove('hidden');
                        } else {
                            card.classList.add('hidden');
                        }
                    });
                });
            }
        });
    </script>

<a href="$discordIcon" class="floating-goat" title="Join Discord">
<img src="$discordUrl" alt="Join Discord">
</a>

</body>
</html>
