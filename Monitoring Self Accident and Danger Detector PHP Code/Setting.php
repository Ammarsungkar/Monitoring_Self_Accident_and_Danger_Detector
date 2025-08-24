<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include the database connection file
require_once "connection.php";

// Fetch custom message for the logged-in user
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id_message, message FROM user_message WHERE id_user = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$message_data = $result->fetch_assoc();
$stmt->close();

// Fetch emergency numbers for the logged-in user
$stmt = $conn->prepare("SELECT id_phone, phone_number FROM user_phone WHERE id_user = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$emergency_numbers = [];
while ($row = $result->fetch_assoc()) {
    $emergency_numbers[] = $row;
}
$stmt->close();

// Process adding new emergency number or custom message
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['numberInput']) && !empty($_POST['numberInput'])) {
        // Adding new emergency number
        $new_number = $_POST['numberInput'];
        // Check if the number already exists
        if (!in_array($new_number, array_column($emergency_numbers, 'phone_number'))) {
            $stmt = $conn->prepare("INSERT INTO user_phone (id_user, phone_number) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $new_number);
            if ($stmt->execute()) {
                // Refresh emergency numbers list
                $stmt->close();
                $stmt = $conn->prepare("SELECT id_phone, phone_number FROM user_phone WHERE id_user = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $emergency_numbers = [];
                $new_number = substr($_POST['numberInput'], 0, 5);

                while ($row = $result->fetch_assoc()) {
                    $emergency_numbers[] = $row;
                }
                $stmt->close();
                echo json_encode(array("success" => true, "message" => "Emergency number added successfully."));
                exit;
            } else {
                echo json_encode(array("success" => false, "message" => "Error adding emergency number."));
                exit;
            }
        } else {
            echo json_encode(array("success" => false, "message" => "Emergency number already exists."));
            exit;
        }
    } elseif (isset($_POST['message']) && !empty($_POST['message'])) {
        // Adding new custom message
        $message = $_POST['message'];
        if (!empty($message_data)) {
            // Update existing message
            $stmt = $conn->prepare("UPDATE user_message SET message = ? WHERE id_message = ?");
            $stmt->bind_param("si", $message, $message_data['id_message']);
        } else {
            // Insert new message
            $stmt = $conn->prepare("INSERT INTO user_message (id_user, message) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $message);
        }
        if ($stmt->execute()) {
            echo json_encode(array("success" => true, "message" => "Custom message added successfully."));
            exit;
        } else {
            echo json_encode(array("success" => false, "message" => "Error adding custom message."));
            exit;
        }
        $stmt->close();
    } elseif (isset($_POST['edit_number']) && isset($_POST['new_number'])) {
        // Edit existing emergency number
        $edit_id = $_POST['edit_number'];
        $new_number = $_POST['new_number'];
        $stmt = $conn->prepare("UPDATE user_phone SET phone_number = ? WHERE id_phone = ?");
        $stmt->bind_param("si", $new_number, $edit_id);
        if ($stmt->execute()) {
            echo json_encode(array("success" => true, "message" => "Emergency number updated successfully."));
            exit;
        } else {
            echo json_encode(array("success" => false, "message" => "Error updating emergency number."));
            exit;
        }
    } elseif (isset($_POST['delete_number'])) {
        // Delete existing emergency number
        $delete_id = $_POST['delete_number'];
        $stmt = $conn->prepare("DELETE FROM user_phone WHERE id_phone = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            echo json_encode(array("success" => true, "message" => "Emergency number deleted successfully."));
            exit;
        } else {
            echo json_encode(array("success" => false, "message" => "Error deleting emergency number."));
            exit;
        }
    } else {
        echo json_encode(array("success" => false, "message" => "Invalid input."));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Numbers</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="SettingPage.css" />
</head>
<body>
    <div class="container">
    <a href="homepage.php" class="back-btn2">
    <span class="arrow">&#8592;</span>Back</a>
        <h1>Emergency Help Setting</h1>
        <br />
        <h2>Input emergency message</h2>
        <form id="editMessageForm">
            <textarea id="message" name="message" rows="4" required><?= !empty($message_data) ? $message_data['message'] : '' ?></textarea>
            <button type="submit">Save Message</button>
        </form>

        <h2>Input emergency number</h2>
        <form id="addEmergencyForm">
            <input type="text" id="numberInput" name="numberInput" required>
            <button type="submit">Add Number</button>
        </form>

        <table id="emergencyValueTable">
            <thead>
                <tr>
                    <th>Value</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($emergency_numbers as $number) : ?>
                    <tr>
                        <td><?= $number['phone_number'] ?></td>
                        <td>
                            <button class="edit-number" data-id="<?= $number['id_phone'] ?>">Edit</button>
                            <button class="delete-number" data-id="<?= $number['id_phone'] ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>


    <script>
    $(document).ready(function() {
        $("#addEmergencyForm").submit(function(event) {
            event.preventDefault(); // Prevent default form submission
            var formData = $(this).serialize(); // Serialize form data
            $.ajax({
                url: "<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>", // PHP script URL
                type: "POST", // Request method
                data: formData, // Form data
                dataType: "json", // Data type
                success: function(response) {
                    if (response.success) {
                        alert(response.message); // Show success message
                        location.reload(); // Reload the page to reflect changes
                    } else {
                        alert(response.message); // Show error message
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText); // Log error message
                    alert("An error occurred. Please try again."); // Show generic error message
                }
            });
        });

        $("#editMessageForm").submit(function(event) {
            event.preventDefault(); // Prevent default form submission
            var formData = $(this).serialize(); // Serialize form data
            $.ajax({
                url: "<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>", // PHP script URL
                type: "POST", // Request method
                data: formData, // Form data
                dataType: "json", // Data type
                success: function(response) {
                    if (response.success) {
                        alert(response.message); // Show success message
                    } else {
                        alert(response.message); // Show error message
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText); // Log error message
                    alert("An error occurred. Please try again."); // Show generic error message
                }
            });
        });

        // Handle edit number button click
        $(document).on("click", ".edit-number", function() {
            var id = $(this).data("id");
            var newNumber = prompt("Enter the new emergency number:");
            if (newNumber !== null) {
                $.ajax({
                    url: "<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>", // PHP script URL for editing number
                    type: "POST",
                    data: { edit_number: id, new_number: newNumber },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            alert(response.message); // Show success message
                            location.reload(); // Reload the page to reflect changes
                        } else {
                            alert(response.message); // Show error message
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText); // Log error message
                        alert("An error occurred. Please try again."); // Show generic error message
                    }
                });
            }
        });

        // Handle delete number button click
        $(document).on("click", ".delete-number", function() {
            var id = $(this).data("id");
            if (confirm("Are you sure you want to delete this emergency number?")) {
                $.ajax({
                    url: "<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>", // PHP script URL for deleting number
                    type: "POST",
                    data: { delete_number: id },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            alert(response.message); // Show success message
                            location.reload(); // Reload the page to reflect changes
                        } else {
                            alert(response.message); // Show error message
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText); // Log error message
                        alert("An error occurred. Please try again."); // Show generic error message
                    }
                });
            }
        });
    });
    </script>
</body>
</html>