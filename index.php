<!DOCTYPE html>
<html>
<head>
    <title>What if...Btc</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php
        function sanitize_and_validate_input($data, $type = "string") {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

            // Additional validation based on data type (string or number)
            if ($type === "number") {
                $data = floatval($data);
                if ($data <= 0) {
                    die("The initial amount must be greater than zero.");
                }
            }

            return $data;
        }

        $startAmount = sanitize_and_validate_input($_POST["startAmount"] ?? "100", "number");
        $startDate = sanitize_and_validate_input($_POST["startDate"] ?? "2012-01-01");
        $endDate = sanitize_and_validate_input($_POST["endDate"] ?? date("Y-m-d"));

        $file = 'bitcoin_prices_daily.csv';
        if (!file_exists($file) || !is_readable($file)) {
            die("Error: The CSV file is not accessible.");
        }

        $data = [];
        if (($handle = fopen($file, 'r')) !== false) {
            $headers = fgetcsv($handle);
            while (($row = fgetcsv($handle)) !== false) {
                $row = array_map('trim', $row);
                $data[] = [
                    'timestamp' => (int) $row[0],
                    'open' => (float) $row[1],
                    'close' => (float) $row[2],
                ];
            }
            fclose($handle);
        }

        $filteredData = array_filter($data, function ($row) use ($startDate, $endDate) {
            return $row['timestamp'] >= strtotime($startDate) && $row['timestamp'] <= strtotime($endDate);
        });

        if (empty($filteredData)) {
            echo "No historical data found in the specified period.\n";
            return;
        }

        $monthlyData = [];
        foreach ($filteredData as $row) {
            $month = date('Y-m', $row['timestamp']);
            $monthlyData[$month][] = $row;
        }
    ?>

    <div class="flex-container">
        <div class="box">
            <h2>Bitcoin Accumulation Plan</h2>
            <div class="aligned-left">
                <img src="image1.png" alt="whatifbtc" style="max-width: 50%; height: 50px;">
            </div>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <label>Monthly Investment (Bitcoin):</label>
                <input type="number" step="10" name="startAmount" id="startAmount" required value="<?php echo isset($_POST["startAmount"]) ? $_POST["startAmount"] : "100"; ?>">
                <label>Start Date:</label>
                <input type="date" name="startDate" id="startDate" required value="<?php echo isset($_POST["startDate"]) ? $_POST["startDate"] : "2012-01-01"; ?>">
                <label>End Date:</label>
                <input type="date" name="endDate" id="endDate" required value="<?php echo isset($_POST["endDate"]) ? $_POST["endDate"] : date("Y-m-d"); ?>">
                <input type="submit" value="Calculate Profit" style="width: 100%; padding: 15px; font-size: 16px; border: none; border-radius: 5px; background-color: #f7931a; color: #ffffff; cursor: pointer;">
            </form>
        </div>

        <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                echo ' <div class="box"> <h2>Investment Result</h2>';
                $totalInvestment = $startAmount;
                $i = 0;
                foreach ($monthlyData as $month => $data) {
                    $startingPrice = $data[0]['open'];
                    $endingPrice = end($data)['close'];
                    $profit = (($endingPrice - $startingPrice) / $startingPrice) * $totalInvestment;
                    $totalInvestment += $profit + $startAmount;
                    $i += 1;
                    
                    /*echo number_format($totalInvestment, 2);
                    echo "___" . $i . "<br>";*/
                }
                $totalInvestedCapital = $startAmount * ($i);
                $profit = $totalInvestment - $totalInvestedCapital;
        ?>

        <label>Profit/Loss: <?php
            if ($profit < 0) {
                echo '<span class="red">' . htmlspecialchars(number_format($profit, 2)) . ' USD</span><br><br>';
            } else {
                echo '<span class="green">' . htmlspecialchars(number_format($profit, 2)) . ' USD</span><br>';
            }
        ?></label>
        <label>Total Investment Amount: <?php echo number_format($totalInvestedCapital, 2) ?> USD</label>
        <label>Total Exit Amount: <?php
            if ($totalInvestment >= $totalInvestedCapital) {
                echo '<span class="green"> ' . number_format($totalInvestment, 2) . ' USD</span><br>';
            } else {
                echo '<span class="red"> ' . number_format($totalInvestment, 2) . ' USD</span><br>';
            }
        ?></label>
        <?php } ?>
        </label>
        </div>

        <div class="results-container">
        <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $totalInvestment = $startAmount;
                $i = 1;
                foreach ($monthlyData as $month => $data) {
                    $startingPrice = $data[0]['open'];
                    $endingPrice = end($data)['close'];
                    $investedCapital = $startAmount * $i;
                    $profit = (($endingPrice - $startingPrice) / $startingPrice) * $totalInvestment;
        ?>
                    <div class="box">
                        <span class="yellow">Month: <?php echo $month; ?></span><br><br>
                        <?php echo "Total Investment Amount: " . number_format($investedCapital, 2) . " USD<br>"; ?>
                        <?php
                            // Add appropriate CSS class based on the relationship between Total Investment and Invested Capital
                            if ($totalInvestment >= $investedCapital) {
                                echo '<span class="green">Cumulated Capital: ' . number_format($totalInvestment, 2) . ' USD</span><br>';
                            } else {
                                echo '<span class="red">Cumulated Capital: ' . number_format($totalInvestment, 2) . ' USD</span><br>';
                            }
                        ?>
                        <?php echo "Current Month Starting Price: " . number_format($startingPrice, 2) . " USD<br>"; ?>
                        <?php echo "Current Month Ending Price: " . number_format($endingPrice, 2) . " USD<br>"; ?>
                        <!-- Add appropriate CSS class based on the profit value -->
                        <?php
                            if ($profit < 0) {
                                echo '<span class="red">Current Month Profit/Loss: ' . number_format($profit, 2) . ' USD</span><br><br>';
                            } else {
                                echo '<span class="green">Current Month Profit/Loss: ' . number_format($profit, 2) . ' USD</span><br><br>';
                            }
                        ?>
                    </div>

        <?php
                    $totalInvestment += $profit + $startAmount;
                    $i += 1;
                }
            }
        ?>
        </div>
    </div>
</body>
</html>

