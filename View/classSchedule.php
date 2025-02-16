<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Teacher') {
    header("Location: ../login.php?error=Unauthorized access.");
    exit();
}

require_once __DIR__ . '/../Controller/ClassScheduleController.php';

$teacherID = $_SESSION['user']['userID'];
$controller = new ClassScheduleController();
$schedule = $controller->getSchedule($teacherID);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Class Schedule</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
                background-color: #212529;
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
            .active-link {
                background-color: #007bff !important;
                color: white !important;
            }
            .main-content {
                margin-left: 260px;
                padding: 20px;
            }
            .card {
                transition: 0.3s;
            }
            .card:hover {
                transform: scale(1.05);
            }
        </style>
    </head>
    <body>
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar Section -->
                <nav class="col-md-2 d-none d-md-block sidebar">
                    <div class="position-sticky">
                        <h4>Teacher Class Schedule</h4>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link" href="teacherDashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="attendance.php">Attendance Management</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active-link" href="classSchedule.php">Class Schedule</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="classReplacement.php">Class Replacement Requests</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="reward.php">Reward Management</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="notifications.php">Automated Notifications</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-danger" href="../logout.php">Logout</a>
                            </li>
                        </ul>
                    </div>
                </nav>

                <!-- Main Content Section -->
                <main class="col-md-9 ms-sm-auto col-lg-10 px-4 main-content">
                    <div class="container">    
                        <h2 class="mt-4">Class Schedule</h2>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Day</th>
                                    <th>Time</th>
                                    <th>Location</th>
                                    <th>Capacity</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedule as $class): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($class["subject"]); ?></td>
                                        <td><?= htmlspecialchars($class["day"]); ?></td>
                                        <td><?= htmlspecialchars($class["startTime"] . " - " . $class["endTime"]); ?></td>
                                        <td><?= htmlspecialchars($class["location"]); ?></td>
                                        <td><?= htmlspecialchars($class["capacity"]); ?></td>
                                        <td>
                                            <button class="btn btn-primary" onclick="viewStudents('<?= $class['classID']; ?>')">View Students</button>
                                            <button class="btn btn-success" onclick="enrollStudent('<?= $class['classID']; ?>')">Enroll Student</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- Student List Section -->
                        <div id="studentListContainer" class="mt-4" style="display: none;">
                            <h4>Enrolled Students</h4>
                            <ul id="studentList" class="list-group"></ul>
                        </div>

                        <!-- Enrollment Modal -->
                        <div class="modal fade" id="enrollModal" tabindex="-1" aria-labelledby="enrollModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="enrollModalLabel">Enroll Student</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="enrollForm">
                                            <input type="hidden" id="classID">
                                            <label for="studentID">Select Student:</label>
                                            <select id="studentID" class="form-control" required></select>
                                            <div class="mt-3 text-center">
                                                <button type="submit" class="btn btn-primary">Enroll</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </main>
            </div>
        </div>

        <script>
            function viewStudents(classID) {
                fetch(`../Controller/ClassScheduleController.php?action=viewStudents&classID=${classID}`)
                        .then(response => response.json())
                        .then(data => {
                            const studentList = document.getElementById("studentList");
                            studentList.innerHTML = "";
                            document.getElementById("studentListContainer").style.display = "block";

                            let count = 1;
                            if (data.length > 0) {
                                data.forEach(student => {
                                    let li = document.createElement("li");
                                    li.classList.add("list-group-item", "d-flex", "justify-content-between", "align-items-center");
                                    li.innerHTML = `${count}. ${student.studentID} - ${student.fullName} 
                            <button class="btn btn-danger btn-sm" onclick="removeStudent('${student.studentID}', '${classID}')">Remove</button>`;
                                    studentList.appendChild(li);
                                    count++;
                                });
                            } else {
                                studentList.innerHTML = "<li class='list-group-item'>No students enrolled.</li>";
                            }
                        })
                        .catch(error => {
                            alert("Error fetching students: " + error);
                            console.error(error);
                        });
            }

            function removeStudent(studentID, classID) {
                if (confirm("Are you sure you want to remove this student from the class?")) {
                    fetch("../Controller/ClassScheduleController.php", {
                        method: "POST",
                        headers: {"Content-Type": "application/x-www-form-urlencoded"},
                        body: `action=removeStudent&studentID=${studentID}&classID=${classID}`
                    })
                            .then(response => response.json())
                            .then(data => {
                                alert(data.message);
                                if (data.status === "success") {
                                    viewStudents(classID);
                                    updateCapacity(classID);
                                }
                            })
                            .catch(error => {
                                alert("Error removing student: " + error);
                                console.error(error);
                            });
                }
            }

            function updateCapacity(classID) {
                fetch(`../Controller/ClassScheduleController.php?action=getClassCapacity&classID=${classID}`)
                        .then(response => response.json())
                        .then(data => {
                            let capacityElement = document.getElementById(`capacity-${classID}`);
                            if (capacityElement) {
                                capacityElement.innerText = data.capacity;  // ✅ Update only if element exists
                            } else {
                                console.warn("Capacity element not found for classID:", classID);
                            }
                        })
                        .catch(error => {
                            alert("Error updating capacity: " + error);
                            console.error(error);
                        });
            }


            function enrollStudent(classID) {
                fetch(`../Controller/ClassScheduleController.php?action=getStudents`)
                        .then(response => response.json())
                        .then(data => {
                            let studentDropdown = document.getElementById("studentID");
                            studentDropdown.innerHTML = "";

                            data.forEach(student => {
                                let option = document.createElement("option");
                                option.value = student.studentID;
                                option.textContent = `${student.studentID} - ${student.fullName}`;
                                studentDropdown.appendChild(option);
                            });

                            document.getElementById("classID").value = classID;
                            new bootstrap.Modal(document.getElementById("enrollModal")).show();
                        })
                        .catch(error => {
                            alert("Error loading student list: " + error);
                            console.error(error);
                        });
            }

            document.getElementById("enrollForm").addEventListener("submit", function (event) {
                event.preventDefault();
                let studentID = document.getElementById("studentID").value;
                let classID = document.getElementById("classID").value;

                fetch("../Controller/ClassScheduleController.php", {
                    method: "POST",
                    headers: {"Content-Type": "application/x-www-form-urlencoded"},
                    body: `action=enroll&studentID=${studentID}&classID=${classID}`
                })
                        .then(response => response.json())
                        .then(data => {
                            alert(data.message);
                            if (data.status === "success") {
                                location.reload();
                            }
                        })
                        .catch(error => {
                            alert("Error enrolling student: " + error);
                            console.error(error);
                        });
            });
        </script>

    </body>
</html>
