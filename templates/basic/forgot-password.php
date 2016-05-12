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

<fieldset>
    <label>Your email</label>
    <input id="public-user-email" name="public-user-email" type="text" />
</fieldset>

<?
if (isset($_GET["code"]) && $_GET["code"] == "true") {
?>
<section id="code-section">
<?
}
else {
?>
<section id="code-section" style="display: none;">
<?
}
?>
    <fieldset id="enter-code-section">
        <label>The code that was emailed to you</label>
        <input id="enter-code" type="text" />
    </fieldset>

    <fieldset>
        <label>Your new password</label>
        <input id="new-password" type="password" />
    </fieldset>
</section>

<fieldset>
    <input id="public-user-forgot-password" type="button" value="<?= $submitButtonLabel ?>" />&nbsp;&nbsp;<a href="#" id="showCodeSection">Already have a code?</a>
</fieldset>

<ul class="public-user-forgot-password-messages">
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
                publicUserForgotPasswordInitialize();
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
        publicUserForgotPasswordInitialize();
    }
}

function addMessage(message) {
    $('.public-user-forgot-password-messages').append('<li>' + message + '</li>');
}

function clearMessages() {
    $('.public-user-forgot-password-messages').html('');
}

function sendRequest() {
    if (!formValidated()) {
        return;
    }

    $.ajax({
        url: wwwRoot + '*/com.mindscapesolutions.public-user/ajax/public-user/request-password',
        type: 'POST',
        data: {
            e: $('#public-user-email').val(),
            c: $('#enter-code').val(),
            p: $('#new-password').val()
        },
        success: function(result) {
            if (result.trim() == 'ok') {
                addMessage('Your reset password instructions have been sent to the email you provided.');
            }
            else {
                addMessage(result);
            }
        },
        error: function(result) {
            addMessage('There was an internal error while attempting to send the request.');
        }
    });
}

function formValidated() {
    var isVerified = true;

    clearMessages();

    if ($.trim($('#public-user-email').val()).length == 0) {
        addMessage('Your email is required');
        isVerified = false;
    }

    if ($('#code-section').css('display') != "none") {
        if ($.trim($('#enter-code').val()).length > 0 && $.trim($('#new-password').val()).length == 0 ||
            $.trim($('#new-password').val()).length > 0 && $.trim($('#enter-code').val()).length == 0) {
        }
    }
    else {
        $('#enter-code').val('');
        $('#new-password').val('');
    }

    return isVerified;
}

function publicUserForgotPasswordInitialize() {
    $('#showCodeSection').click(function() {
        $('#code-section').show();
    });

    $('#public-user-forgot-password').click(function() {
        sendRequest();
    });

    $(document).keypress(function(e) {
        if (e.which == 13) {
            sendRequest();
        }
    });
}

</script>

