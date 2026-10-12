<?php

$action = $_POST["action"] ?? "";
$message = "";
$students = [];
$error = "";

if ($action === "connect" || $action === "display") {
    try {
        require_once __DIR__ . "/database.php";
        $message = "Connected to the database successfully.";

        if ($action === "display") {
            $columnsByTable = [];
            $columnQuery = $pdo->query(
                "SELECT TABLE_NAME, COLUMN_NAME
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                 ORDER BY TABLE_NAME, ORDINAL_POSITION"
            );

            foreach ($columnQuery->fetchAll() as $column) {
                $tableName = $column["TABLE_NAME"];
                $columnName = $column["COLUMN_NAME"];
                $normalizedName = strtolower(str_replace(" ", "_", $columnName));
                $columnsByTable[$tableName][$normalizedName] = $columnName;
            }

            $requiredColumns = [
                "id",
                "email",
                "course",
                "created_at",
                "first_name",
                "last_name"
            ];
            $matchingTables = [];

            foreach ($columnsByTable as $tableName => $columns) {
                if (count(array_diff($requiredColumns, array_keys($columns))) === 0) {
                    $matchingTables[$tableName] = $columns;
                }
            }

            if (count($matchingTables) === 0) {
                $error = "No table was found with the required columns: id, email, course, created_at, first_name, and last name (or last_name).";
            } elseif (count($matchingTables) > 1) {
                $error = "More than one table has these columns. Keep only the relevant table in this database or specify its name in index.php.";
            } else {
                $tableName = array_key_first($matchingTables);
                $columns = $matchingTables[$tableName];
                $quotedTable = "`" . str_replace("`", "``", $tableName) . "`";
                $selectColumns = [];

                foreach ($requiredColumns as $columnName) {
                    $quotedColumn = "`" . str_replace("`", "``", $columns[$columnName]) . "`";
                    $selectColumns[] = $quotedColumn . " AS `" . $columnName . "`";
                }

                $sql = "SELECT " . implode(", ", $selectColumns) . " FROM " . $quotedTable;
                $students = $pdo->query($sql)->fetchAll();
            }
        }
    } catch (PDOException $exception) {
        error_log($exception->getMessage());
        $error = "Could not load the data. Check the database connection and table/column names.";
    }
}

function escapeHtml($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Records</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 3rem;
        }

        button {
            cursor: pointer;
            margin-right: 0.7rem;
            padding: 0.6rem 1rem;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            border-collapse: collapse;
            margin-top: 1rem;
            width: 120%;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 0.9rem;
            text-align: left;
        }

        th {
            background: #ADD8E6;
        }
    </style>
</head>
<body>
    <h1>Student Records</h1>

    <form method="post">
        <button type="submit" name="action" value="connect">Connect to Database</button>
        <button type="submit" name="action" value="display">Display Data</button>
    </form>

    <?php if ($message !== ""): ?>
        <p><?= escapeHtml($message) ?></p>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <p role="alert"><?= escapeHtml($error) ?></p>
    <?php endif; ?>

    <?php if ($action === "display" && $error === ""): ?>
        <?php if (count($students) === 0): ?>
            <p>No records found.</p>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Created At</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= escapeHtml($student["id"]) ?></td>
                                <td><?= escapeHtml($student["email"]) ?></td>
                                <td><?= escapeHtml($student["course"]) ?></td>
                                <td><?= escapeHtml($student["created_at"]) ?></td>
                                <td><?= escapeHtml($student["last_name"]) ?></td>
                                <td><?= escapeHtml($student["first_name"]) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
