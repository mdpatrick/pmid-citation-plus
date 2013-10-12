<div class="wrap" style="margin-bottom:0;" xmlns="http://www.w3.org/1999/html">
    <h2>PMID Citation Plus Settings</h2>

    <form method="post" action="options.php">
        <?php settings_fields('pmidplus_options'); // adds nonce ?>
        <table class="pmidplus-options-table">
            <tr>
                <td>
                    Display abstract tooltip when hovering bibliographic entry
                </td>
                <td>
                    <label for="abstract_tooltip_on">on</label>
                    <input id="abstract_tooltip_on" name="pmidplus_options[abstract_tooltip]" type="radio"
                           value="true" <?php echo $pmidplus_options['abstract_tooltip'] ? 'checked="true"' : ''; ?> />
                </td>
                <td>
                    <label for="abstract_tooltip_off">off</label>
                    <input id="abstract_tooltip_off" name="pmidplus_options[abstract_tooltip]" type="radio"
                           value="false" <?php echo !$pmidplus_options['abstract_tooltip'] ? 'checked="true"' : ''; ?> />
                </td>
            </tr>
            <tr>
                <td>
                    <label for="abstract_tooltip_length">Length of abstract in tooltip (in characters)</label>
                </td>
                <td>
                    <input id="abstract_tooltip_length" name="pmidplus_options[abstract_tooltip_length]" type="number"
                           value="<?php echo $pmidplus_options['abstract_tooltip_length']; ?>" />
                </td>
                <td>
                    &nbsp;
                </td>
            </tr>
            <tr>
                <td>
                    Append "Open with Read" link to bibliographical entries <small><em>(<a href="https://www.readbyqxmd.com/about">what's this?</a>)</em></small>
                </td>
                <td>
                    <label for="open_with_read_on">on</label>
                    <input id="open_with_read_on" name="pmidplus_options[open_with_read]" type="radio"
                           value="true" <?php echo $pmidplus_options['open_with_read'] ? 'checked="true"' : ''; ?> />
                </td>
                <td>
                    <label for="open_with_read_off">off</label>
                    <input id="open_with_read_off" name="pmidplus_options[open_with_read]" type="radio"
                           value="false" <?php echo !$pmidplus_options['open_with_read'] ? 'checked="true"' : ''; ?>/>
                </td>
            </tr>
            <tr>
                <td>
                    Add target=&quot;_blank&quot; to links in bibliography
                </td>
                <td>
                    <label for="targetblank_on">on</label>
                    <input id="targetblank_on" name="pmidplus_options[targetblank]" type="radio"
                           value="true" <?php echo $pmidplus_options['targetblank'] ? 'checked="true"' : ''; ?>" />
                </td>
                <td>
                    <label for="targetblank_off">off</label>
                    <input id="targetblank_off" name="pmidplus_options[targetblank]" type="radio"
                           value="false" <?php echo !$pmidplus_options['targetblank'] ? 'checked="true"' : ''; ?>"/>
                </td>
            </tr>
        </table>
        <p class="submit"><input type="Submit" name="submit" class="button" value="<?php esc_attr_e('Save Changes'); ?>"/></p>
    </form>
    <?php
    // Sends email, and prints response if contact form is used.
    if (preg_match('/[A-Za-z]{4,15}/', $_POST['comments'])) {
        $admin_email = get_option('admin_email');
        wp_mail(
            'dan@mdpatrick.com', //to
            "{$_POST['subject']} - PMID Citation Plus", //subject
            "{$_POST['comments']}", //body
            "From: $admin_email <$admin_email>" . " \r\n" //header
        );
        echo "<h2>Email sent!</h2><br />";
        readfile(plugins_url('pmidplus-contact.php', __FILE__));
    } else {
        // Prints contact form, newsletter, donate button, etc.
        readfile(plugins_url('pmidplus-contact.php', __FILE__));
    }
    ?></div></div>
