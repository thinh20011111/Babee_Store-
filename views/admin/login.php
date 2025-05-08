<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 50px auto; }
        .error { color: red; }
        form { display: flex; flex-direction: column; gap: 10px; }
        label { font-weight: bold; }
        input[type="text"], input[type="password"] { padding: 8px; }
        input[type="submit"] { padding: 10px; background: #007bff; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Admin Login</h1>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
        <p>
            <label>Username:</label>
            <input type="text" name="username" value="admin@gmail.com" required>
        </p>
        <p>
            <label>Password:</label>
            <input type="password" name="password" required>
        </p>
        <p>
            <input type="submit" value="Login">
        </p>
    </form>
</body>
</html>