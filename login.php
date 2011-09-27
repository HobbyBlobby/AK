<?php
    session_start();
    include("./account.php");
    if((isset($_REQUEST['passphrase']) && $_REQUEST['passphrase'] == $_PASSPHRASE) && (isset($_REQUEST['username']) && $_REQUEST['username'] == $_USERNAME)) {
        $_SESSION["username"] = $_USERNAME;
    }
    if(isset($_SESSION["username"])) {
        header("Location: ./index.php");
    }
?>

<HEAD>

</HEAD>

<BODY>
<div style="margin:100px 40%;width:20%;background:lightgray;text-align:center;">
    <form action="login.php" method="post">
        <table>
            <tr>
                <td>Nutzername:</td><td><input type="text" name="username" /></td>
            </tr><tr>
                <td>Passwort:</td><td><input type="password" name="passphrase" /></td>
            </tr><tr>
                <td></td><td><input type="submit" name="anmelden" value="Anmelden" /></td>
            </tr>
        </table>
    </form>
</div>
</BODY>