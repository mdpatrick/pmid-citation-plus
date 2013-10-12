<?php
/*
Plugin Name: PMID Citation Plus
Plugin URI: http://www.mdpatrick.com/2011/pmidcitationplus/
Version: 1.0.7
Author: Dan Patrick
Author URI: http://www.mdpatrick.com/
Description: This plugin makes citing scientific studies in an aesthetically pleasing manner much more easy. It allows you to simply enter in Pubmed IDs and have a references list automatically built for you.
*/

add_action('wp_enqueue_scripts', 'enqueue_pmid_scripts');
add_action('admin_init', 'pmidplus_add_meta');
add_action('save_post', 'pmidplus_save_postdata'); // Execute save function on save.
add_filter('the_content', 'pmidplus_append_bibliography', 9);
add_shortcode('pmidplus', 'shortcode_cite');

// TODO don't use globals. May cause conflicts in WordPress core or other plugins.
global $pmidplus_options;
$pmidplus_options = get_option('pmidplus_options', false);
// Set some defaults options for settings page.
if (!$pmidplus_options or (count($pmidplus_options) < 4)) {
    $pmidplus_options = array(
        'abstract_tooltip' => true,
        'abstract_tooltip_length' => 450,
        'open_with_read' => false,
        'targetblank' => true
    );
}

// Add script necessary to have abstract in tooltip.
function enqueue_pmid_scripts()
{
    wp_enqueue_script('jquery');
    wp_register_script('jquery-tooltip', plugins_url('/js/jquery-tooltip/jquery.tooltip.js', __FILE__));
    wp_enqueue_script('jquery-tooltip');
    wp_register_style('jquery-tooltip', plugins_url('/js/jquery-tooltip/jquery.tooltip.css', __FILE__));
    wp_enqueue_style('jquery-tooltip');
    wp_register_style('pmidplus-style', plugins_url('/css/pmidplus.css', __FILE__));
    wp_enqueue_style('pmidplus-style');
}

// Grabs pubmed page from URL, pulls into string, parses out an array with: title, journal, issue, authors, institution.
function scrape_pmid_abstract($pubmedid)
{
    $ret_array = array();
    try {
        $request = wp_remote_get('http://www.ncbi.nlm.nih.gov/pubmed/' . $pubmedid);
        $pubmedpage = $request['body'];
        //TODO replace try/catch with is_wp_error()
        $ret_array['url'] = 'http://www.ncbi.nlm.nih.gov/pubmed/' . $pubmedid;
        preg_match('/<div class="cit">(?P<journal>.*?)<\/a>(?P<issue>.*?\.).*?<\/div>/', $pubmedpage, $matches);
        $ret_array['journal'] = strip_tags($matches['journal']);
        $ret_array['issue'] = trim($matches['issue']);
        $ret_array['pmid'] = $pubmedid;
        preg_match('/<h1>(?P<title>.+)<\/h1><div class="auths">(?P<authors>.*?)<\/div>/', $pubmedpage, $matches);
        $ret_array['title'] = $matches['title'];
        $ret_array['authors'] = strip_tags($matches['authors']);
        preg_match('/<div class="aff"><h3.*Source<\/h3><p>(?P<institution>.*?)<\/p>/', $pubmedpage, $matches);
        $ret_array['institution'] = $matches['institution'];
        preg_match('/<div class="abstr">.*?\<p\>(?P<abstract>.*?)\<\/p>/', $pubmedpage, $matches);
        $ret_array['abstract'] = strip_tags($matches['abstract']);

        return $ret_array;
    } catch (Exception $e) {
        return false;
    }
}

// Takes a comma separated list, like the one constructed from build_simple_pmid_string, and creates a multi-dimensional array of all of the information produced by the scrape_pmid_abstract.
function process_pmid_input($fieldinput)
{
    $pmidarray = preg_split("/[\s,]+/", $fieldinput, null, PREG_SPLIT_NO_EMPTY);
    foreach ($pmidarray as &$pmid) {
        $pmid = scrape_pmid_abstract($pmid);
    }
    $pmidarray = array_filter($pmidarray); // remove entries wp_remote_get failed on
    return $pmidarray;
}

// Takes the processed input -- an array -- and builds a comma separated listed of PMIDs from it.
function build_simple_pmid_string($processedarray)
{
    if (is_array($processedarray)) {
        foreach ($processedarray as &$citation) {
            $citation = $citation['pmid'];
        }
        $processedarray = implode(", ", $processedarray);
        return $processedarray;
    } else {
        return false;
    }
}

// Takes an array, like that built from process_pmid_input, and returns it as a string.
function build_references_html($processedarray)
{
    ob_start();
    ?>
<div class="pmidcitationplus">
    <h1>References</h1>
    <ul>
        <?php
        foreach ($processedarray as $singlecitation) {
            echo "<li id=\"cit" . $singlecitation['pmid'] . "\">";
            $targetblank = $pmidplus_options["targetblank"] ? ' target="_blank"' : '';
            $openwithread = $pmidplus_options["open_with_read"] ? " [<a href=\"{$singlecitation['pmid']}\"{$targetblank}> Open with Read</a>]" : '';
            echo "{$singlecitation['authors']} {$singlecitation['title']} {$singlecitation['journal']} {$singlecitation['issue']} " . 'PMID: ' . '<a href="' . $singlecitation['url'] . "\"{$targetblank}>" . $singlecitation['pmid'] . '</a>.'.$openwithread;
            if ((strlen($singlecitation['abstract']) > 0) and $pmidplus_options['abstract_tooltip']) {
                echo '
                    <span style="display:none;" class="abstr">
                    ' . substr(trim($singlecitation['abstract']), 0, $pmidplus_options['abstract_tooltip_length']) . ' [...]
                    </span>
                    <script type="text/javascript">
                    jQuery(document).ready(function() {
                    jQuery("#cit' . $singlecitation['pmid'] . '").tooltip({
                        bodyHandler: function() {
                            return jQuery("#cit' . $singlecitation['pmid'] . ' .abstr").text();
                        },
                        showURL: false
                    });
                    });
                    </script>';
            }
            echo "</li>";
        }
        ?></ul>
</div>
<?php
    return ob_get_clean();
}

// Called to fill the new meta box about to be created.
function pmidplus_input_fields()
{
    global $post;
    wp_nonce_field(plugin_basename(__FILE__), 'pmidplus_nonce');
    // The actual fields for data entry
    echo '<label for="pmidinput">Comma separated list of PMIDs</label>';
    echo '<textarea id="pmidinput" name="pmidinput" value="' . build_simple_pmid_string(get_post_meta($post->ID, '_pcp_article_sources', true)) . '" rows="6" cols="35">' . build_simple_pmid_string(get_post_meta($post->ID, '_pcp_article_sources', true)) . '</textarea>';
}

//SYNTAX: add_meta_box( $id, $title, $callback, $page, $context, $priority, $callback_args ); 
function pmidplus_add_meta()
{
    add_meta_box("pmidplusmeta", "PMID Citation Plus", "pmidplus_input_fields", "post", "side", "high", $post);
    add_meta_box("pmidplusmeta", "PMID Citation Plus", "pmidplus_input_fields", "page", "side", "high", $post);
}

function pmidplus_save_postdata($post_id)
{
    // Make sure save is intentional, not just autosave.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return $post_id;

    // Verify this came from the our screen and with proper authorization
    if (!wp_verify_nonce($_POST['pmidplus_nonce'], plugin_basename(__FILE__)))
        return $post_id;

    // Check permissions
    if ('page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id))
            return $post_id;
    } else {
        if (!current_user_can('edit_post', $post_id))
            return $post_id;
    }

// So far so good. Now we need to save the data. Only do it if the field doesn't match.
    if (empty($_POST['pmidinput'])) {
        delete_post_meta($post_id, '_pcp_article_sources');
    } elseif (build_simple_pmid_string(get_post_meta($post_id, '_pcp_article_sources', true)) != $_POST['pmidinput'] && $_POST['wp-preview'] != 'dopreview') {
        // Take the form input, scrape the info from the pubmed pages, output multidimensional array, and save update.
        $neverusedbefore = process_pmid_input($_POST['pmidinput']);
        if (!update_post_meta($post_id, '_pcp_article_sources', $neverusedbefore)) {
            die('Unable to post pmid citation plus update to meta.');
        }
    }
}

// Adds references to the bottom of posts
function pmidplus_append_bibliography($contentofpost)
{
    global $post;
    if (get_post_meta($post->ID, '_pcp_article_sources', true))
        $contentofpost .= build_references_html(get_post_meta($post->ID, '_pcp_article_sources', true));
    return $contentofpost;
}

// TODO Future shortcode implementation in the works
// [pmidplus pmid="815460, 817050"]
/*
function shortcode_citation( $atts ) {
	if(array_key_exists('pmid', $atts)) {
		$pmids = explode(",", $atts['pmid']);
		}
			
		$intext	= "[HI]"; 
	}
	else {
		return "ELSE FALSE ". print_r($atts);
	}
}
*/


/******************** below this point, admin area code **********************/

if (is_admin()) {
    // Show the PMID Citation Plus option under the Settings section.
    add_action('admin_menu', 'pmidplus_admin_menu', 9);
    add_action('admin_init', 'register_pmidplus_settings', 9);
    // Show nag screen asking to rate plugin (first time only).
    add_action('admin_notices', 'pmidplus_rate_plugin_notice');
    wp_enqueue_style('pmidplus-style');
}

function pmidplus_admin_menu()
{
    // TODO make an actual icon instead of using my personal gravatar
    $icon = 'http://0.gravatar.com/avatar/89ba550ea497b0b1a329b4e9b10034b2?s=16&amp;d=http%3A%2F%2F0.gravatar.com%2Favatar%2Fad516503a11cd5ca435acc9bb6523536%3Fs%3D16&amp';
    add_object_page('PMID Citation Plus', 'PMID Citation Plus', 'edit_theme_options', 'pmid-citation-plus/includes/pmidplus-settings.php', '', $icon, 79);
}

function register_pmidplus_settings()
{
    register_setting('pmidplus_options', 'pmidplus_options', 'pmidplus_options_sanitization');
}

function pmidplus_rate_plugin_notice()
{
    if ($_GET['dismiss_rate_notice'] == '1') {
        update_option('pmidplus_rate_notice_dismissed', '1');
    } elseif ($_GET['remind_rate_later'] == '1') {
        update_option('pmidplus_reminder_date', strtotime('+10 days'));
    } else {
        // If no dismiss & no reminder, this is fresh install. Lets give it a few days before nagging.
        update_option('pmidplus_reminder_date', strtotime('+3 days'));
    }

    $rateNoticeDismissed = get_option('pmidplus_rate_notice_dismissed');
    $reminderDate = get_option('pmidplus_reminder_date');
    if (!$rateNoticeDismissed && (!$reminderDate || ($reminderDate < strtotime('now')))) {
        ?>
    <div id="pmidplus-rating-reminder" class="updated"><p>
        Hey, you've been using <a href="admin.php?page=pmid-citation-plus/includes/pmidplus-settings.php">PMID Citation Plus</a>
        for a while. Will you please take a moment to rate it? <br/><br/><a
        href="http://wordpress.org/extend/plugins/pmid-citation-plus" target="_blank" onclick="jQuery.ajax({'type': 'get', 'url':'options-general.php?page=pmidplus_options&dismiss_rate_notice=1'});">sure, i'll
        rate it right now</a>
        <a href="options-general.php?page=pmidplus_options&remind_rate_later=1" class="remind-later">remind me later</a>
    </p></div>
    <?php
    }
}

// ALL of the options array passes through this. This must be amended for new options.  
function pmidplus_options_sanitization($input)
{
    $safe = array();
    $input['abstract_tooltip_length'] = trim($input['abstract_tooltip_length']);

    if (preg_match('/^[\d]+$/', $input['abstract_tooltip_length'], $matches) and (intval($matches[1]) > 1)) {
        $safe['abstract_tooltip_length'] = $matches[1];
    }

    foreach(array('abstract_tooltip', 'open_with_read', 'targetblank') as $key => $value) {
        if ($value) {
            $safe[$key] = true;
        } else {
            $safe[$key] = false;
        }
    }

    return $safe;
}

?>
