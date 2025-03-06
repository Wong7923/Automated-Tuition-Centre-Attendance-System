<?php
session_start();
require_once __DIR__ . '/../Controller/StudentClassReplacementController.php';

// Redirect if not logged in
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../login.php?error=Unauthorized access.");
    exit();
}
$studentID = $_SESSION['user']['userID'];
$controller = new StudentClassReplacementController();
$missedClasses = $controller->getStudentMissedClass($studentID);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Missed Classes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style2.css">
    <style>
        
        
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            height: 100vh;
            position: fixed;
            background-color: #343a40;
            color: white;
        }
        .sidebar h4 {         
            padding: 15px;
            margin: 0;
            text-align: center;
        }
        .sidebar .nav-link {
            color: white;
        }
        .sidebar .nav-link:hover {
            background-color: #495057;
        }
        .main-content {
            margin-left: 260px;
            padding: 20px;
        }
        .table-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .table {
            border-radius: 8px;
            overflow: hidden;
            background-color: #ffffff;
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        .table thead {
            background-color: #007bff;
            color: white;
        }
        .badge {
            font-size: 14px;
            padding: 6px 10px;
        }
        .filter-section {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-none d-md-block sidebar">
                <div class="position-sticky">
                    <a href="studentDashboard.php" target="_blank" style="text-decoration: none; color: white;">
                        <h4 class="text-center py-4">Student Dashboard</h4>
                    </a>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link active" href="StudentAttendanceManagement.php">Student Attendance Management</a></li>
                        <li class="nav-item"><a class="nav-link" href="studentProfileManagement.php">Student Profile Management</a></li>
                        <li class="nav-item"><a class="nav-link" href="LeaveApplicationManagement.php">Student Leave Application</a></li>
                        <li class="nav-item"><a class="nav-link" href="StudentNotifications.php">Notifications</a></li>
                        <li class="nav-item"><a class="nav-link" href="ReportGeneration.php">Report Generation</a></li>
                        <li class="nav-item"><a class="nav-link text-danger" href="../logout.php">Logout</a></li>
                    </ul>
                </div>
            </nav>

            <div class="col-md-10 main-content">
                <h2>Missed Classes During Approved Leave</h2>

                <div class="filter-section">
                    <label><strong>Filter by Date:</strong></label>
                    <input type="date" id="dateFilter" class="form-control w-auto">
                    <button class="btn btn-primary" id="sortButton">Sort Asc/Desc</button>
                </div>

                <div class="table-container">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Leave ID</th>
                                <th>Class ID</th>
                                <th>Subject</th>
                                <th>Class Date</th>
                                <th>Day</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="missedClassesTable">
                            <?php if (!empty($missedClasses) && is_array($missedClasses)): ?>
                                <?php foreach ($missedClasses as $class): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($class['leaveID']) ?></td>
                                        <td><?= htmlspecialchars($class['classID']) ?></td>
                                        <td><?= htmlspecialchars($class['subject']) ?></td>
                                        <td><?= date('Y-m-d', strtotime($class['date'])) ?></td>
                                        <td><?= htmlspecialchars($class['day']) ?></td>
                                        <td><?= date('H:i', strtotime($class['startTime'])) ?></td>
                                        <td><?= date('H:i', strtotime($class['endTime'])) ?></td>  
                                         <td>
                                            <button class="btn btn-primary btn-sm check-replacement" 
                                            data-studentid="<?= $studentID ?>" 
                                            data-classid="<?= $class['classID'] ?>" 
                                            data-subject="<?= htmlspecialchars($class['subject']) ?>"
                                            data-leaveid="<?= $class['leaveID'] ?>">
                                            Check Replacement Class
                                            </button> 
                                            <div class="replacement-container"></div>
                                        </td> 
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No replacement classes found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>                       
                    </table>                 
                </div>
            </div>
        </div>
    </div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".check-replacement").forEach(button => {
        button.addEventListener("click", function () {
            let studentID = this.dataset.studentid;
            let classID = this.dataset.classid;
            let subject = this.dataset.subject;
            let leaveID = this.dataset.leaveid;
            let replacementContainer = this.nextElementSibling;

            // Check if this is a "Close" button, and toggle accordingly
            if (this.textContent === "Close") {
                replacementContainer.innerHTML = "";  // Clear the form
                this.textContent = "Check Replacement Class";  // Reset button text
                return;  // Exit if it's already a "Close" button
            }

            // If not "Close", proceed to fetch and display replacement options
            replacementContainer.innerHTML = "<p>Loading replacement options...</p>";

            fetch("../Controller/StudentClassReplacementController.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({ action: "ReplacementClassSelection", studentID, classID, subject, leaveID })
            })
            .then(response => response.json())
            .then(data => {
                // If no replacement classes found, display a message
                if (data.length === 0) {
                    replacementContainer.innerHTML = "<p class='text-muted'>No available replacements.</p>";
                    return;
                }

                // Generate the HTML for replacement class dropdown first
                let table = `<div class="replacement-options">
    <div class="form-group">
        <label for="replacement-class-${classID}" style="display: block; margin-bottom: 5px; text-align: left;">Select Replacement Class:</label>
        <select class="form-control" id="replacement-class-${classID}">
            <option value="">Select Replacement Class</option>`;

                // Loop through available replacement classes and create options
                data.forEach(classItem => {
                    table += `<option value="${classItem.classID}">${classItem.subject} - ${classItem.day} ${classItem.startTime} - ${classItem.endTime}</option>`;
                });

                table += `
        </select>
    </div>
    
    <div class="form-group">
        <label for="replacement-date-${classID}" style="display: block; margin-bottom: 5px; text-align: left;">Select Replacement Date:</label>
        <input type="date" class="form-control replacement-date" id="replacement-date-${classID}" disabled>
    </div>
    
    <small class="text-danger replacement-msg" style="display: block; margin-top: 5px;"></small>
    
    <button class="btn btn-primary btn-sm mt-3 request-replacement"
            data-leaveid="${leaveID}"
            data-studentid="${studentID}"
            data-classid="${classID}"
            data-subject="${subject}">
        Request Replacement
    </button>
</div>`;

                replacementContainer.innerHTML = table;

                // Enable date input only when a replacement class is selected
                document.getElementById(`replacement-class-${classID}`).addEventListener('change', function () {
                    let dateInput = document.getElementById(`replacement-date-${classID}`);
                    if (this.value) {
                        dateInput.disabled = false;

                        // Get the selected replacement class and its day
                        let selectedClassID = this.value;
                        let selectedClass = data.find(item => item.classID === selectedClassID);
                        let selectedDay = selectedClass.day;

                        // Calculate the next occurrence of the selected day
                        let nextDate = getNextDateForDay(selectedDay);
                        dateInput.value = formatDate(nextDate);
                    } else {
                        dateInput.disabled = true;
                    }
                });

                // Handle request submission
                document.querySelectorAll(".request-replacement").forEach(reqButton => {
                    reqButton.addEventListener("click", function () {
                        let selectedClass = document.getElementById(`replacement-class-${classID}`).value;
                        let requestDate = document.getElementById(`replacement-date-${classID}`).value;

                        // Validate the form
                        if (!selectedClass) {
                            document.querySelector(".replacement-msg").textContent = "Please select a replacement class.";
                            return;
                        }

                        if (!requestDate) {
                            alert("Please select a valid date.");
                            return;
                        }

                        // Check if the selected date is today or in the future (not in the past)
                        let selectedDate = new Date(requestDate);
                        let currentDate = new Date();
                        if (selectedDate < currentDate.setHours(0, 0, 0, 0)) {
                            document.querySelector(".replacement-msg").textContent = "Selected date cannot be in the past.";
                            return;
                        }

                        let selectedDay = new Date(requestDate).toLocaleString('en-us', { weekday: 'long' });

                        // Check if the selected class day matches the input date
                        let classDay = data.find(item => item.classID === selectedClass).day;

                        if (selectedDay !== classDay) {
                            document.querySelector(".replacement-msg").textContent = "The replacement class day does not match the selected date.";
                            return;
                        }

                        fetch("../Controller/StudentClassReplacementController.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({ 
        action: "InsertReplacementRequest", 
        studentID, 
        classID: selectedClass, 
        requestDate,
        leaveID
    })
})
.then(response => response.text())
.then(data => {
    alert(data); // Show success or error message
    window.location.reload(); // Refresh the page after the request
})
.catch(error => console.error("Error submitting request:", error));
                    });
                });
            })
            .catch(error => console.error("Error fetching replacement classes:", error));

            // Change button to "Close" when replacement form is displayed
            this.textContent = "Close";
        });
    });
});

// Function to calculate the next date for a given day (e.g., next Friday)
function getNextDateForDay(dayName) {
    const daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const today = new Date();
    const dayIndex = daysOfWeek.indexOf(dayName);
    const currentDayIndex = today.getDay();
    let daysToAdd = dayIndex - currentDayIndex;

    if (daysToAdd <= 0) {
        daysToAdd += 7; // If the day has already passed this week, calculate for next week
    }

    today.setDate(today.getDate() + daysToAdd);
    return today;
}

// Function to format date as yyyy-mm-dd
function formatDate(date) {
    const year = date.getFullYear();
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const day = date.getDate().toString().padStart(2, '0');
    return `${year}-${month}-${day}`;
}
</script>


<script>
document.addEventListener("DOMContentLoaded", function () {
    // Filter by Date functionality
    document.getElementById('dateFilter').addEventListener('input', function() {
        let filterDate = this.value;  // Get the selected date
        let rows = document.querySelectorAll("#missedClassesTable tr");  // All rows in the table

        rows.forEach(row => {
            let classDate = row.cells[3].textContent.trim();  // Get the date from the table (class date is in the 4th column)

            // Show rows where the class date matches the filter or hide otherwise
            if (!filterDate || filterDate === classDate) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });

    // Sort functionality for class date
    document.getElementById('sortButton').addEventListener('click', function() {
        let table = document.getElementById("missedClassesTable");
        let rows = Array.from(table.rows);  // Convert rows to an array
        let ascending = this.dataset.order !== "desc";  // Toggle sorting order

        // Show all rows before sorting
        rows.forEach(row => row.style.display = "");

        // Sort rows by class date
        rows.sort((a, b) => {
            let dateA = new Date(a.cells[3].textContent.trim());  // Get the date from the table (class date is in the 4th column)
            let dateB = new Date(b.cells[3].textContent.trim());

            return ascending ? dateA - dateB : dateB - dateA;  // Ascending or Descending
        });

        // Update button to toggle order
        this.dataset.order = ascending ? "desc" : "asc";
        
        // Clear the table and append sorted rows
        table.innerHTML = "";  // Clear the current table content
        table.append(...rows);  // Append sorted rows
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
