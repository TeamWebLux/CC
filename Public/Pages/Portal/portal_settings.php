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

    // print($uri);
    include './App/db/db_connect.php';
    // Check if the form has been submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['time_zone'])) {
        $userId = $_SESSION['user_id'];
        $newTimeZone = $_POST['time_zone'];

        $sql = "UPDATE user SET timezone = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$newTimeZone, $userId]);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Time zone updated successfully'];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Handle password change
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'], $_POST['confirm_password'])) {
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        if ($newPassword === $confirmPassword) {
            $userId = $_SESSION['user_id'];

            $sql = "UPDATE user SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$newPassword, $userId]);

            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Password changed successfully'];
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Passwords do not match'];
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Handle profile picture upload
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
        $userId = $_SESSION['user_id'];
        // $profilePicture = $_FILES['profile_picture'];
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $profilePicture = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $fileName = time() . '-' . basename($_FILES['profile_picture']['name']);
            $targetFilePath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
                $profilePicture = $fileName;
            } else {
                echo "Error uploading file.";
                exit;
            }
        }


        $sql = "UPDATE user SET p_p = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$profilePicture, $userId]);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Profile picture updated successfully'];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    ?>
    <style>
        .custom-toast {
            color: black !important;
            /* Ensure text is visible */
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

            <div class="row">


                <div class="col-sm-12">
                    <h3 class="mt-4 mb-3">Your Settings</h3>
                    <?php
                    if (isset($_SESSION['timezone_updated'])) {
                        echo '<p>Time zone updated successfully.</p>';
                        unset($_SESSION['timezone_updated']);
                    }
                    ?>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
                        <h3>Update Time Zone</h3>
                        <label for="time_zone">Select your time zone:</label>
                        <select name="time_zone" id="time_zone">
                            <?php
                            $timezones = DateTimeZone::listIdentifiers();
                            foreach ($timezones as $timezone) {
                                $selected = ($_SESSION['timezone'] ?? 'UTC') === $timezone ? ' selected' : '';
                                echo "<option value=\"$timezone\"$selected>$timezone</option>";
                            }
                            ?>
                        </select>
                        <button type="submit">Update Time Zone</button>
                    </form>

                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                        <h3>Change Password</h3>
                        <input type="password" name="new_password" placeholder="New Password" required>
                        <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
                        <button type="submit">Change Password</button>
                    </form>

                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
                        <h3>Update Profile Picture</h3>
                        <input type="file" name="profile_picture" required>
                        <button type="submit">Upload Picture</button>
                    </form>

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
    <?php if (isset($_SESSION['toast'])) : ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                console.log('<?php echo $_SESSION['toast']['message']; ?>'); // Debug message content
                toastr['<?php echo $_SESSION['toast']['type']; ?>']('<?php echo addslashes($_SESSION['toast']['message']); ?>');
            });
        </script>
        <?php unset($_SESSION['toast']); ?>
    <?php endif; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            toastr.success('This is a test message');
        });
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut",
            "tapToDismiss": false
        };

        toastr['success']('This is a success message!'); // Example for a success message
    </script>

</body>

</html>