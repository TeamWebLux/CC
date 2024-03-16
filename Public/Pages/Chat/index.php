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
	print_r($_SESSION);
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
	if (isset($_SESSION['username'])) {
		# database connection file
		include 'app/db.conn.php';
	// include './Public/Pages/Chat/./Public/Pages/Chat/app/';
		include 'app/helpers/user.php';
		include 'app/helpers/conversations.php';
		include 'app/helpers/timeAgo.php';
		include 'app/helpers/last_chat.php';
	
		# Getting User data data
		$user = getUser($_SESSION['username'], $conn);
	
		# Getting User conversations
		$conversations = getConversation($user['id'], $conn);
	}	
	print($uri);
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
			<div class="p-2 w-400
                rounded shadow">
				<div>
					<div class="d-flex
    		            mb-3 p-3 bg-light
			            justify-content-between
			            align-items-center">
						<div class="d-flex
    			            align-items-center">
							<img src="uploads/<?= $user['p_p'] ?>" class="w-25 rounded-circle">
							<h3 class="fs-xs m-2"><?= $user['name'] ?></h3>
						</div>
						<a href="logout.php" class="btn btn-dark">Logout</a>
					</div>

					<div class="input-group mb-3">
						<input type="text" placeholder="Search..." id="searchText" class="form-control">
						<button class="btn btn-primary" id="serachBtn">
							<i class="fa fa-search"></i>
						</button>
					</div>
					<ul id="chatList" class="list-group mvh-50 overflow-auto">
						<?php if (!empty($conversations)) { ?>
							<?php

							foreach ($conversations as $conversation) { ?>
								<li class="list-group-item">
									<a href="chat.php?user=<?= $conversation['username'] ?>" class="d-flex
	    				          justify-content-between
	    				          align-items-center p-2">
										<div class="d-flex
	    					            align-items-center">
											<img src="uploads/<?= $conversation['p_p'] ?>" class="w-10 rounded-circle">
											<h3 class="fs-xs m-2">
												<?= $conversation['name'] ?><br>
												<small>
													<?php
													echo lastChat($_SESSION['user_id'], $conversation['id'], $conn);
													?>
												</small>
											</h3>
										</div>
										<?php if (last_seen($conversation['last_seen']) == "Active") { ?>
											<div title="online">
												<div class="online"></div>
											</div>
										<?php } ?>
									</a>
								</li>
							<?php } ?>
						<?php } else { ?>
							<div class="alert alert-info 
    				            text-center">
								<i class="fa fa-comments d-block fs-big"></i>
								No messages yet, Start the conversation
							</div>
						<?php } ?>
					</ul>
				</div>
			</div>


			<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

			<script>
				$(document).ready(function() {

					// Search
					$("#searchText").on("input", function() {
						var searchText = $(this).val();
						if (searchText == "") return;
						$.post('../Public/Pages/Chat/app/ajax/search.php', {
								key: searchText
							},
							function(data, status) {
								$("#chatList").html(data);
							});
					});

					// Search using the button
					$("#serachBtn").on("click", function() {
						var searchText = $("#searchText").val();
						if (searchText == "") return;
						$.post('../Public/Pages/Chat/app/ajax/search.php', {
								key: searchText
							},
							function(data, status) {
								$("#chatList").html(data);
							});
					});


					/** 
					auto update last seen 
					for logged in user
					**/
					let lastSeenUpdate = function() {
						$.get("../Public/Pages/Chat/app/ajax/update_last_seen.php");
					}
					lastSeenUpdate();
					/** 
					auto update last seen 
					every 10 sec
					**/
					setInterval(lastSeenUpdate, 10000);

				});
			</script>




		</div>






		<?
		include("./Public/Pages/Common/footer.php");
		// print_r($_SESSION);
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