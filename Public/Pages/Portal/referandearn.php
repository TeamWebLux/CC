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

    print($uri);
    ?>
    <style>
        #referralLinkInput {
            border-color: #007bff;
            /* Bootstrap primary color */
        }
    </style>

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
            <?php
            include "./App/db/db_connect.php";
            $userId = $_SESSION['user_id'];

            // Prepare the SQL query using mysqli
            $query = "SELECT * FROM user WHERE id = ?";
            $stmt = $conn->prepare($query);

            // Bind the user ID as a parameter to the query
            $stmt->bind_param("i", $userId); // "i" denotes that the parameter is an integer

            // Execute the query
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $refercode = $row['refer_code'];
            $page = $row['pagename'];
            $stmt->close();

            // Generate the referral link
            $referralLink = "https://test.custcount.com/index.php/Register_to_CustCount?r=" . $refercode . "&p=" . $page;


            // Generate the referral link

            ?>
            <br> <br>
            <br>

            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-md-10">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Your Referral Details</h5>
                                <p class="card-text">Share your referral link to invite friends and earn rewards!</p>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($referralLink); ?>" id="referralLinkInput" readonly>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyReferralLink()">Copy</button>
                                        <button class="btn btn-outline-primary" type="button" onclick="shareReferralLink()">Share</button>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <h6>Your Referral Code: <strong><?php echo htmlspecialchars($refercode); ?></strong></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            // Ensure database connection is established
            // $conn = new mysqli('host', 'username', 'password', 'database_name');
            include "./App/db/db_connect.php";

            $userId = $_SESSION['user_id'];
            $directReferralsQuery = "Select * from refferal where refered_by=?";
            $stmt = $conn->prepare($directReferralsQuery);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $directReferralsResult = $stmt->get_result();
            print_r($directReferralsResult);

            $affilates="Select * from refferal where afilated_by=?";
            $af = $conn->prepare($affilates);
            $af->bind_param("i", $userId);
            $af->execute();
            $affilatesresult = $af->get_result();
            print_r($affilatesresult);


            ?>
            
            <div class="container mt-4">
                <h4>Your Referrals and Affiliates</h4>
                <?php foreach ($referrals as $userId => $userDetails): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Referred User: <?= htmlspecialchars($userDetails['username']); ?></h5>
                            <p class="card-text">Affiliates:</p>
                            <?php if (!empty($userDetails['affiliates'])): ?>
                                <ul>
                                    <?php foreach ($userDetails['affiliates'] as $affiliateUsername): ?>
                                        <li><?= htmlspecialchars($affiliateUsername); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p>No affiliates for this user.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            

        </div>






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
    <script>
        // Function to copy referral link to clipboard
        function copyReferralLink() {
            var copyText = document.getElementById("referralLinkInput");
            copyText.select(); // Select the text field
            copyText.setSelectionRange(0, 99999); // For mobile devices
            navigator.clipboard.writeText(copyText.value); // Copy the text inside the text field
            alert("Copied the link: " + copyText.value); // Alert the copied text
        }

        // Function to share the referral link
        function shareReferralLink() {
            var shareUrl = document.getElementById("referralLinkInput").value;
            if (navigator.share) {
                navigator.share({
                        title: 'Join me on CustCount',
                        url: shareUrl
                    }).then(() => {
                        console.log('Thanks for sharing!');
                    })
                    .catch(console.error);
            } else {
                // Fallback for browsers that don't support the Web Share API
                copyReferralLink(); // Automatically copy link if share is not supported
                alert("Link copied to clipboard. Please paste it to share.");
            }
        }
    </script>

</body>

</html>