<?php
session_start();
require 'db.php'; // Include database connection

$errorMessages = [];
$successMessage = "";
$successMessage_user = "";
$successMessage_Contact = "";
$successMessage_work = "";
$successMessage_contact_email = "";
$successMessage_contact_email_message="";


$userId = $_SESSION['user_id'] ?? null;
$username = "";
$email = "";
$work_with_us_email = '';

if ($userId) {
    // Fetch current username
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $username = $user['username'] ?? '';

     // Fetch contact emails
     $stmt = $pdo->prepare("SELECT * FROM  contact_emails WHERE id = ?");
     $stmt->execute([1]);
     $contact = $stmt->fetch(PDO::FETCH_ASSOC);
     $block_contact = $contact['block_contact'] ?? '0';
     $block_contact_message = $contact['block_contact_message'] ?? '';

     // Fetch contact emails
     $stmt = $pdo->prepare("SELECT email FROM  contact_emails WHERE id = ?");
     $stmt->execute([1]);
     $contact = $stmt->fetch(PDO::FETCH_ASSOC);
     $contact_email = $contact['email'] ?? '';

     // Fetch work with us
     $stmt = $pdo->prepare("SELECT email FROM work_with_us WHERE id = ?");
     $stmt->execute([1]);
     $work = $stmt->fetch(PDO::FETCH_ASSOC);
     $work_with_us_email = $work['email'] ?? '';




}

if ($_SERVER["REQUEST_METHOD"] == "POST"  && isset($_POST['repitpassword'])) {
    $currentPassword = $_POST['currentpassword'] ?? '';
    $newPassword = $_POST['newpassword'] ?? '';
    $repeatPassword = $_POST['repitpassword'] ?? '';
    $userId = $_SESSION['user_id'] ?? null; // Assume the user is logged in

    if (!$userId) {
        $errorMessages['general'] = "User is not logged in.";
    }

    // Validate password length
    if (strlen($newPassword) < 10 || strlen($newPassword) > 100) {
        $errorMessages['newpassword'] = "New password must be between 10 and 100 characters.";
    }

    // Check if new password and repeat password match
    if ($newPassword !== $repeatPassword) {
        $errorMessages['repitpassword'] = "New password and repeat password do not match.";
    }

    if (empty($errorMessages)) {
        // Fetch current password from database
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verify current password
            if (!password_verify($currentPassword, $user['password'])) {
                $errorMessages['currentpassword'] = "Current password is incorrect.";
            } else {
                // Hash new password
                $newHashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

                // Update password in database
                $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($updateStmt->execute([$newHashedPassword, $userId])) {
                    $successMessage = "Password updated successfully.";
                } else {
                    $errorMessages['general'] = "Error updating password.";
                }
            }
        } else {
            $errorMessages['general'] = "User not found.";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_username'])) {
    $newUsername = trim($_POST['username'] ?? '');

    if (!empty($newUsername) && $userId) {
        $updateStmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
        if ($updateStmt->execute([$newUsername, $userId])) {
            $successMessage_user = "Username updated successfully.";
            $username = $newUsername; // Update displayed username
        } else {
            $errorMessages['user'] = "Error updating username.";
        }
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_block_contact'])) {
    $block_contact = trim($_POST['block_contact'] ?? '0');
    
    $updateStmt = $pdo->prepare("UPDATE contact_emails SET block_contact = ? WHERE id = ?");
    if ($updateStmt->execute([$block_contact, 1])) {
        $successMessage_contact_email = "Contact Email Access updated successfully.";  
                    
    } else {
        $errorMessages['contact_email_message'] = "Error updating Contact Email Access.";
    }

    $block_contact_message = '';
    $updateStmt = $pdo->prepare("UPDATE contact_emails SET block_contact_message = ? WHERE id = ?");
    $updateStmt->execute([$block_contact_message, 1]);

      
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['block_contact_message'])) {
    $block_contact_message = trim($_POST['block_contact_message'] ?? '');
    
    
    if (!empty($block_contact_message)) {
        $updateStmt = $pdo->prepare("UPDATE contact_emails SET block_contact_message = ? WHERE id = ?");
        if ($updateStmt->execute([$block_contact_message, 1])) {
            $successMessage_contact_email_message = "Contact Email Block Text updated successfully.";           
        } else {
            $errorMessages['contact_email_message'] = "Error updating Contact Email Block Text Access.";
        }
    }

    
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_contact_email'])) {
    // Initialize error message array
    $errorMessages = [];

    // Get the emails from the input and trim unnecessary spaces
    $email = trim($_POST['email']); // Capture input
    $emails = explode(';', $email); // Split by semicolons
    $validEmails = [];

    // Validate each email
    foreach ($emails as $singleEmail) {
        $singleEmail = trim($singleEmail); // Remove extra spaces
        if (filter_var($singleEmail, FILTER_VALIDATE_EMAIL)) {
            $validEmails[] = $singleEmail; // Add valid email to the list
        } else {
            $errorMessages['contact'] = "Invalid email format: $singleEmail";
        }
    }

    // If all emails are valid, update the database with the original input
    if (empty($errorMessages)) {
        $stmt = $pdo->prepare("UPDATE contact_emails SET email = ? WHERE id = 1");

        if (!$stmt->execute([$email])) {  // Use original input format
            $errorMessages['contact'] = "Error updating contact emails";
        } else {
            $successMessage_Contact = "Emails updated successfully.";
            $contact_email = $email;
        }
    }
}

// Handle Work With Us Email form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_workWithUs_email'])) {

    $errorMessages = [];

    // Get the emails from the input and trim unnecessary spaces
    $email = trim($_POST['email']); // Capture input
    $emails = explode(';', $email); // Split by semicolons
    $validEmails = [];

    // Validate each email
    foreach ($emails as $singleEmail) {
        $singleEmail = trim($singleEmail); // Remove extra spaces
        if (filter_var($singleEmail, FILTER_VALIDATE_EMAIL)) {
            $validEmails[] = $singleEmail; // Add valid email to the list
        } else {
            $errorMessages['work'] = "Invalid email format: $singleEmail";
        }
    }

    // If all emails are valid, update the database with the original input
    if (empty($errorMessages)) {
        $stmt = $pdo->prepare("UPDATE work_with_us SET email = ? WHERE id = 1");

        if (!$stmt->execute([$email])) {  // Use original input format
            $errorMessages['work'] = "Error updating work with us emails";
        } else {
            $successMessage_work = "Emails updated successfully.";
            $work_with_us_email = $email;
        }
    }

    
    
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        .header {
            background: #2525aa;
            color: yellow;
            font-size: 36px;
            font-weight: bold;
            padding: 20px;
            text-shadow: 3px 3px 5px red;
        }
        .green-box {
            background: #00cc00;
            padding: 20px;
            width: 50%;
            margin: 20px auto;
            border-radius: 15px;
            border: 3px solid black;
        }
        .green-box h3 {
            color: black;
            background: white;
            display: inline-block;
            padding: 5px 15px;
            border-radius: 10px;
        }
        .green-box input {
            display: block;
            width: 80%;
            margin: 10px auto;
            padding: 10px;
            font-size: 16px;
            border: 1px solid black;
            border-radius: 5px;
        }
        .ok-button {
            background: white;
            color: black;
            padding: 10px 30px;
            border: 2px solid black;
            font-size: 18px;
            cursor: pointer;
            margin-top: 10px;
            border-radius: 10px;
        }
        .info-section {
            width: 60%;
            margin: 20px auto;
            text-align: left;
        }
        .info-section input {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            font-size: 14px;
            border: 1px solid black;
            border-radius: 5px;
        }
        .info-section p {
            font-size: 16px;
            font-weight: bold;
        }
        .checkbox-section {
            margin: 10px 0;
        }
        .blue-buttons {
            margin: 20px auto;
        }
        .blue-buttons button {
            background: #0066ff;
            color: white;
            padding: 10px 20px;
            font-size: 18px;
            margin: 10px;
            border: none;
            cursor: pointer;
            border-radius: 10px;
        }
    </style>
</head>
<body>
<div class="header">GERENCIE O SITE</div>
    
    <div class="green-box">
        <form action="" method="POST">
            <h3>Trocar Senha</h3>
            <?php if (!empty($successMessage)): ?>
                <p class="success"><?php echo $successMessage; ?></p>
            <?php endif; ?>
            <?php if (!empty($errorMessages['general'])): ?>
                <p class="error"><?php echo $errorMessages['general']; ?></p>
            <?php endif; ?>
            <input type="password" placeholder="Digite a senha atual" name="currentpassword" class="<?php echo isset($errorMessages['currentpassword']) ? 'input-error' : ''; ?>" required>
            <span class="error"><?php echo $errorMessages['currentpassword'] ?? ''; ?></span>
            <input type="password" placeholder="Digite a nova senha" name="newpassword" class="<?php echo isset($errorMessages['newpassword']) ? 'input-error' : ''; ?>" required>
            <span class="error"><?php echo $errorMessages['newpassword'] ?? ''; ?></span>
            <input type="password" placeholder="Repita a nova senha" name="repitpassword" class="<?php echo isset($errorMessages['repitpassword']) ? 'input-error' : ''; ?>" required>
            <span class="error"><?php echo $errorMessages['repitpassword'] ?? ''; ?></span>
            <button class="ok-button" type="submit">OK</button>
        </form>
    </div>
  
    <div class="info-section">
        <form id="usernameForm" method="POST">
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" placeholder="Digite aqui o e-mail para login">
            <input type="hidden" name="update_username" value="1">
        </form>

        <?php if (!empty($successMessage_user)): ?>
            <p class="success"><?php echo $successMessage_user; ?></p>
        <?php endif; ?>
        <?php if (!empty($errorMessages['user'])): ?>
            <p class="error"><?php echo $errorMessages['user']; ?></p>
        <?php endif; ?>
    
        <form action="" method="POST" id="block_contact_form">
            <p>Desabilitar o envio de mensagens aos visitantes na página "Contato".</p>
            <div class="checkbox-section">
                <input type="hidden" name="update_block_contact" value="1">
                <input type="hidden" value="0" name="block_contact">
                <input type="checkbox" id="disable-messages" name="block_contact" value="1" <?php if ($block_contact==1) { ?>  checked <?php } ?>>
                <label for="disable-messages">Desabilitar</label>
            </div>
            <input type="text" id="mensagem" placeholder="Digite aqui a mensagem" name="block_contact_message" <?php if ($block_contact!=1) { ?>  style="display: none;" <?php } ?> value="<?php echo htmlspecialchars($block_contact_message); ?>" >
            <?php if (!empty($successMessage_contact_email_message)) { ?>
                <p style="color: green;"><?php echo $successMessage_contact_email_message; ?></p>
            <?php } ?>
            <?php if (!empty($errorMessages['contact_email_message'])): ?>
                <p class="error"><?php echo $errorMessages['contact_email_message']; ?></p>
            <?php endif; ?>
        </form>
        <script>
            document.getElementById("disable-messages").addEventListener("change", function() {
                let mensagemInput = document.getElementById("mensagem");
                mensagemInput.style.display = this.checked ? "block" : "none";
                
                // Submit the form when the checkbox is toggled
                let form = document.getElementById("block_contact_form"); // Replace with your actual form ID
                form.submit();  // This will submit the form
            });
        </script>


        <!-- Contact Email Form -->
        <form method="POST" action="">
            <input type="hidden" name="update_contact_email" value="1">
            <input type="text" placeholder="Digite aqui os e-mails" name="email" id="contactemail" 
            value="<?php echo htmlspecialchars($contact_email); ?>" required>
        </form>
        <?php if (!empty($successMessage_Contact)) { ?>
            <p style="color: green;"><?php echo $successMessage_Contact; ?></p>
        <?php } ?>
        <?php if (!empty($errorMessages['contact'])): ?>
            <p class="error"><?php echo $errorMessages['contact']; ?></p>
        <?php endif; ?>


        <!-- Work With Us Email Form -->
        <form method="POST" action="">
            <input type="hidden" name="update_workWithUs_email" value="1">
            <input type="text" placeholder="Digite aqui os e-mails (separados por ;)" name="email" id="workwithus_email" 
                value="<?php echo htmlspecialchars($work_with_us_email); ?>" required>
            
        </form>

        <!-- Success or Error Messages for Work With Us Form -->
        <?php if (!empty($successMessage_work)) { ?>
            <p style="color: green;"><?php echo $successMessage_work; ?></p>
        <?php } ?>

        <?php if (!empty($errorMessages['work'])): ?>
                <p class="error"><?php echo $errorMessages['work']; ?></p>
        <?php endif; ?>
       
    </div>
    
    <div class="blue-buttons">
        <a href="home.php"><button>Inserir imagens/vídeos</button></a>
        <a href=""><button type="submit">Sair do site</button></a> 
    </div>

</body>
</html>