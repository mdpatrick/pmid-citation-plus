=== PMID Citation Plus ===
Contributors: mdanielpatrick
Donate link: http://www.mdpatrick.com/donate/
Tags: pmid, PubMed, citing, cite, citation, science, academia, scholarship, school, academic, university, education, research, bibliography, references
Stable tag: trunk
Requires at least: 3.1.2
Tested up to: 3.6.1

This plugin allows you to simply enter in PubMed IDs (PMIDs) and have a references list automatically built at the bottom of your post for you.

== Description ==

Feature request?

**[Consider dropping me a few bucks (donate page)](http://www.mdpatrick.com/donate/)**

.. or ..

**[You can make your own feature and create a pull request on GitHub. This plug is open source.](https://github.com/mdpatrick/PMID-Citation-Plus)**

This plugin makes citing scientific studies in an *aesthetically pleasing manner* much more easy. 

It allows you to simply enter in PubMed IDs on the composition page and have a references list (very similar to Wikipedia's) automatically built for you. At the moment it only supports PMIDs, but in the future will also support citation via DOI.

= Features =

* Creates an input box on your post composition page where you can input PubMed IDs.
* PubMed IDs are then stored in the database along with your post, and this data is used to create a references block at the bottom of your post.

== Screenshots ==

1. The references block at the bottom of a blog post.
2. The PMID Citation Plus entry field above the update button.
3. A PubMed abstract with the location of the PMID indicated.

== Upgrade Notice ==

= 1.0.8 =
Download this update if you'd like to be able to toggle abstract tooltips on/off, change its length, and/or add "Open with Read (By QxMD)" links to citations.

= 1.0.6 =
This update fixes an important javascript error that broke tooltips which allow the user to see part of the associated abstract upon hover. It also includes a fix which should prevent some PMIDs from failing to create a correct entry in the bibliography.

= 1.0.1 =
Swapped input for larger textarea on pubmed id entry input.

= 1.0.0 =
Release

== Changelog ==

= 1.0.8 =
* Added a new settings page.
* Added contact form to get in touch with me (the author).
* Added ability to toggle abtract tooltips on/off.
* Added ability to set length of abstract in tooltip.
* Added ability to append "Open with Read" link to bibliographic entries.
* Add target="_blank" (open in new window) to links in bibliography. This can be toggled in new settings page.
* Added a nag notice to remind you guys to rate the plugin.


= 1.0.6 =
* Tooltips fixed, now shows part of abstract when hovering an entry in the bibliography.
* Abstracts with no institution listed are now able to be parsed.
* Improved error handling for some particular edge cases.

= 1.0.1 =
* Swapped input for larger textarea on pubmed id entry input.

= 1.0.0 =
* Release


== Installation ==

1. Download the plugin

2. Extract the contents of pmid-citation-plus.zip to wp-content/plugins/ folder. You should get a folder called pmid-citation-plus.

3. Activate the Plugin in WP-Admin.

4. Go to your post composition page, and enter in your comma separated list of PubMed IDs in the field at the top right hand of the page.


== Frequently Asked Questions ==

= What are the requirements for this plugin? =

A recent version of WordPress. The plugin doesn't do anything too fancy, so it probably runs on older versions too.

= Do you do WordPress consultation work? =

Absolutely! I'm a Zend Certified PHP 5.3 engineer, and can customize or create WordPress plugins and themes. Visit http://www.mdpatrick.com or email me (see support, below) for more details.

= Can I customize what is displayed? =

The plugin uses the css class 'pmidcitationplus' in the div that surrounds the references block. If you would like, you may use this class to then style it by adding code to your *style.css* file of your theme folder.

For more information, please visit http://www.mdpatrick.com/pmidcitationplus/

= Support =

For now, you may email me at **dan atsymbolgoeshere mdpatrick.com**.
