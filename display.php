
<?php
require_once "connection.php";

try {
    $sql = "SELECT id, first_name, second_name, gpa FROM students";
    $stmt = $conn->query($sql);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Could not retrieve student records.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Results</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 40px;
        }

        table {
            margin: 20px auto;
            border-collapse: collapse;
            width: 80%;
        }

        th, td {
            border: 1px solid #999;
            padding: 12px;
        }

        th {
            background-color: #1677d2;
            color: white;
        }
    </style>
</head>
<body>

<h1>Student Results</h1>

<table>
    <tr>
        <th>ID</th>
        <th>First Name</th>
        <th>Second Name</th>
        <th>GPA</th>
    </tr>

    <?php foreach ($students as $student): ?>
        <tr>
            <td><?= htmlspecialchars((string)$student["id"]) ?></td>
            <td><?= htmlspecialchars($student["first_name"]) ?></td>
            <td><?= htmlspecialchars($student["second_name"]) ?></td>
            <td><?= htmlspecialchars((string)$student["gpa"]) ?></td>
        </tr>
    <?php endforeach; ?>

    <?php if (count($students) === 0): ?>
        <tr>
            <td colspan="4">No students found.</td>
        </tr>
    <?php endif; ?>
</table>

<a href="index.html">Go Back</a>

</body>
</html>
