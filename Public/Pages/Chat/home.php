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

		include 'app/helpers/user.php';
		include 'app/helpers/chat.php';
		include 'app/helpers/opened.php';

		include 'app/helpers/timeAgo.php';

		if (!isset($_GET['user'])) {
			header("Location: home.php");
			exit;
		}

		# Getting User data data
		$chatWith = getUser($_GET['user'], $conn);

		if (empty($chatWith)) {
			header("Location: home.php");
			exit;
		}

		$chats = getChats($_SESSION['user_id'], $chatWith['id'], $conn);

		opened($chatWith['id'], $conn, $chats);
	}


	// print($uri);
	?>

	<style>
		.vh-100 {
			min-height: 100vh;
		}

		.w-400 {
			width: 800px;
		}

		.fs-xs {
			font-size: 1rem;
		}

		.w-10 {
			width: 10%;
		}

		a {
			text-decoration: none;
		}

		.fs-big {
			font-size: 5rem !important;
		}

		.online {
			width: 10px;
			height: 10px;
			background: green;
			border-radius: 50%;
		}

		.w-15 {
			width: 10%;
		}

		.fs-sm {
			font-size: 2rem;
		}

		.display-4 {
			font-size: 1.5rem !important;
		}

		small {
			color: #bbb;
			font-size: 0.7rem;
			text-align: right;
		}

		.chat-box {
			overflow-y: auto;
			overflow-x: hidden;
			max-height: 50vh;
		}

		.rtext {
			width: 65%;
			background: #f8f9fa;
			color: #444;
		}

		.ltext {
			width: 65%;
			background: #3289c8;
			color: #fff;
		}

		/* width */
		*::-webkit-scrollbar {
			width: 3px;
		}

		/* Track */
		*::-webkit-scrollbar-track {
			background: #f1f1f1;
		}

		/* Handle */
		*::-webkit-scrollbar-thumb {
			background: #aaa;
		}

		/* Handle on hover */
		*::-webkit-scrollbar-thumb:hover {
			background: #3289c8;
		}

		textarea {
			resize: none;
		}

		/*message_status*/
		/* Custom CSS styles */
		.chat-box {
			max-width: 750px;
			max-height: 300px;
			/* Limit the height of the chat box */
			overflow-y: auto;
			/* Enable vertical scrolling */
		}

		.chat-box p {
			margin: 5px 0;
			/* Add spacing between chat messages */
		}

		.chat-input-group {
			position: relative;
			/* Set position to relative for proper alignment */
		}

		#message {
			border-radius: 20px;
			/* Adjust border radius for message input */
			resize: none;
			/* Disable resizing of textarea */
		}

		#sendBtn {
			position: absolute;
			/* Position the send button */
			right: 10px;
			bottom: 10px;
		}

		.ltext {
			background-color: blueviolet;
			color: black;
			max-width: 50%;
			font-size: large;
		}

		.rtext {
			background-color: blue;
			color: aliceblue;
			max-width: 50%;
			font-size: large;


		}


		.emoji-picker {
			position: absolute;
			bottom: 60px;
			/* Adjust based on your layout */
			border: 1px solid #ddd;
			padding: 5px;
			background-color: white;
			width: 400px;
			/* Adjust as necessary */
			display: grid;
			grid-template-columns: repeat(8, 1fr);
			/* Adjust column count based on preference */
			gap: 5px;
			overflow-y: auto;
			max-height: 400px;
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
			<div class="w-400 shadow p-4 rounded">
				<a href="./Chat_l" class="fs-4 link-dark">&#8592;</a>

				<div class="d-flex align-items-center">
					<img src="../assets/images/avatars/<?= !empty($chatWith['p_p']) ? $chatWith['p_p'] : '07.png' ?>" class="w-15 rounded-circle">

					<h3 class="display-4">
						<?= $chatWith['name'] ?> <br>
						<div class="d-flex
               	              align-items-center" title="online">
							<?php
							if (last_seen($chatWith['last_seen']) == "Active") {
							?>
								<div class="online"></div>
								<small class="d-block p-1">Online</small>
							<?php } else { ?>
								<small style="max-width: small;" class="d-block p-1">
									Last seen:
									<?= last_seen($chatWith['last_seen']) ?>
								</small>
							<?php } ?>
						</div>
					</h3>
				</div>

				<div class="shadow p-4 rounded
    	               d-flex flex-column
    	               mt-2 chat-box" id="chatBox">
					<?php
					if (!empty($chats)) {
						foreach ($chats as $chat) {
							if ($chat['from_id'] == $_SESSION['user_id']) { ?>
								<p class="rtext align-self-end
						        border rounded p-2 mb-1">
									<?= $chat['message'] ?>
									<small class="d-block">
										<?= $chat['created_at'] ?>
									</small>
								</p>
							<?php } else { ?>
								<p class="ltext border 
					         rounded p-2 mb-1">
									<?= $chat['message'] ?>
									<?php
									$attachmentHTML = '';
									if (!empty($chat['attachment'])) {
										// Assuming the attachment field contains the filename of the image
										$imageUrl = "../uploads/" . $chat['attachment']; // Adjust the path as needed
										$attachmentHTML = "<img src='{$imageUrl}' alt='Attachment' style='max-width: 200px; display: block;'>";
									}
									// echo $attachmentHTML;

									?>
									<?= $attachmentHTML ?>

									<small style="" class="d-block">
										<?= $chat['created_at'] ?>
									</small>
								</p>
						<?php }
						}
					} else { ?>
						<div class="alert alert-info 
    				            text-center">
							<i class="fa fa-comments d-block fs-big"></i>
						</div>
					<?php } ?>
				</div>
				<!-- Remove the previous emoji-picker element -->
				<div class="input-group mb-3">
					<button class="btn btn-outline-secondary" type="button" id="attachmentBtn">📎</button>
					<input type="file" id="fileInput" style="display: none;">

					<button class="btn btn-outline-secondary emoji-picker-button" type="button">😊</button>
					<textarea cols="3" id="message" class="form-control"></textarea>
					<button class="btn btn-primary" id="sendBtn">
						<i class="fa fa-paper-plane">Send</i>
					</button>
				</div>
				<div id="emojiPicker" class="emoji-picker" style="display: none;"></div>

			</div>

			<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

			<script>
				document.getElementById('attachmentBtn').addEventListener('click', function() {
					document.getElementById('fileInput').click(); // Simulate click on the file input when attachment button is clicked
				});

				document.getElementById('fileInput').addEventListener('change', function() {
					sendMessage(); // Trigger message send when a file is selected
				});

				function sendMessage() {
					const message = document.getElementById('message').value.trim();
					const fileInput = document.getElementById('fileInput');
					const formData = new FormData();

					formData.append('message', message);
					if (fileInput.files[0]) {
						formData.append('attachment', fileInput.files[0]);
					}

					formData.append('to_id', <?= json_encode($chatWith['id']) ?>); // Adjust to ensure correct variable handling

					// Make the AJAX call using formData
					$.ajax({
						url: "../Public/Pages/Chat/app/ajax/insert.php",
						type: "POST",
						data: formData,
						processData: false, // Prevent jQuery from automatically transforming the data into a query string
						contentType: false, // Set content type to false as jQuery will tell the server its a query string request
						success: function(data) {
							document.getElementById('message').value = ""; // Clear the message input field
							document.getElementById('fileInput').value = ""; // Reset the file input
							$("#chatBox").append(data); // Assuming you want to append the message to the chat box
							scrollDown(); // Ensure the chat box scrolls to the latest message
						}
					});
				}

				document.addEventListener('DOMContentLoaded', function() {
					const textarea = document.getElementById('message');
					const sendBtn = document.getElementById('sendBtn'); // Reference to the send button

					// Function to send the message
					function sendMessage() {
						const message = textarea.value.trim();
						console.log(message);
						if (message !== '') {
							// Perform AJAX call to insert.php
							$.post("../Public/Pages/Chat/app/ajax/insert.php", {
									message: message,
									to_id: <?= json_encode($chatWith['id']) ?> // Ensure PHP variable is correctly encoded for JavaScript
								},
								function(data, status) {
									$("#message").val(""); // Clear the textarea after sending
									$("#chatBox").append(data); // Assuming you want to append the message to the chat box
									scrollDown(); // Ensure the chat box scrolls to the latest message
								});
						}
					}

					// Event listener for the send button
					sendBtn.addEventListener('click', function() {
						sendMessage();
					});

					// Event listener for the Enter key in the textarea
					textarea.addEventListener('keydown', function(event) {
						if (event.key === "Enter" && !event.shiftKey) {
							event.preventDefault(); // Prevent new line
							sendMessage(); // Send the message
						}
					});
				});

				var scrollDown = function() {
					let chatBox = document.getElementById('chatBox');
					chatBox.scrollTop = chatBox.scrollHeight;
				}

				scrollDown();

				$(document).ready(function() {

					$("#sendBtn").on('click', function() {
						message = $("#message").val();
						if (message == "") return;

						$.post("../Public/Pages/Chat/app/ajax/insert.php", {
								message: message,
								to_id: <?= $chatWith['id'] ?>
							},
							function(data, status) {
								$("#message").val("");
								$("#chatBox").append(data);
								scrollDown();
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



					// auto refresh / reload
					let fechData = function() {
						$.post("../Public/Pages/Chat/app/ajax/getMessage.php", {
								id_2: <?= $chatWith['id'] ?>
							},
							function(data, status) {
								$("#chatBox").append(data);
								if (data != "") scrollDown();
							});
					}

					fechData();
					/** 
					auto update last seen 
					every 0.5 sec
					**/
					setInterval(fechData, 500);

				});
				document.addEventListener('DOMContentLoaded', function() {
					const emojiPicker = document.getElementById('emojiPicker');
					const toggleButton = document.querySelector('.emoji-picker-button');
					const textarea = document.getElementById('message');

					// Emoji list example, add more as needed
					const emojis = ['😀', '😁', '😂', '🤣', '😃', '😄', '😅', '😆', '😉', '😊', '😋', '😎', '😍', '😘', '🥰', '😗', '😙', '😚', '🙂', '🤗'];

					// Populate the emoji picker
					emojis.forEach(emoji => {
						const button = document.createElement('button');
						button.textContent = emoji;
						button.style.border = 'none';
						button.style.background = 'transparent';
						button.style.cursor = 'pointer';
						button.onclick = function() {
							textarea.value += emoji;
							emojiPicker.style.display = 'none'; // Hide picker after selection
						};
						emojiPicker.appendChild(button);
					});

					// Toggle emoji picker display
					toggleButton.addEventListener('click', function() {
						const isDisplayed = window.getComputedStyle(emojiPicker).display !== 'none';
						emojiPicker.style.display = isDisplayed ? 'none' : 'block';
					});

					// Hide emoji picker when clicking outside
					document.addEventListener('click', function(event) {
						if (!emojiPicker.contains(event.target) && event.target !== toggleButton) {
							emojiPicker.style.display = 'none';
						}
					});

					// Send message on Enter key press
					textarea.addEventListener('keypress', function(event) {
						if (event.key === "Enter" && !event.shiftKey) {
							event.preventDefault(); // Prevent new line in textarea
							sendMessage();
						}
					});

					// Function to send the message
					function sendMessage() {
						const message = textarea.value.trim();
						if (message !== '') {
							console.log('Message sent:', message);
							textarea.value = ''; // Clear the textarea after sending
						}
					}
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
	<script>
	</script>

</body>

</html>