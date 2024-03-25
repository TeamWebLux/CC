<!doctype html>
<html lang="en" dir="ltr">

<head>
    <?php
    include("./Public/Pages/Common/header.php");
    include "./Public/Pages/Common/auth_user.php";

    // Function to echo the script for toastr
    function echoToastScript($type, $message)
    {
        echo "<script type='text/javascript'>document.addEventListener('DOMContentLoaded', function() { toastr['$type']('$message'); });</script>";
    }

    // Check if there's a toast message set in session, display it, then unset
    //print_r($_SESSION);
    if (isset($_SESSION['toast'])) {
        $toast = $_SESSION['toast'];
        echoToastScript($toast['type'], $toast['message']);
        unset($_SESSION['toast']); // Clear the toast message from session
    }

    if (session_status() !== PHP_SESSION_ACTIVE) session_start();

    // Display error message if available
    if (isset($_SESSION['login_error'])) {
        echo '<p class="error">' . $_SESSION['login_error'] . '</p>';
        unset($_SESSION['login_error']); // Clear the error message
    }
    include './App/db/db_connect.php';

    // Initialize variables
    $referralPercentage = '';
    $affiliatePercentage = '';

    // Fetch the existing bonus rates
    $fetchQuery = "SELECT * FROM refferal_bonus LIMIT 1";
    $fetchResult = $conn->query($fetchQuery);

    if ($fetchResult->num_rows > 0) {
        $existingData = $fetchResult->fetch_assoc();
        $referralPercentage = $existingData['referal'];
        $affiliatePercentage = $existingData['affiliate'];
    }

    // Check if form is submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Get new bonus percentages from the form
        $referralPercentage = $_POST['referralPercentage'];
        $affiliatePercentage = $_POST['affiliatePercentage'];

        // Sanitize input
        $referralPercentage = filter_var($referralPercentage, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $affiliatePercentage = filter_var($affiliatePercentage, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        // Update the referral_bonus table
        if ($existingData) {
            $updateQuery = "UPDATE refferal_bonus SET referal = ?, affiliate = ? WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("ddi", $referralPercentage, $affiliatePercentage, $existingData['id']);
        } else {
            $insertQuery = "INSERT INTO refferal_bonus (referal, affiliate) VALUES (?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("dd", $referralPercentage, $affiliatePercentage);
        }

        if ($stmt->execute()) {
            // If you want to show a success message, you can use session or direct echo
            echo "<p>Bonus percentages updated successfully.</p>";
        } else {
            echo "<p>Error updating record: " . $conn->error . "</p>";
        }

        $stmt->close();
    }

    $conn->close();


    ?>

</head>

<body class="  ">
    <!-- loader Start -->
    <?php
    // include("./Public/Pages/Common/loader.php");

    ?>
    <!-- loader END -->

    <!-- sidebar  -->
    <?php
    include("./Public/Pages/Common/sidebar.php");

    ?>

    <main class="main-content">
        <?php
        include("./Public/Pages/Common/main_content.php");
        ?>



        <div class="content-inner container-fluid pb-0" id="page_layout">
            <div class="container mt-5">
                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h3>Manage Referral & Affiliate Bonus</h3>
                            </div>
                            <div class="card-body">
                                <form action="" method="POST">
                                    <div class="mb-3">
                                        <label for="referralPercentage" class="form-label">Referral Bonus Percentage</label>
                                        <input type="text" class="form-control" id="referralPercentage" name="referralPercentage" required value="<?php echo htmlspecialchars($referralPercentage); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="affiliatePercentage" class="form-label">Affiliate Bonus Percentage</label>
                                        <input type="text" class="form-control" id="affiliatePercentage" name="affiliatePercentage" required value="<?php echo htmlspecialchars($affiliatePercentage); ?>">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">Referal List</h4>
                            <h6 class="box-subtitle"></h6>

                        </div>
                        <?php
                        include './App/db/db_connect.php';
                        $sql = "SELECT * FROM refferal ";

                        $result = $conn->query($sql);

                        // Check if there are results

                        if ($result->num_rows > 0) {
                        ?>
                            <div class="card-body">
                                <div class="custom-table-effect table-responsive border rounded">
                                    <table class="table mb-0" id="example">
                                        <thead>
                                            <tr class="bg-white">
                                                <th scope="col">ID</th>
                                                <th scope="col">UserName</th>
                                                <th scope="col">Referred By Username</th>
                                                <th scope="col">Affiliated By Username</th>
                                                <th scope="col">Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $result->fetch_assoc()) : ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row['id']); ?></td>
                                                    <td><?= htmlspecialchars($row['name']); ?></td>
                                                    <td><?= htmlspecialchars($row['refered_by']); ?></td>
                                                    <td><?= htmlspecialchars($row['afilated_by']); ?></td>
                                                    <td><?= htmlspecialchars($row['created_at']); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                <?php
                            } else {
                                echo "<p>No referral records found.</p>";
                            }
                            // Close connection
                            $conn->close();
                                ?>
                                </div>
                            </div>
                    </div>
                </div>

            </div>
        </div>



        <script>
            $(document).ready(function() {
                $('#example').DataTable({
                    "order": [
                        [4, "desc"]
                    ],
                    dom: 'Bfrtip', // Add the Bfrtip option to enable buttons

                    buttons: [
                        'copy', 'excel', 'pdf'
                    ]
                });
            });
        </script>





        <?
        include("./Public/Pages/Common/footer.php");
        // //print_r($_SESSION);
        ?>

    </main>
    <!-- Wrapper End-->
    <!-- Live Customizer start -->
    <!-- Setting offcanvas start here -->
    <?php
    include("./Public/Pages/Common/theme_custom.php");

    ?>

    <!-- Settings sidebar end here -->

    <?php
    include("./Public/Pages/Common/settings_link.php");

    ?>
    <!-- Live Customizer end -->

    <!-- Library Bundle Script -->
    <?php
    include("./Public/Pages/Common/scripts.php");

    ?>

</body>

</html>