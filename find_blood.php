<?php
require_once 'db_connect.php';

$hospitals_data = [];

// Fetch all hospitals and their blood unit quantities in one go
$sql = "
    SELECT 
        h.hospital_id, 
        h.name, 
        h.address, 
        h.phone,
        bu.blood_group,
        bu.quantity
    FROM 
        hospitals h
    LEFT JOIN 
        blood_units bu ON h.hospital_id = bu.hospital_id
    ORDER BY
        h.name, bu.blood_group
";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $hospitals_data[$row['hospital_id']]['details']['name'] = $row['name'];
        $hospitals_data[$row['hospital_id']]['details']['address'] = $row['address'];
        $hospitals_data[$row['hospital_id']]['details']['phone'] = $row['phone'];
        $hospitals_data[$row['hospital_id']]['units'][$row['blood_group']] = $row['quantity'];
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale-1.0">
    <title>Find Blood - LifeFlow Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">

    <!-- Navbar -->
    <header class="bg-white shadow-md sticky top-0 z-10">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.html" class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-heartbeat text-3xl mr-2 text-red-500"></i>
                <span>LifeFlow Connect</span>
            </a>
            <div>
                <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <a href="dashboard_redirect.php" class="bg-red-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-red-700 transition duration-300">
                        <i class="fas fa-tachometer-alt mr-2"></i>Go to Dashboard
                    </a>
                <?php else: ?>
                    <a href="login.php" class="text-gray-800 font-semibold text-lg hover:text-red-600 mr-6">Login</a>
                    <a href="register.php" class="bg-red-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-red-700 transition duration-300">Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-800">Blood Availability</h1>
            <p class="text-lg text-gray-600 mt-2">Find available blood units at our partner hospitals in real-time.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if (empty($hospitals_data)): ?>
                <p class="text-center text-gray-500 col-span-full">No hospital data available at the moment.</p>
            <?php else: ?>
                <?php foreach ($hospitals_data as $hospital_id => $data): ?>
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:scale-105 transition-transform duration-300">
                        <div class="p-6">
                            <h2 class="text-2xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($data['details']['name']); ?></h2>
                            <p class="text-gray-600 mb-1"><i class="fas fa-map-marker-alt w-5 text-red-500 mr-2"></i><?php echo htmlspecialchars($data['details']['address']); ?></p>
                            <p class="text-gray-600 mb-4"><i class="fas fa-phone w-5 text-red-500 mr-2"></i><?php echo htmlspecialchars($data['details']['phone']); ?></p>
                            
                            <div class="border-t border-gray-200 pt-4">
                                <h3 class="font-semibold text-gray-700 mb-3 text-center">Available Units</h3>
                                <div class="grid grid-cols-4 gap-2 text-center">
                                    <?php 
                                    $blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                                    foreach ($blood_groups as $group): 
                                        $quantity = $data['units'][$group] ?? 0;
                                        $color = $quantity > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                    ?>
                                    <div class="p-2 rounded-md <?php echo $color; ?>">
                                        <div class="font-bold text-lg"><?php echo $group; ?></div>
                                        <div class="text-sm"><?php echo $quantity; ?></div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
