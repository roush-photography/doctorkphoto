<?php
define('CONTACT', 'danny@doctorkphoto.com');

function VerifyContact(&$cleansed, &$errors) {
    if (strlen($cleansed['name']) == 0)
        $errors['name'] = 'Please provide your name.';
    if (strlen($cleansed['company']) == 0)
        $errors['company'] = 'Please provide a company name.';
    if (!preg_match('/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/', $cleansed['email']))
        $errors['email'] = 'Please provide a valid email address.';
    if (strlen($cleansed['review']) == 0)
        $errors['review'] = 'Please include a review';

    return (count($errors) == 0);
}

function DisplayContact($cleansed, $errors) {
?>
            <h1>Submit a Review</h1>
                <p>Please fill out the form below.</p>
                <div class="contactForm">
                    <form name="contact" method="post" action="<?php htmlentities($_SERVER['PHP_SELF']); ?>">
<?php
    if (count($errors) > 0) {
        echo '<p class="errortext" style="text-align:left;">
            There are problems with your submission.  Please correct them and try again.</p>';
    }
    if ($errors['name']) { echo '<p class="errortext">'.$errors['name'].'</p>'; }
?>
                                <p>Name:<br />
                                <input type="text" name="name"
                                    value="<?php echo (isset($cleansed['name']) ? $cleansed['name'] : ''); ?>" /></p>

<?php echo (isset($errors['company']) ? '<p class="errortext">' . $errors['company'] . '</p>' : ''); ?>
                                <p>Company<br />
                                <input type="text" name="company"
                                    value="<?php echo (isset($cleansed['company']) ? $cleansed['company'] : ''); ?>" /></p>

<?php echo (isset($errors['email']) ? '<p class="errortext">'.$errors['email'].'</p>' : ''); ?>
                                <p>Email:<br />
                                <input type="email" name="email"
                                    value="<?php echo (isset($cleansed['email']) ? $cleansed['email'] : ''); ?>" /></p>

                            <p>Review:</p>
                                <textarea rows="5" cols="60" name="review"><?php echo (isset($cleansed['review']) ? $cleansed['review'] : ''); ?></textarea>

                            <div class="submit">
                                <input type="hidden" name="submitted" value="contact" />
                                <input type="submit" name="submit" value="Send" />
                                <input type="reset" name="reset" value="Reset" />
                            </div><!-- .submit -->
                    </form><!-- mail -->
                </div><!-- .contactForm -->
            </div><!-- .content -->

<?php
}

function ProcessContact($cleansed) {
    $name = $cleansed['name'];
    $company = $cleansed['company'];
    $email = $cleansed['email'];
    $review = $cleansed['review'];

    // Let's compose an email for Jeff
    $to = CONTACT;
    $subject = 'Email from ' . $name . ' <' . $email . '>';
    $header = 'From: Doctor K Photo Review <' . $email . '>';
    $message = 'Name: ' . $name . "\r\n" .
        'Date: ' . Date('l F d, Y') . "\r\n" .
        'Company: ' . $company . "\r\n" .
        'Email: ' . $email . "\r\n" .
        'Review: ' . "\r\n" .
        $review . '' . "\r\n";

    // Send that sumbitch
    $sentmail = mail($to, $subject, $message, $header);

    if ($sentmail) {
        echo '<h1>Message Sent!</h1>
            <p>Thank you for your review.</p>';
        $mailsent = TRUE;
    } else {
        $errors['mail'] = '<h1>Whoa!</h1>
            <p>Your email was not sent. Please ensure all fields are filled out correctly.</p>';
        $mailsent = FALSE;
    }
}

// Now to point out errors
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    function cleanse($value) {
        // find evil
        $evil = array('to:', 'cc:', 'bcc:', 'content-type:', 'mime-version:', 'multipart-mixed:', 'content-transfer-encoding:');
        // purge evil, return warning
        foreach ($evil as $v) {
            if (stripos($value, $v) !== false) return '!!!';
        }
        // strip newlines
        $value = str_replace(array( "\r", "\n", "%0a", "%0d"), ' ', $value);
        // return purity
        return trim($value);
    }
    // rinse, no repeat
    $cleansed = array_map('cleanse', $_POST);
    $formValues = $_POST;
    $formErrors = array();

    // And add them in the form
    if (!VerifyContact($cleansed, $formErrors))
        DisplayContact($cleansed, $formErrors);

    // Send 'er on home Jack, she's done
    else
        ProcessContact($cleansed);

} else
    DisplayContact(null, null);
