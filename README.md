# PMID Citation Plus (for WordPress)
This plugin allows authors to quickly construct a rich bibliography not unlike the bottom of Wikipedia articles by simply typing in the [Pubmed ID](https://github.com/mdpatrick/PMID-Citation-Plus/blob/master/screenshot-3.png)s to the relevant studies.

This plugin should appeal primarily to health and science bloggers.

[View "PMID Citation Plus" in the WordPress.org Plugin Directory](http://wordpress.org/plugins/facebook-simple-like/)

#### Todo
* Ditch output buffering. (ob_start, ob_end_clean, ob_get_contents, ob_end_flush, etc.)
* Add tooltip containing abstract text on hover over reference in bibliography.
* Add shortcode functionality that allows people to put citations in-line.
* Update source to exclusively use [PSR style guide & standards](http://www.php-fig.org/) WordPress standard.
* Add support for DOI.
* Create settings page with contact form and make abstract tooltips a toggleable option.
* Make length of abstract provided in tooltip user-definable. (Default is 445 characters before truncation occurs.)
* Add better exception handling (and form validation).
* Replace regexp with actual html/xml parser. Use css selectors, and possibly xpath.
* Add one-time nag screen to remind people to review the plugin.
* ???

Got an idea? Submit a pull request!

##### Screenshots
* [The references block at the bottom of a blog post](https://github.com/mdpatrick/PMID-Citation-Plus/blob/master/screenshot-1.png).
* [The PMID Citation Plus entry field above the update button](https://github.com/mdpatrick/PMID-Citation-Plus/blob/master/screenshot-2.png).
* [A PubMed abstract with the location of the PMID indicated](https://github.com/mdpatrick/PMID-Citation-Plus/blob/master/screenshot-3.png).
