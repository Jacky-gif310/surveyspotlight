<?php
// results.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// DB connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "survey_db";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch platforms
$platforms = [];
$sqlPlatforms = "SELECT DISTINCT shopping_platform FROM customer_feedback ORDER BY shopping_platform ASC";
$resultPlatforms = $conn->query($sqlPlatforms);
if ($resultPlatforms && $resultPlatforms->num_rows > 0) {
    while ($row = $resultPlatforms->fetch_assoc()) {
        $platforms[] = $row['shopping_platform'];
    }
}

// Chart data function
function getChartData($conn, $platform, $column, $order = "") {
    $sql = "SELECT $column, COUNT(*) as count FROM customer_feedback 
            WHERE shopping_platform = '$platform' 
            GROUP BY $column";
    if($order !== "") {
        $sql .= " ORDER BY $order";
    }
    $result = $conn->query($sql);
    $labels = [];
    $counts = [];
    if ($result && $result->num_rows > 0) {
        while($r = $result->fetch_assoc()){
            $labels[] = $r[$column];
            $counts[] = $r['count'];
        }
    }
    return ['labels' => json_encode($labels), 'counts' => json_encode($counts), 'raw_labels' => $labels, 'raw_counts' => $counts];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Survey Results Grouped by Platform | Survey Analysis Tool</title>
  <link rel="stylesheet" href="styles.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { font-family: Arial; background: #fdf5ff; margin: 0; padding: 0; }
    header { background: #8e44ad; color: #fff; padding: 1em; }
    nav ul { list-style: none; display: flex; justify-content: center; padding: 0; }
    nav li { margin: 0 1em; }
    nav a { color: #fff; text-decoration: none; font-weight: bold; }
    .results { max-width: 960px; margin: 2em auto; background: #fff; padding: 2em; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .platform-container { margin-bottom: 3em; padding: 1.5em; border: 2px solid #e0c3fc; border-radius: 12px; background: #faf1ff; }
    .platform-header { display: flex; align-items: center; margin-bottom: 1em; }
    .platform-logo { width: 50px; margin-right: 1em; }
    .chart-grid { display: flex; flex-wrap: wrap; gap: 1em; justify-content: center; }
    .chart-item { flex: 1 1 280px; max-width: 280px; background: #fff; padding: 1em; border: 1px solid #f2d1f7; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.06); }
    .chart-item canvas { width: 100% !important; height: 200px !important; }
    .insights-box { margin-top: 1em; padding: 1em; background: #e8fdfd; border-left: 5px solid #00cec9; border-radius: 8px; }
    .insight-line { margin-bottom: 0.5em; }
    footer { text-align: center; padding: 1em; background-color: #8e44ad; color: #fff; margin-top: 2em; }
  </style>
</head>
<body>
<header>
  <nav>
    <h1>Survey Analysis Tool</h1>
    <ul>
      <li><a href="Form.php">Home</a></li>
      <li><a href="survey.php">Survey</a></li>
      <li><a href="results.php">Results</a></li>
      <li><a href="about.php">About</a></li>
      <li><a href="contact.php">Contact</a></li>
    </ul>
  </nav>
</header>
<section class="results">
  <h2>Survey Results Grouped by Platform</h2>
  <div style="margin-bottom: 20px;">
  <form action="export_csv.php" method="post" style="display: inline;">
    <button type="submit">📥 Export as CSV</button>
  </form>
  
</div>

<?php
foreach ($platforms as $index => $platform) {
    $pLower = strtolower($platform);
    $logo = "";

    if ($pLower == "jumia") $logo = "jumia.png";
    elseif ($pLower == "amazon") $logo = "amazon.png";
    elseif ($pLower == "kilimall") $logo = "kilimal .png";
    elseif ($pLower == "carrefour") $logo = "carrefour.png";

    if (!$logo) continue;

    $experience = getChartData($conn, $platform, "experience_rating", "experience_rating");
    $product    = getChartData($conn, $platform, "product_satisfaction");
    $delivery   = getChartData($conn, $platform, "delivery_rating", "delivery_rating");
    $recommend  = getChartData($conn, $platform, "recommendation");
    $frequency  = getChartData($conn, $platform, "shopping_frequency");
    $pricing    = getChartData($conn, $platform, "pricing_fairness");
    $support    = getChartData($conn, $platform, "support_rating", "support_rating");
    $speed      = getChartData($conn, $platform, "delivery_speed");

    echo '<div class="platform-container">';
    echo '<div class="platform-header">';
    echo '<img src="' . $logo . '" alt="' . $platform . ' Logo" class="platform-logo">';
    echo '<h3>' . $platform . '</h3>';
    echo '</div>';
    echo '<div class="chart-grid">';

    $chartTypes = [
        'experience' => ['Shopping Experience Rating', 'bar', $experience],
        'product' => ['Product Satisfaction', 'pie', $product],
        'delivery' => ['Delivery Experience Rating', 'bar', $delivery],
        'recommend' => ['Would Recommend?', 'doughnut', $recommend],
        'frequency' => ['Shopping Frequency', 'bar', $frequency],
        'pricing' => ['Pricing Fairness', 'doughnut', $pricing],
        'support' => ['Customer Support Rating', 'bar', $support],
        'speed' => ['Delivery Speed', 'pie', $speed],
    ];

    foreach ($chartTypes as $key => [$label, $type, $data]) {
        echo '<div class="chart-item">';
        echo '<canvas id="' . $key . 'Chart_' . $index . '"></canvas>';
        echo '<p>' . $label . '</p>';
        echo '</div>';
    }

    echo '</div>'; // chart-grid

    echo '<div class="insights-box">';
    echo '<h4>📊 Insights</h4>';

    $totalExp = array_sum($experience['raw_counts']);
    $weightedExp = 0;
    foreach ($experience['raw_labels'] as $i => $label) {
        $weightedExp += intval($label) * $experience['raw_counts'][$i];
    }
    $avgExp = $totalExp > 0 ? $weightedExp / $totalExp : 0;

    if ($avgExp >= 4.5) {
        echo '<div class="insight-line"> Customers love shopping here! High ratings across the board indicate a strong customer experience, but don’t rest on your laurels just yet. Even the best platforms can improve. Continue to engage with customers to maintain satisfaction, keep up with market trends, and consider enhancing loyalty programs or exclusive offers. By staying proactive, you can ensure customers remain loyal and encourage them to spread the word. Consistently exceeding expectations is the key to long-term success!</div>';
    } elseif ($avgExp >= 3) {
        echo '<div class="insight-line"> Customers find the experience decent, but there’s clear potential for growth. Business owners can boost satisfaction by streamlining the shopping process, improving user interfaces, and offering personalized support. Small improvements can lead to big loyalty gains!.</div>';
    } else {
        echo '<div class="insight-line">Improve the user experience by gathering feedback directly from your customers and making improvements based on their needs. Offering personalized experiences or faster support could significantly boost satisfaction and loyalty!.</div>';
    }

    $yesCount = 0;
    foreach ($recommend['raw_labels'] as $i => $label) {
        if (strtolower($label) === 'yes') $yesCount += $recommend['raw_counts'][$i];
    }
    $totalRec = array_sum($recommend['raw_counts']);
    $yesPercent = $totalRec > 0 ? ($yesCount / $totalRec) * 100 : 0;

    if ($yesPercent > 80) {
        echo '<div class="insight-line"> Most users would recommend this platform! To build on this positive momentum, continue maintaining high-quality service and engagement. Encourage satisfied customers to leave reviews and share their experiences to help boost your reputation even further!</div>';
    } elseif ($yesPercent > 50) {
        echo '<div class="insight-line"> Mixed feedback on recommendations. It seems some customers are unsure whether they’d recommend this platform, which may suggest room for improvement in key areas like product variety, user experience, or pricing. To turn the tide, businesses should gather more detailed feedback to pinpoint pain points. Enhancing user experience, offering better deals, or addressing specific product concerns could help win over more customers and increase positive word-of-mouth.</div>';
    } else {
            echo '<div class="insight-line">Users are hesitant to recommend this platform.This could indicate concerns about product quality, customer service, or overall experience. It’s important to dive deeper into the feedback and identify specific areas for improvement. Enhancing product offerings, streamlining the purchasing process, or improving post-purchase support can turn hesitant users into loyal advocates. Consider loyalty programs or follow-up surveys to address their concerns and turn the situation around!.</div>';
    }

    echo '</div>'; // insights-box
    echo '</div>'; // platform-container

    echo "<script>";
    foreach ($chartTypes as $key => [$label, $type, $data]) {
        echo "
        new Chart(document.getElementById('{$key}Chart_{$index}').getContext('2d'), {
            type: '{$type}',
            data: {
                labels: {$data['labels']},
                datasets: [{
                    label: '{$label}',
                    data: {$data['counts']},
                    backgroundColor: [
                        '#e84393', '#00cec9', '#6c5ce7', '#fab1a0', '#55efc4', '#fd79a8'
                    ]
                }]
            },
            options: {
                responsive: true,
                scales: " . ($type === 'bar' ? "{ y: { beginAtZero: true } }" : "{}") . "
            }
        });";
    }
    echo "</script>";
}
?>
</section>
<footer>
  <p>&copy; 2025 Survey Analysis Tool. All rights reserved.</p>
</footer>
</body>
</html>
