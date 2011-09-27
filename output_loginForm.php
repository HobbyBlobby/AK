<?php
        $output = '
        <div style="margin:100px 25%;width:50%;background:lightgray;text-align:center;">
            <form action="./index.php" method="post">
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
        </div>';
    echo $output;
?>