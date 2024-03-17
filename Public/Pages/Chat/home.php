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
		/* Custom CSS styles */
		.chat-box {
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
		}

		.rtext {
			background-color: blue;
			color: aliceblue;
		}

		emoji-picker {
			position: absolute;
			bottom: 50px;
			/* Adjust based on your layout */
			right: 20px;
			/* Adjust based on your layout */
			display: none;
			/* Hide by default */
		}

		.emoji-picker {
			position: absolute;
			bottom: 60px;
			/* Adjust based on your layout */
			border: 1px solid #ddd;
			padding: 5px;
			background-color: white;
			width: 200px;
			/* Adjust as necessary */
			display: grid;
			grid-template-columns: repeat(8, 1fr);
			/* Adjust column count based on preference */
			gap: 5px;
			overflow-y: auto;
			max-height: 200px;
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
				<a href="home.php" class="fs-4 link-dark">&#8592;</a>

				<div class="d-flex align-items-center">
					<img src="uploads/<?= $chatWith['p_p'] ?>" class="w-15 rounded-circle">

					<h3 class="display-4 fs-sm m-2">
						<?= $chatWith['name'] ?> <br>
						<div class="d-flex
               	              align-items-center" title="online">
							<?php
							if (last_seen($chatWith['last_seen']) == "Active") {
							?>
								<div class="online"></div>
								<small class="d-block p-1">Online</small>
							<?php } else { ?>
								<small class="d-block p-1">
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
									<small class="d-block">
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
					<button class="btn btn-outline-secondary emoji-picker-button" type="button">😊</button>
					<textarea cols="3" id="message" class="form-control"></textarea>
					<button class="btn btn-primary" id="sendBtn">
						<i class="fa fa-paper-plane">Send</i>
					</button>
				</div>
				<div id="emojiPicker" class="emoji-picker" style="display: none;"></div>

			</div>

			<script>
				document.addEventListener('DOMContentLoaded', function() {
					const emojiPicker = document.getElementById('emojiPicker');
					const toggleButton = document.getElementById('emojiPickerToggle');
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
				});
			</script>
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

			<script>
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
			textarea.addEventListener('keydown', function(event) {
				if (event.key === 'Enter' && !event.shiftKey) {
					event.preventDefault(); // Prevent new line in textarea
					sendMessage();
				}
			});

			// Function to send the message
			function sendMessage() {
				const message = textarea.value.trim();
				if (message !== '') {
					// Add your code to send the message here
					console.log('Message sent:', message);
					textarea.value = ''; // Clear the textarea after sending
				}
			}
		});
	</script>

</body>

</html>