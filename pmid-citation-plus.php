<?php
/*
Plugin Name: PMID Citation Plus
Plugin URI: http://www.mdpatrick.com/pmidcitationplus/
Version: 1.0.4
Author: Dan Patrick
Description: This plugin makes citing scientific studies in an aesthetically pleasing manner much more easy. It allows you to simply enter in Pubmed IDs and have a references list automatically built for you. At the moment it only supports PMIDs, but in the future will also support citation via DOI.
*/


add_action('wp_enqueue_scripts', 'enqueue_pmid_scripts');
add_action('admin_init', 'pmidplus_add_meta');
add_filter('the_content', 'addsometext', 9);
add_shortcode('pmidplus', 'shortcode_cite');
// Execute save function on save.
add_action('save_post', 'pmidplus_save_postdata');

// Add script necessary to have abstract in tooltip.
function enqueue_pmid_scripts()
{
    wp_enqueue_script('jquery');
    wp_register_script('jquery-tooltip', plugins_url('/js/jquery-tooltip/jquery.tooltip.js', __FILE__));
    wp_enqueue_script('jquery-tooltip');
    wp_register_style('jquery-tooltip', plugins_url('/js/jquery-tooltip/jquery.tooltip.css', __FILE__));
    wp_enqueue_style('jquery-tooltip');
}

// Grabs pubmed page from URL, pulls into string, parses out an array with: title, journal, issue, authors, institution.
function scrape_pmid_abstract($pubmedid)
{
    $pubmedpage = file_get_contents('http://www.ncbi.nlm.nih.gov/pubmed/' . $pubmedid);
    preg_match('/<div class="cit">(?P<journal>.*?)<\/a>(?P<issue>.*?\.).*?<\/div><h1>(?P<title>.+)<\/h1><div class="auths">(?P<authors>.+)<\/div><div class="aff"><h3.*Source<\/h3><p>(?P<institution>.*?)<\/p>.*?<div class="abstr">.*?\<p\>(?P<abstract>.*?)\<\/p\>(<p>\s*(©|Copyright|\s|\n|&#169;|&copy;).*?<\/p>)*<\/div>/', $pubmedpage, $matches);
    $abstract = array(
        'authors' => strip_tags($matches['authors']),
        'title' => $matches['title'],
        'institution' => $matches['institution'],
        'journal' => strip_tags($matches['journal']),
        'issue' => trim($matches['issue']),
        'pmid' => $pubmedid,
        'url' => 'http://www.ncbi.nlm.nih.gov/pubmed/' . $pubmedid,
        'abstract' => strip_tags($matches['abstract'])
    );
    return $abstract;
}

// Takes a comma separated list, like the one constructed from build_simple_pmid_string, and creates a multi-dimensional array of all of the information produced by the scrape_pmid_abstract.
function process_pmid_input($fieldinput)
{
    $pmidarray = preg_split("/[\s,]+/", $fieldinput, null, PREG_SPLIT_NO_EMPTY);
    foreach ($pmidarray as &$pmid) {
        $pmid = scrape_pmid_abstract($pmid);
    }
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
            echo "{$singlecitation['authors']} {$singlecitation['title']} {$singlecitation['journal']} {$singlecitation['issue']} " . 'PMID: ' . '<a href="' . $singlecitation['url'] . '">' . $singlecitation['pmid'] . '</a>.';
            if (strlen($singlecitation['abstract']) > 0) {
                echo '
<span style="display:none;" class="abstr">
' . substr(trim($singlecitation['abstract']), 0, 445) . ' [...]
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
function addsometext($contentofpost)
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
?>
