<?
	class PublicUser extends BigTreeModule {
		var $Table = "public_users";

        /**
         * Generate a password reset code for a user
         *
         * @param string $email
         *
         * @return string or false
         */
        public static function generateResetCode($email) {
            $code = base64_encode(date("U"));
            $result = sqlquery("update public_users set password_reset = '$code'");

            if (!$result) {
                return false;
            }
            else {
                return $code;
            }
        }

        /**
         * Attempt to log in a user
         *
         * @param array $creds
         *  'email' =>
         *  'password' =>
         *
         * @return string 'ok' on success else error message
         */
        public static function login($creds) {

            if (!is_array($creds)) {
                return 'Invalid data passed to login function.';
            }

            if (!array_key_exists('email', $creds) || !array_key_exists('password', $creds)) {
                return 'Both an email and a password must be provided to login.';
            }

            if (empty($creds['email']) || empty($creds['password'])) {
                return 'Both an email and a password must be provided to login.';
            }

            $email = trim($creds['email']);

            $user = new PublicUser();
            $user = $user->getMatching('email', $creds['email']);

            if (count($user) == 0) {
                return 'Your credentials were not found in the system. Please try again or request assistance.';
            }

            $user = $user[0];

			$phpass = new PasswordHash($bigtree["config"]["password_depth"], TRUE);
            if (!$phpass->CheckPassword($creds['password'], $user['password'])) {
                return 'Your credentials were not found in the system. Please try again or request assistance.';
            }

            $isAuthorized = $user['deactivated'] != 'on';

            if (!$isAuthorized) {
                return 'Your account has been disabled. Contact your administrator for further assistance.';
            }

            $_SESSION['public-user-id'] = $user['id'];

            return 'ok';

        }

        /**
         * Attempt to send a user a password reset link or process their reset code
         *
         * @param array $creds
         *  'email' =>
         *  'code' =>
         *  'password' =>
         *
         * @return string 'ok' on success else error message
         */
        public static function requestPassword($creds) {
            if (!is_array($creds)) {
                return 'Invalid data passed to request password function.';
            }

            if (!empty($creds["code"]) && empty($creds["password"]) || 
                !empty($creds["password"]) && empty($creds["code"])) {
                return "Both a code and new password must be provided to reset your password.";
            }

            $email = trim($creds["email"]);

            $user = new PublicUser();
            $user = $user->getMatching("email", $creds["email"]);

            if (count($user) == 0) {
                return "Your credentials were not found in the system. Please try again or request assistance.";
            }

            $user = $user[0];

            $isAuthorized = $user["deactivated"] != "on";

            if (!$isAuthorized) {
                return "Your account has been disabled. Contact your administrator for further assistance.";
            }

            $emailDomain = str_replace(array("https://www.", "http://www.", "https://", "http://"), "", WWW_ROOT);
            $emailHeaders = "MIME-Version: 1.0" . "\r\n";
            $emailHeaders .= "Content-type: text/html; charset-iso-8859-1" . "\r\n";
            $emailHeaders .= "From: no-reply@$emailDomain\r\n";

            if (empty($creds["code"])) {
                // since code is empty, this is a request for a reset link

                $resetCode = self::generateResetCode($creds["email"]);
                if (!$resetCode) {
                    return "There was an internal error processing your request. Contact your administrator for further assistance.";
                }

                $emailSubject = "Password Reset Request from " . strtoupper($emailDomain);

                $emailBody = "";
                $emailBody .= "<p>A new password was requested for " . $creds["email"] . " at " . strtoupper($emailDomain) . ". If you did not request a new password, you may disregard this email.</p>";
                $emailBody .= "<p>Your reset code is: </p>";
                $emailBody .= "<p>" . $resetCode . "</p>";
                $emailBody .= "<p>Enter this code at <a href=\"" . BigTreeAdmin::getSetting("com.mindscapesolutions.public-user*public-user-forgot-password-page")["value"] . "?code=true\">" . BigTreeAdmin::getSetting("com.mindscapesolutions.public-user*public-user-forgot-password-page")["value"] . "?code=true</a></p>";

                mail($creds['email'], $emailSubject, $emailBody, $emailHeaders);

                return "The instructions to reset your password have been sent to the email address you provided.";
            }
            else {
                // the user has sent their code

                // make sure the code matches
                $result = sqlquery("select * from public_users where email='" . $creds["email"] . "' and password_reset='" . $creds["code"] . "'");

                $total = 0;
                while ($f = sqlfetch($result)) {
                    $total++;
                }

                if ($total == 0) {
                    return "You have entered an invalid code, however you may send a request for a new code.";
                }

			    $phpass = new PasswordHash($bigtree["config"]["password_depth"], TRUE);
                $hashed = sqlescape($phpass->HashPassword($creds["password"]));

                $result = sqlquery("update public_users set password = '" . $hashed . "' where email = '" . $creds["email"] . "'");

                if (!$result) {
                    return "There was an internal error resetting your password. Please try again.";
                }
                else {
                    return "Your password has been reset. You may now go to the <a href=\"" . BigTreeAdmin::getSetting("com.mindscapesolutions.public-user*public-user-login-page")["value"] . "\">login screen</a>.";
                }
            }
        }
	}

?>
