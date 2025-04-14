<?php
// export_csv.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "survey_db";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=survey_results.csv');
$output = fopen('php://output', 'w');

// Column headers
fputcsv($output, ['Platform', 'Experience Rating', 'Product Satisfaction', 'Delivery Rating', 'Recommendation', 'Shopping Frequency', 'Pricing Fairness', 'Support Rating', 'Delivery Speed']);

// Fetch data
$sql = "SELECT * FROM customer_feedback ORDER BY shopping_platform";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['shopping_platform'],
            $row['experience_rating'],
            $row['product_satisfaction'],
            $row['delivery_rating'],
            $row['recommendation'],
            $row['shopping_frequency'],
            $row['pricing_fairness'],
            $row['support_rating'],
            $row['delivery_speed']
        ]);
    }
}
fclose($output);
$conn->close();
exit();
