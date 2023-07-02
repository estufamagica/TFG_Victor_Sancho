<!doctype html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../style/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
</head>
<body>
<div class="center-allign margintop10">
    <h1>Login</h1>
    <div class="margintop3">
        <form method="post" action="?action=login">
            <div>
                <div class="dvLabelContainer">
                    <label class="label-login" for="email">Email</label>
                </div>
                <input class="input-login" type="email" id="email" name="email"/>

            </div>
            <div>
                <div class="dvLabelContainer">
                    <label class="label-login" for="password">Password</label>
                </div>
                <input class="input-login" type="password" id="password" name="password"/>

            </div>
            <button id="btnLogin" class="pointer" type="submit" name="submit"><img src="../Resources/img/sign-in.png" style="width: 35px;"></button>
        </form>

        <div>
            <?php echo @$login?>
        </div>
    </div>
</div>
</body>
</html>


