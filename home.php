<?php
include("db.php"); // Ensure this file contains the correct database connection code

$errorMessages = [];
$home_page_top_sucuss = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['home_page_top'])) {
    $uploadDir = "uploads/";

    // Create the uploads directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Check if a file was uploaded
    if (isset($_FILES["file_name"]) && $_FILES["file_name"]["error"] == 0) {
        $fileName = basename($_FILES["file_name"]["name"]);
        $fileName = preg_replace("/[^a-zA-Z0-9\-\_\.]/", "", $fileName); // Only allow safe characters
        $targetFile = $uploadDir . $fileName;
        $duration = isset($_POST["duration"]) ? $_POST["duration"] : "N/A";

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES["file_name"]["tmp_name"], $targetFile)) {
            // Prepare and execute the database insert
            $stmt = $pdo->prepare("INSERT INTO files (file_name, duration) VALUES (?, ?)");
            
            if ($stmt->execute([$fileName, $duration])) {
                $home_page_top_sucuss =  "File uploaded and data saved successfully!";
            } else {
                $errorMessages['home_page_top'] = "Error saving file information to the database: " ;
            }

        } else {
            $errorMessages['home_page_top'] = "File upload failed!" ;
           
        }
    } else {
        $errorMessages['home_page_top'] = "No file uploaded or file upload error!" ;
    }
}


// Update duration if form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['home_page_top_duration_edit'])) {
    $id = $_POST['id'];
    $duration = $_POST['duration'];

    $stmt = $pdo->prepare("UPDATE files SET duration = ? WHERE id = ?");
    $stmt->execute([$duration, $id]);

    $home_page_top_sucuss = "Duration updated";
   
}

// Delete file if form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['home_page_top_duration_delete'])) {
    $id = $_POST['id'];

    // Fetch file name to delete from server
    $stmt = $pdo->prepare("SELECT file_name FROM files WHERE id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        $filePath = "uploads/" . $file['file_name'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete record from DB
        $stmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
        $stmt->execute([$id]);

        $home_page_top_sucuss = "File Deleted";
    } else {
        $errorMessages['home_page_top'] = "File not found" ;
    }
  
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['sector2'])) {
    // Handle the form submission to update the record
    if (isset($_POST['upload_image'])) {
        $header = $_POST['header'];
        $description = $_POST['description'];

        // Handle image upload
        $image = $_FILES['image']['name'];
        $imageTmpName = $_FILES['image']['tmp_name'];
        $uploadDirectory = 'uploads/';
        $imagePath = $uploadDirectory . basename($image);
        move_uploaded_file($imageTmpName, $imagePath);

        // Update the record in the database
        $sql = "UPDATE sector_details SET image = ?, header = ?, description = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$imagePath, $header, $description, $sectorId]);

        // Check if the update was successful
        if ($stmt->rowCount() > 0) {
            $successMessage = "Record updated successfully!";
        } else {
            $errorMessage = "Error updating record.";
        }
    }
}

// Fetch the current details from the database to prefill the form
$sectorId = 1; // Set the ID of the record you want to fetch
$sql = "SELECT * FROM sector2 WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$sectorId]);
$sectorDetails = $stmt->fetch(PDO::FETCH_ASSOC);


$stmt = $pdo->prepare("SELECT * FROM files"); // Change 'files' to your actual table name
$stmt->execute();
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>




<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Site</title>
        <style>
                        /* Global Styles */
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                    text-align: center;
                }

                .container {
                    width: 80%;
                    margin: 20px auto;
                    padding: 20px;
                    background-color: white;
                    border-radius: 8px;
                    border: 1px solid black;
                    max-width: 800px;
                }

                h1, .sector-title {
                    font-size: 2rem;
                    margin-bottom: 20px;
                    color: yellow;
                    text-shadow: 3px 3px 4px red;
            
                }

                .sector-title {
                    color: #333;
                }

                /* Card Container */
                .card-container {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 30px;
                    padding: 30px;
                    justify-items: center;
                }

                .card {
                    background-color: #fff;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    width: 100%;
                    max-width: 400px;
                    transition: all 0.3s ease;
                }

                .card:hover {
                    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
                }

                /* Form Styles */
                .form-group {
                    margin-bottom: 15px;
                }

                .form-group label {
                    font-weight: bold;
                    color: #555;
                    display: block;
                    margin-bottom: 8px;
                }

                .form-group input, .form-group textarea, .form-group input[type="file"] {
                    width: 100%;
                    padding: 12px;
                    border: 1px solid #ddd;
                    border-radius: 6px;
                    font-size: 1rem;
                    color: #333;
                    box-sizing: border-box;
                }

                /* Button Styles */
                .btn {
                    padding: 12px 20px;
                    border: none;
                    border-radius: 6px;
                    font-size: 1rem;
                    cursor: pointer;
                    transition: background-color 0.3s ease;
                    width: 100%;
                }

                .add-btn {
                    background-color: #f1f1f1;
                    padding: 8px 12px;
                    border-radius: 6px;
                    cursor: pointer;
                    width: 20%;
                    text-align: center;
                }

                .add-btn:hover {
                    background-color: #ddd;
                }

                .submit-btn {
                    background-color: #007bff;
                    color: white;
                }

                .submit-btn:hover {
                    background-color: #0056b3;
                }

                /* Edit & Delete Buttons */
                .edit-btn {
                    background-color: #007bff;
                    color: white;
                }

                .delete-btn {
                    background-color: #ff4444;
                    color: white;
                }

                .add-btn, .edit-btn, .delete-btn {
                    padding: 10px 15px;
                    margin: 10px;
                    cursor: pointer;
                    border-radius: 5px;
                }

                /* Input Styling */
                input[type="text"], textarea, button {
                    width: 100%;
                    padding: 12px;
                    border: 1px solid black;
                    border-radius: 5px;
                    font-size: 16px;
                }

                button {
                    background: #007bff;
                    color: white;
                    cursor: pointer;
                    transition: 0.3s;
                }

                button:hover {
                    background: #0056b3;
                }

                /* Image Block */
                .block {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 10px;
                    border: 1px solid black;
                    margin: 10px 0;
                    border-radius: 5px;
                    background: #fff;
                }

                .block img {
                    max-width: 50px;
                    max-height: 50px;
                    margin-right: 10px;
                }

                /* Form Styling */
                form {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    gap: 10px;
                }

                input[type="file"] {
                    display: none;
                }

                /* Block Image Upload Button */
                .add-btn {
                    background-color: rgb(16, 204, 63);
                    padding: 8px 12px;
                    border-radius: 6px;
                    cursor: pointer;
                }

                .add-btn:hover {
                    background-color: #ddd;
                }

                /* Form Input Styling */
                .form-input {
                    width: 100%;
                    max-width: 500px;
                    padding: 10px;
                    margin-bottom: 15px;
                    border: 1px solid black;
                    border-radius: 4px;
                    box-sizing: border-box;
                }

                textarea.form-input {
                    height: 150px;  /* Adjust height as needed */
                }
        </style>
    </head>

    <body>
        
        <h1>GERENCIE O SITE</h1>

        <div class="container">
            <p>Inco Page (Top - Desktop) </p>
            <form id="home_page_top" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="home_page_top" value="1">
                <label class="btn add-btn">
                    Add images/videos
                    <input type="file" id="fileInput" name="file_name" accept="image/*,video/*,application/pdf">
                </label>
                
                <div id="blocks" style="display: none;"></div>

            </form>
            <div id="fileContainer">
                <?php foreach ($files as $file): ?>
                    <div class="block">
                        <?php
                        $filePath = "uploads/" . $file['file_name']; 
                        $extension = pathinfo($file['file_name'], PATHINFO_EXTENSION);
                        
                        if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
                            echo '<img src="' . $filePath . '" alt="Uploaded Image" style="max-width: 100px;">';
                        } elseif (in_array(strtolower($extension), ['mp4', 'mov', 'avi'])) {
                            echo '<video src="' . $filePath . '" controls style="max-width: 100px;"></video>';
                        } else {
                            echo '<span>' . htmlspecialchars($file['file_name']) . '</span>';
                        }
                        ?>
                            <form action="" method="POST">                        
                                <input type="hidden" name="home_page_top_duration_edit" value="1">
                                <input type="hidden" name="id" value="<?= $file['id'] ?>">
                                <input type="text" value="<?= $file['duration'] ?? 'N/A' ?>" name="duration" readonly class="duration">
                                <button class="btn edit-btn" type="button">Edit </button>
                            </form>
                        <form action="" method="POST">
                            <input type="hidden" name="id" value="<?= $file['id'] ?>">
                            <input type="hidden" name="home_page_top_duration_delete" value="1">
                            <button type="submit" class="btn delete-btn">Delete</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
            <script>
                document.getElementById('fileInput').addEventListener('change', function(event) {
                    let file = event.target.files[0];
                    if (file) {
                        let blockContainer = document.getElementById('blocks');
                        let newBlock = document.createElement('div');
                        newBlock.className = 'block';

                        let fileDisplay = '';
                        if (file.type.startsWith('image/')) {
                            let imgUrl = URL.createObjectURL(file);
                            fileDisplay = `<img src="${imgUrl}" alt="Uploaded Image" style="max-width: 100px;">`;
                        } else if (file.type.startsWith('video/')) {
                            let videoUrl = URL.createObjectURL(file);
                            fileDisplay = `<video src="${videoUrl}" controls style="max-width: 100px;"></video>`;
                        } else if (file.type === "application/pdf") {
                            fileDisplay = `<span>PDF: ${file.name}</span>`;
                        } else {
                            fileDisplay = `<span>${file.name}</span>`;
                        }

                        newBlock.innerHTML = `
                            ${fileDisplay}
                            <label>10</label>
                            <input type="hidden" value="10" name="duration">
                            <button class="btn edit-btn">Edit </button>
                            <button class="btn delete-btn" onclick="deleteBlock(this)">Delete</button>
                        `;

                        blockContainer.appendChild(newBlock);

                        let form = document.getElementById("home_page_top"); // Replace with your actual form ID
                        form.submit();  // This will submit the form
                    }
                });

                function deleteBlock(button) {
                    button.parentElement.remove();
                }
                document.addEventListener("DOMContentLoaded", function() {
                    document.querySelectorAll(".edit-btn").forEach(function(button) {
                        button.addEventListener("click", function() {
                            let input = this.parentElement.querySelector("input[name='duration']");
                            input.removeAttribute("readonly");
                            input.focus();
                        });
                    });
                });


                // AJAX form submission to prevent page reload
                document.getElementById("home_page_top").addEventListener("submit", function(event) {
                    event.preventDefault(); // Prevent page reload

                    let formData = new FormData(this);
                    let xhr = new XMLHttpRequest();
                    xhr.open("POST", this.action, true);

                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            // Handle the server response (e.g., file uploaded successfully)
                            console.log("File uploaded successfully!");
                            alert("File uploaded and saved successfully.");
                        } else {
                            // Handle the error
                            console.log("Error uploading file.");
                            alert("Error uploading file.");
                        }
                    };

                    xhr.send(formData);
                });
            </script>          
        </div>
    

        <!-- sector 2 -->
        <div class="container">
            <h2>Sector 02</h2>
            
            <?php if (isset($successMessage)) echo "<p style='color: green;'>$successMessage</p>"; ?>
            <?php if (isset($errorMessage)) echo "<p style='color: red;'>$errorMessage</p>"; ?>

            <form action="process_landing_page.php" method="POST">
                
                <input type="hidden" name="landing_page_id" value="<!-- Add Landing Page ID if Editing -->">
                <label for="title">Sector 02 Title:</label>
                <input type="text" name="title" id="title" placeholder="Enter title" required>

                <label for="content">Sector 02 Content:</label>
                <textarea name="content"  placeholder="Enter content" required></textarea>

                            <img id="previewImage" style="display: none; width: 100px; margin-top: 10px;" />
                            <input type="hidden" name="home_page_top" value="1">
                            <label class="btn add-btn">
                                Add images/videos
                                <input type="file" id="fileInput" name="file_name" accept="image/*,video/*,application/pdf" onchange="previewFile(event)">
                            </label>
                            <script>
                                function previewFile(event) {
                                    const file = event.target.files[0];
                                    if (file && file.type.startsWith("image/")) {
                                        const reader = new FileReader();
                                        reader.onload = function(e) {
                                            const previewImage = document.getElementById("previewImage");
                                            previewImage.src = e.target.result;
                                            previewImage.style.display = "block";
                                        };
                                        reader.readAsDataURL(file);
                                    }
                                }
                            </script>
                
            </form>
        </div>

        <!-- sector 3 -->
        <section class="landing-page-form">
            <div class="container">
                <h2>Sector 03</h2>
                
                <!-- section 1  -->
                <section class="landing-pages">
                    <div class="container">
                        <h3>Landing Page 1</h3>
                        <form action="process_landing_page.php" method="POST">
                            
                            <input type="hidden" name="landing_page_id" value="<!-- Add Landing Page ID if Editing -->">
                            <label for="title">Landing Page Title:</label>
                            <input type="text" name="title" id="title" placeholder="Enter title" required>

                            <label for="content">Content:</label>
                            <textarea name="content"  placeholder="Enter content" required></textarea>

                        
                            <img id="preview" style="display: none; width: 100px; margin-top: 10px;" />
                            <input type="hidden" name="home" value="1">
                            <label class="btn add-btn">
                                Add images/videos
                                <input type="file" id="fileInput" name="section1" accept="image/*,video/*,application/pdf" onchange="preview1(event)">
                            </label>
                            <script>
                                function preview1(event) {
                                    const file = event.target.files[0];
                                    if (file && file.type.startsWith("image/")) {
                                        const reader = new FileReader();
                                        reader.onload = function(e) {
                                            const preview = document.getElementById("preview");
                                            preview.src = e.target.result;
                                            preview.style.display = "block";
                                        };
                                        reader.readAsDataURL(file);
                                    }
                                }
                            </script>


                        </form>
                    </div>
                </section>

                <!-- section 2  -->
                <section class="landing-pages">
                    <div class="container">
                    <h3>Landing Page 2</h3>
                        <form action="process_landing_page.php" method="POST">
                            
                            <input type="hidden" name="landing_page_id" value="<!-- Add Landing Page ID if Editing -->">
                            <label for="title">Landing Page Title:</label>
                            <input type="text" name="title" id="title" placeholder="Enter title" required>

                            <label for="content">Content:</label>
                            <textarea name="content"  placeholder="Enter content" required></textarea>

                            <img id="previewImage2" style="display: none; width: 100px; margin-top: 10px;" />
                            <input type="hidden" name="home_page_top" value="1">
                            <label class="btn add-btn">
                                Add images/videos
                                <input type="file" id="fileInput" name="section2" accept="image/*,video/*,application/pdf" onchange="preview2(event)">
                            </label>
                            <script>
                                function preview2(event) {
                                    const file = event.target.files[0];
                                    if (file && file.type.startsWith("image/")) {
                                        const reader = new FileReader();
                                        reader.onload = function(e) {
                                            const previewImage2 = document.getElementById("previewImage2");
                                            previewImage2.src = e.target.result;
                                            previewImage2.style.display = "block";
                                        };
                                        reader.readAsDataURL(file);
                                    }
                                }
                            </script>
                            
                        </form>
                    </div>
                </section>

                <!-- section 3  -->
                <section class="landing-pages">
                    <div class="container">
                        <h3>Landing Page 3</h3>
                        <form action="process_landing_page.php" method="POST">
                        
                            <input type="hidden" name="landing_page_id" value="<!-- Add Landing Page ID if Editing -->">
                            <label for="title">Landing Page Title:</label>
                            <input type="text" name="title" id="title" placeholder="Enter title" required>

                            <label for="content">Content:</label>
                            <textarea name="content"  placeholder="Enter content" required></textarea>

                            <img id="previewImage3" style="display: none; width: 100px; margin-top: 10px;" />
                            <input type="hidden" name="home_page_top" value="1">
                            <label class="btn add-btn">
                                Add images/videos
                                <input type="file" id="fileInput" name="file_name" accept="image/*,video/*,application/pdf" onchange="sectionImage3(event)">
                            </label>
                            <script>
                                function sectionImage3(event) {
                                    const file = event.target.files[0];
                                    if (file && file.type.startsWith("image/")) {
                                        const reader = new FileReader();
                                        reader.onload = function(e) {
                                            const previewImage3 = document.getElementById("previewImage3");
                                            previewImage3.src = e.target.result;
                                            previewImage3.style.display = "block";
                                        };
                                        reader.readAsDataURL(file);
                                    }
                                }
                            </script>
                        
                        </form>
                        </div>
                </section>

                <!-- section 4  -->
                <section class="landing-pages">
                    <div class="container">
                        <h3>Landing Page 4</h3>
                        <form action="process_landing_page.php" method="POST">
                            
                            <input type="hidden" name="landing_page_id" value="<!-- Add Landing Page ID if Editing -->">
                            <label for="title">Landing Page Title:</label>
                            <input type="text" name="title" id="title" placeholder="Enter title" required>

                            <label for="content">Content:</label>
                            <textarea name="content"  placeholder="Enter content" required></textarea>

                            <img id="previewImage4" style="display: none; width: 100px; margin-top: 10px;" />
                            <input type="hidden" name="home_page_top" value="1">
                            <label class="btn add-btn">
                                Add images/videos
                                <input type="file" id="fileInput" name="section4" accept="image/*,video/*,application/pdf" onchange="sectionImage4(event)">
                            </label>
                            <script>
                                function sectionImage4(event) {
                                    const file = event.target.files[0];
                                    if (file && file.type.startsWith("image/")) {
                                        const reader = new FileReader();
                                        reader.onload = function(e) {
                                            const previewImage4 = document.getElementById("previewImage4");
                                            previewImage4.src = e.target.result;
                                            previewImage4.style.display = "block";
                                        };
                                        reader.readAsDataURL(file);
                                    }
                                }
                            </script>
                            
                        </form>
                    </div>
                </section>
            </div> 
        </section>

        <!-- sector 4 -->
        <div class="container">
            <h2>Sector 04</h2>
            
            <?php if (isset($successMessage)) echo "<p style='color: green;'>$successMessage</p>"; ?>
            <?php if (isset($errorMessage)) echo "<p style='color: red;'>$errorMessage</p>"; ?>
            <p>Sector 4 image:</p>
            <form action="process_landing_page.php" method="POST">
            <img id="sectorImage4" style="display: none; width: 100px; margin-top: 10px;" />
                <input type="hidden" name="home_page_top" value="1">
                <label class="btn add-btn">
                    Add images/videos
                    <input type="file" id="fileInput" name="sector4" accept="image/*,video/*,application/pdf" onchange="Imagesector4(event)">
                </label>
                <br>
                
                    <script>
                        function Imagesector4(event) {
                            const file = event.target.files[0];
                            if (file && file.type.startsWith("image/")) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    const sectorImage4 = document.getElementById("sectorImage4");
                                    sectorImage4.src = e.target.result;
                                    sectorImage4.style.display = "block";
                                };
                                reader.readAsDataURL(file);
                            }
                        }
                    </script>
            </form>
        </div>

        <!-- sector 5 -->
        <div class="container">
            <h2>Sector 05 </h2>
            <form id="sector5_form" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="sector5" value="1">
                <label class="btn add-btn" style="width: 200px;">
                    Add images
                    <input type="file" id="sector5FileInput" name="sectorImage5[]" accept="image/*" multiple>
                </label>
                <div id="sector5_blocks" class="gallery" style="width: 700px;"></div>
            </form>

            <div id="sector5_fileContainer" >
                <?php if (!empty($files)): ?>
                    <div class="gallery" >
                        <?php foreach ($files as $file): ?>
                            <div class="block">
                                <?php
                                $filePath = "uploads/" . htmlspecialchars($file['sectorImage5'], ENT_QUOTES, 'UTF-8');
                                echo '<img src="' . $filePath . '" alt="Uploaded Image">';
                                ?>
                                <input type="text" name="image_descriptions[]"  class="image-description" placeholder="Enter image description" value="<?= htmlspecialchars($file['description'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <form action="" method="POST">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($file['id'], ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="sector5_delete"value="1">
                                    <button type="submit" class="btn delete-btn" >Delete</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <script>
                let selectedFiles = [];

                document.getElementById('sector5FileInput').addEventListener('change', function(event) {
                    let files = Array.from(event.target.files);
                    let blockContainer = document.getElementById('sector5_blocks');

                    

                    files.forEach(file => {
                        if (selectedFiles) {
                            selectedFiles.push(file);
                            let newBlock = document.createElement('div');
                            newBlock.className = 'block';
                            let imgUrl = URL.createObjectURL(file);
                            newBlock.innerHTML = `
                                <img src="${imgUrl}" alt="Uploaded Image">
                                <input type="text" name="image_descriptions[]" class="image-description" placeholder="Enter hifer link">
                                <button type="button" style="width: 150px;" class="btn delete-btn" onclick="deleteBlock(this, '${imgUrl}')">Delete</button>
                            `;
                            blockContainer.appendChild(newBlock);
                        }
                    });

                    updateUploadButton();
                });
                
                function deleteBlock(button, imgUrl) {
                    button.parentElement.remove();
                    selectedFiles = selectedFiles.filter(file => URL.createObjectURL(file) !== imgUrl);
                    updateUploadButton();
                }

                function updateUploadButton() {
                    document.getElementById("uploadButton").disabled = selectedFiles.length === 0;
                }
            </script>

        </div>

    </body>
</html>
