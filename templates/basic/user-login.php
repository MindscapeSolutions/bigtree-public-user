<?
	/*
		Resources Available:
		$headline = Headline - Text
		$subheader = Subheader - Text
        $loginButtonLabel = Login Button Label - Text
	*/

?>

<?
if (!empty($headline)) {
?>
<h1><?= $headline ?></h1>
<?
}
?>

<?
if (!empty($subheader)) {
?>
<p><?= $subheader ?></p>
<?
}
?>

<?
$redirectPage = BigTreeAdmin::getSetting('com.mindscapesolutions.public-user*public-user-home-page')['value'];
?>

<input id="public-user-redirect" type="hidden" value="<?= $redirectPage ?>" />

<fieldset>
    <label>Email</label>
    <input id="public-user-email" name="public-user-email" type="text" />
</fieldset>

<fieldset>
    <label>Password</label>
    <input id="public-user-password" name="public-user-password" type="password" />
</fieldset>

<fieldset>
    <input id="public-user-login" type="button" value="<?= $loginButtonLabel ?>" />
</fieldset>

<ul class="public-user-login-messages">
</ul>

<script type="text/javascript">

wwwRoot = '<?= WWW_ROOT ?>';

window.onload = function() {
    if (!window.jQuery) {
        var head = document.getElementsByTagName('head')[0];

        var jQueryScript = document.createElement('script');
        jQueryScript.type = 'text/javascript';
        jQueryScript.src = '//code.jquery.com/jquery-2.1.4.min.js';
        head.appendChild(jQueryScript);

        var checkReady = function() {
            if (window.jQuery) {
                publicUserLoginInitialize();
            }
            else {
                window.setTimeout(function() {
                    checkReady();
                }, 100);
            }
        };

        checkReady();
    }
    else {
        publicUserLoginInitialize();
    }
}

function addMessage(message) {
    $('.public-user-login-messages').append('<li>' + message + '</li>');
}

function clearMessages() {
    $('.public-user-login-messages').html('');
}

function login() {
    if (!loginValidated()) {
        return;
    }

    $.ajax({
        url: wwwRoot + '*/com.mindscapesolutions.public-user/ajax/public-user/login',
        type: 'POST',
        data: {
            e: $('#public-user-email').val(),
            p: $('#public-user-password').val()
        },
        success: function(result) {
            if (result.trim() == 'ok') {
                addMessage('Login successful');

                if ($('#public-user-redirect').val().length > 0) {
                    window.location = $('#public-user-redirect').val();
                }
                else {
                    window.location = wwwRoot;
                }
            }
            else {
                addMessage(result);
            }
        },
        error: function(result) {
            addMessage('There was an internal error while attempting to log you in.');
        }
    });
}

function loginValidated() {
    var isVerified = true;

    clearMessages();

    if ($.trim($('#public-user-email').val()).length == 0) {
        addMessage('Your email is required');
        isVerified = false;
    }

    if ($.trim($('#public-user-password').val()).length == 0) {
        addMessage('Your password is required');
        isVerified = false;
    }

    return isVerified;
}

function publicUserLoginInitialize() {
    $('#public-user-login').click(function() {
        login();
    });

    $(document).keypress(function(e) {
        if (e.which == 13) {
            login();
        }
    });
}

</script>

