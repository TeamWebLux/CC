<?php

use function PHPSTORM_META\type;

include "../db/db_connect.php";
session_start();
class Creation
{
    private $susername, $srole;
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->susername = $_SESSION['username'];
        $this->srole = $_SESSION['role'];
    }

    public function addUser()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input data
            $name = $this->sanitizeInput($_POST['name']);
            $username = $this->sanitizeInput($_POST['username']);
            $password = ($_POST['password']);
            // $rawpass = $this->sanitizeInput($_POST['password']);
            $role = $this->sanitizeInput($_POST['role']);
            $managerid = isset($_POST['managerid']) ? $this->sanitizeInput($_POST['managerid']) : null;
            $agentid = isset($_POST['agentid']) ? $this->sanitizeInput($_POST['agentid']) : null;
            $branchId = isset($_POST['branchname']) ? $this->sanitizeInput($_POST['branchname']) : null;
            $pageId = isset($_POST['pagename']) ? $this->sanitizeInput($_POST['pagename']) : null;

            // Check if the username is unique
            if ($this->isUsernameUnique($username)) {
                $query = "INSERT INTO user (name, username, password, role, branchname, pagename) VALUES (  ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($this->conn, $query);
                mysqli_stmt_bind_param($stmt, "ssssss", $name, $username, $password, $role, $branchId, $pageId);
                $result = mysqli_stmt_execute($stmt);

                if ($result) {
                    echo "User added successfully.";
                    // $newUserId = mysqli_insert_id($this->conn);
                    // $this->addToTree($newUserId, $role, $managerid, $agentid);
                } else {
                    echo "Error adding user: " . mysqli_error($this->conn);
                }
                mysqli_stmt_close($stmt);
            } else {
                echo "Username is not unique. Please choose another username.";
            }
        }
    }
    public function Platform()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $platformName = $this->conn->real_escape_string($_POST['platformname']);
            $status = isset($_POST['status']) ? 1 : 0;
            $currentBalance = $this->conn->real_escape_string($_POST['currentbalance']);
            $addedBy = $this->susername;

            $sql = "INSERT INTO platform (name, status, current_balance, by_u, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())";

            if ($stmt = $this->conn->prepare($sql)) {
                $stmt->bind_param("sids", $platformName, $status, $currentBalance, $addedBy);

                if ($stmt->execute()) {
                    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Platform added successfully.'];
                    header("location: ../../index.php/Portal_Platform_Management");
                    exit();
                } else {
                    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error adding platform: ' . $stmt->error];
                }
                $stmt->close();
            } else {
                $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error preparing statement: ' . $this->conn->error];
            }
        }
    }
    public function CashApp()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $name = $this->conn->real_escape_string($_POST['cashAppname']);
            $cashtag = $this->conn->real_escape_string($_POST['cashApptag']);
            $currentBalance = $this->conn->real_escape_string($_POST['currentbalance']);
            $email = $this->conn->real_escape_string($_POST['email']);
            $remark = $this->conn->real_escape_string($_POST['remark']);


            $status = isset($_POST['active']) ? 1 : 0;

            $sql = "INSERT INTO cashapp (name, cashtag,start,email, current_balance,remark, status, created_at, updated_at) VALUES (?, ?,NOW(), ?,?,?, ?, NOW(), NOW())";

            if ($stmt = $this->conn->prepare($sql)) {
                $stmt->bind_param("sssdsi", $name, $cashtag, $email, $currentBalance, $remark, $status);

                if ($stmt->execute()) {
                    $_SESSION['toast'] = ['type' => 'success', 'message' => 'CashApp details added successfully.'];
                    header("location: ../../index.php/Portal_Cashup_Management");
                    exit();
                } else {
                    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error adding CashApp details: ' . $stmt->error];
                }
                $stmt->close();
            } else {
                $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error preparing statement: ' . $this->conn->error];
            }
        }
    }

    public function CashOut()
    {
        if (isset($_POST)) {
            $username = $_POST['username'];
            $cashoutamount = $_POST['reedemamount'];
            $fbid = $_POST['pagename'];
            $accessamount = $_POST['excessamount'];
            $platformName = ($_POST['platformname'] !== 'other') ? $_POST['platformname'] : $_POST['platformname_other'];
            $cashupName = ($_POST['cashAppname'] !== 'other') ? $_POST['cashAppname'] : $_POST['cashAppname_other'];
            $remark=$_POST['remark'];
            $tip = $_POST['tip'];
            $type="Credit";
            $by_role = $this->srole;
            $by_username = $this->susername;

            $sql = "Insert into transaction (username,redeem,page_no,excess,cashname,platform,tip,type,remark,by_u) VALUES (?,?,?,?,?,?,?,?,?,?)";
           if( $stmt = mysqli_prepare($this->conn, $sql)){
            mysqli_stmt_bind_param($stmt, "sisissssss", $username, $cashoutamount, $fbid, $accessamount, $cashupName, $platformName, $tip, $type,$remark, $by_username);
            if ($stmt->execute()) {
                $_SESSION['toast'] = ['type' => 'success', 'message' => 'Recharge Added Sucessfully '];

                echo "Transaction added successfully. Redirecting...<br>";
                header("Location: ../../index.php/Portal_User_Management");
                exit();
            } else {
                echo "Error adding transaction details: " . $stmt->error . "<br>";
                $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error adding transaction details: ' . $stmt->error];
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $this->conn->error . "<br>";
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error preparing statement: ' . $this->conn->error];
        }
    }
    }

    //pending
    public function Deposit()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Validate input fields
            if (empty($_POST['username']) || empty($_POST['platformname']) || empty($_POST['cashAppname']) || empty($_POST['bonusamount'])) {
                echo "Validation failed. Redirecting...<br>";
                echo "Current URL: " . $_SERVER['REQUEST_URI'] . "<br>";

                $_SESSION['toast'] = ['type' => 'error', 'message' => 'Please fill in all required fields.'];
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            }

            $username = $this->conn->real_escape_string($_POST['username']);
            $recharge = $this->conn->real_escape_string($_POST['depositamount']);
            $pageId = 1;
            $pagename = $_POST['pagename'] === 'other' ? $_POST['pagename_other'] : $_POST['pagename'];
            $platform = $_POST['platformname'] === 'other' ? $_POST['platformname_other'] : $_POST['platformname'];
            $cashName = $_POST['cashAppname'] === 'other' ? $_POST['cashAppname_other'] : $_POST['cashAppname'];
            $bonus = $this->conn->real_escape_string($_POST['bonusamount']);
            $remark = $this->conn->real_escape_string($_POST['remark']);
            $byId = 1; // Assuming a default value for byId
            $byUsername = $this->susername;

            $type = "Debit"; // Adjust the type as needed

            $sql = "INSERT INTO transaction (username, recharge, page_id,page_no, platform, cashname, bonus, remark, by_id, by_u, type, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, NOW(), NOW())";

            if ($stmt = $this->conn->prepare($sql)) {
                $stmt->bind_param("sssssssssss", $username, $recharge, $pageId, $pagename, $platform, $cashName, $bonus, $remark, $byId, $byUsername, $type);

                if ($stmt->execute()) {
                    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Recharge Added Sucessfully '];

                    echo "Transaction added successfully. Redirecting...<br>";
                    header("Location: ../../index.php/Portal_User_Management");
                    exit();
                } else {
                    echo "Error adding transaction details: " . $stmt->error . "<br>";
                    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error adding transaction details: ' . $stmt->error];
                }
                $stmt->close();
            } else {
                echo "Error preparing statement: " . $this->conn->error . "<br>";
                $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error preparing statement: ' . $this->conn->error];
            }
        }
    }
    public function Redeem()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Retrieve form data
            $name = $_POST['username'] ?? '';
            $platformName = $_POST['platformname'] === 'other' ? $_POST['platformname_other'] : $_POST['platformname'];
            $cashupName = $_POST['cashupname'] === 'other' ? $_POST['cashupname_other'] : $_POST['cashupname'];
            $cashtag = $_POST['cashtag'] ?? '';
            $amount = $_POST['amount'] ?? 0;
            $remark = $_POST['remark'] ?? '';
            $by_role = $this->srole;
            $by_username = $this->susername;


            // Prepare an SQL statement to insert the form data into the database
            $sql = "INSERT INTO withdrawl (username, platformname, cashupname, cashtag, amount, remark,by_username) VALUES (?, ?, ?, ?, ?, ?,?)";

            try {
                // Prepare the SQL statement
                $stmt = mysqli_prepare($this->conn, $sql);
                $stmt->bind_param("ssssiss", $name, $platformName, $cashupName, $cashtag, $amount, $remark, $by_username);

                // Execute the statement
                $stmt->execute();

                // Close statement
                $stmt->close();

                // Redirect or inform the user of success
                echo "Record added successfully.";
                // Optionally, redirect to another page
                // header('Location: success_page.php');
            } catch (Exception $e) {
                // Close statement if it's set
                if (isset($stmt)) {
                    $stmt->close();
                }
                die("Error: " . $e->getMessage());
            }
        } else {
            // Handle incorrect access or display a specific error message
            echo "Invalid request.";
        }
    }



    public function CashupAction()
    {
        if (isset($_POST)) {
            $cashupname = $_POST['cashupname'];
            $cashuptag = $_POST['cashuptag'];
            $active = $_POST['active'] ? 1 : 0;
            $currentbalance = $_POST['currentbalance'];
            $sql = "Insert into CashupAction (cashupname,cashuptag,active,currentbalance) VALUES (?,?,?,?)";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssii", $cashupname, $cashuptag, $active, $currentbalance);
            $result = mysqli_stmt_execute($stmt);
            if ($result) {
                echo "CashupAction added successfully.";
            }
        }
    }
    public function AddBranch()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $name = $this->conn->real_escape_string($_POST['name']);
            $status = isset($_POST['status']) ? 1 : 0; // Assuming 'status' is a checkbox

            $sql = "INSERT INTO branch (name, status, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";

            if ($stmt = $this->conn->prepare($sql)) {
                $stmt->bind_param("si", $name, $status);

                if ($stmt->execute()) {
                    // Success: Redirect or display a success message
                    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Branch added successfully.'];
                    header("location: ../../index.php/Portal_Branch_Management");
                    exit();
                } else {
                    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error adding branch: ' . $stmt->error];
                }
                $stmt->close();
            } else {
                $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error preparing statement: ' . $this->conn->error];
            }
        }
    }



    // Redirect back to the form page in case of an error or invalid method
    // header("location: " . $postUrl);
    // exit();

    public function EditBranch()
    {
        $name = $this->conn->real_escape_string($_POST['name']);
        $status = isset($_POST['status']) ? 1 : 0; // Assuming 'status' is a checkbox

        // Update the database for editing a branch
        $bid = $_POST['bid']; // Assuming you have the branch ID

        $sql = "UPDATE branch SET name=?, status=?, updated_at=NOW() WHERE bid=?";

        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param("sii", $name, $status, $bid);

            if ($stmt->execute()) {
                // Success: Redirect or display a success message
                $_SESSION['toast'] = ['type' => 'success', 'message' => 'Branch updated successfully.'];
                header("location: ../../index.php/Portal_Branch_Management");
                exit();
            } else {
                $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error updating branch: ' . $stmt->error];
            }
            $stmt->close();
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error preparing statement: ' . $this->conn->error];
        }
    }
    public function AddPage()
    {

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // $pid = $this->conn->real_escape_string($_POST['pid']);
            $bid = $this->conn->real_escape_string($_POST['bid']);
            $name = $this->conn->real_escape_string($_POST['name']);
            $status = isset($_POST['status']) ? 1 : 0; // Assuming 'status' is a checkbox
            $by = $this->susername;

            $sql = "INSERT INTO page (bid, name, status, by_u, created_at, updated_at) VALUES (?, ?, ?, ?,  NOW(), NOW())";

            if ($stmt = $this->conn->prepare($sql)) {
                $stmt->bind_param("ssis", $bid, $name, $status, $by);

                if ($stmt->execute()) {
                    // Success: Redirect or display a success message
                    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Page added successfully.'];
                    header("location: ../../index.php/Portal_Page_Management");
                    exit();
                } else {
                    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error adding page: ' . $stmt->error];
                }
                $stmt->close();
            } else {
                $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error preparing statement: ' . $this->conn->error];
            }
        }
    }



    private function addToTree($newUserId, $role, $managerid, $agentid)
    {
        $id = $_SESSION['userid'];
        if ($newUserId != null && $role != null && $managerid != null && $agentid != null && $id != null) {
            $query = "INSERT INTO tree (agentid, adminid, managerid,userid	) VALUES (?, ?, ?,?)";
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, "iiii", $agentid, $id, $managerid, $newUserId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        $parentId = 0; // Default to 0 or a suitable default parent ID
        $currentUserRole = $_SESSION['role'] ?? 'Admin'; // Defaulting to Admin if session role is not set
        $currentUserId = $_SESSION['user_id'] ?? 1; // Defaulting to 1 or a suitable admin ID if session user_id is not set

        // Determine the parent ID based on the current user's role and the new user's role
        if ($role == 'User') {
            if ($currentUserRole == 'Admin') {
                $parentId = $currentUserId; // The admin themselves are the parent
            } elseif ($currentUserRole == 'Manager' || $currentUserRole == 'Agent') {
                $parentId = $this->findParentId($currentUserId, $currentUserRole);
            }
            // For users, the parent could be an Admin, Manager, or Agent
        } elseif ($role == 'Manager' || $role == 'Agent') {
            // For managers and agents, the parent is assumed to be an Admin or higher level manager
            $parentId = $this->findParentId($currentUserId, $currentUserRole);
        }

        // Insert into the tree table
        // $query = "INSERT INTO tree (user_id, parent_id, role) VALUES (?, ?, ?)";
        // $stmt = mysqli_prepare($this->conn, $query);
        // mysqli_stmt_bind_param($stmt, "iis", $newUserId, $parentId, $role);
        // mysqli_stmt_execute($stmt);
        // mysqli_stmt_close($stmt);
    }

    private function findParentId($userId, $userRole)
    {
        // Logic to find the parent ID based on current user role and ID
        // This could involve querying the tree table to find the appropriate parent
        // For simplicity, return a default or queried parent ID
        return $userId; // Placeholder return
    }

    private function isUsernameUnique($username)
    {
        $query = "SELECT COUNT(*) FROM user WHERE username = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        return $count == 0; // If count is 0, the username is unique
    }

    private function sanitizeInput($input)
    {
        return htmlspecialchars(strip_tags(trim($input)));
    }
}

$creation = new Creation($conn);
if (isset($_GET['action']) && $_GET['action'] == "UserAdd") {
    $creation->addUser();
} else if (isset($_GET['action']) && $_GET['action'] == "platform") {
    $creation->Platform();
} else if (isset($_GET['action']) && $_GET['action'] == "CashApp") {
    $creation->CashApp();
} else if (isset($_GET['action']) && $_GET['action'] == "CashOut") {
    $creation->CashOut();
} else if (isset($_GET['action']) && $_GET['action'] == "Deposit") {
    $creation->Deposit();
} else if (isset($_GET['action']) && $_GET['action'] == "CashupAction") {
    $creation->CashupAction();
} else if (isset($_GET['action']) && $_GET['action'] == "Withdrawl") {
    $creation->Redeem();
} else if (isset($_GET['action']) && $_GET['action'] == "AddBranch") {
    $creation->AddBranch();
} else if (isset($_GET['action']) && $_GET['action'] == "AddPage") {
    $creation->AddPage();
}



// Close the database connection
mysqli_close($conn);
