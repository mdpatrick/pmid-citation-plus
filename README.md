# PMID Citation Plus (for WordPress)
This plugin allows authors to quickly construct a rich bibliography not unlike the bottom of Wikipedia articles by simply typing in the [Pubmed ID](https://github.com/mdpatrick/PMID-Citation-Plus/blob/master/screenshot-3.png)s to the relevant studies.

This plugin should appeal primarily to health and science bloggers.

[View "PMID Citation Plus" in the WordPress.org Plugin Directory](http://wordpress.org/plugins/pmid-citation-plus/)

#### Todo
* Ditch output buffering. (ob_start, ob_end_clean, ob_get_contents, ob_end_flush, etc.)
* Add shortcode functionality that allows people to put citations in-line.
* Update source to exclusively use [PSR style guide & standards](http://www.php-fig.org/) instead of the WordPress standard.
* Add support for DOI.
* Add better exception handling (and form validation).
* Replace regexp with actual html/xml parser. Use css selectors, and possibly xpath.
* ???

Got an idea? Submit a pull request!

##### Screenshots
* [The references block at the bottom of a blog post](https://github.com/mdpatrick/PMID-Citation-Plus/blob/master/screenshot-1.png).
* [The PMID Citation Plus entry field above the update button](https://github.com/mdpatrick/PMID-Citation-Plus/blob/master/screenshot-2.png).
* [A PubMed abstract with the location of the PMID indicated](https://github.com/mdpatrick/PMID-Citation-Plus/blob/master/screenshot-3.png).
