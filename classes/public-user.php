<?
	class PublicUser extends BigTreeModule {
		var $Table = "public_users";

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

	}

?>
