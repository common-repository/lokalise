=== Lokalise Companion Plugin ===
Contributors: lokalise
Tags: lokalise,localise,lokalise companion app,localize,localization,app localization,localize mobile apps,localization system,website localization,localization software,localization platform,localization workflow,tms
Requires at least: 4.9
Tested up to: 6.0.0
Requires PHP: 5.4.2
Stable tag: trunk
License: LGPLv3
License URI: https://www.gnu.org/licenses/lgpl-3.0.html

Simplify WordPress website translation and multilingual content management with Lokalise.

== Description ==
Set up a continuous, automated, and streamlined localization process for your multi language WordPress websites and blogs. By integrating WordPress and Lokalise, you gain access to a modern translation management system with robust functionalities.

Using Lokalise to manage localization processes leads to faster launches, fewer errors, and savings on translation budgets. Here are some of the features that make this possible:

* Machine translation suggestions for rapid results
* Translation memory (TM) that stores your previous translations and helps improve consistency, as well as reducing translation costs and turnaround time
* Comments and chat for the whole project or a particular translation
* Powerful filter and search functionalities on multiple levels (string, status, language, etc.)
* Automated notifications when certain actions are completed
* And many more.

Lokalise is a collaborative translation platform which means you can work with your team and external partners, creating a single source of truth for all your translation projects.

_This plugin is relying on a 3rd party as a service. In order to use the plugin, you are required to have an account on [http://app.lokalise.com](http://app.lokalise.com). Once you have a paying account, you can configure the WordPress plugin in Lokalise environment by following the [documentation](https://docs.lokalise.com/en/articles/4131716-wordpress). After the plugin is configured, you can import & export the content between Lokalise & WordPress. No personal data or any other sensitive information is sent to 3rd parties._

== Frequently Asked Questions ==
For a detailed explanation of how the integration works and how to set it up, take a look at our [WordPress integration tutorial](https://docs.lokalise.com/en/articles/4131716-wordpress).


== Screenshots ==
1. Connect WordPress with Lokalise to manage your multilingual posts
2. Link items that you would like to translate
3. Translate content with all the features of modern translation management software within Lokalise, and export it back to WordPress in just a few clicks

== Changelog ==
= 1.6.9 =
* Fixed "Argument #1 ($term) must be of type WP_Term, bool given"

= 1.6.8 =
* Enable Lokalise to access supported ACF fields from ACF Pro plugin

= 1.6.7 =
* Fixed default locale for polylang translation provider

= 1.6.6 =
* Fixed plugin version

= 1.6.5 =
* Fixed default locale

= 1.6.4 =
* Removed Guard Cookie

= 1.6.2 =
* Generate full auth page URL

= 1.6.1 =
* Fixed fatal error for caused by getallheaders()
* Added missing get_footer() call at end of authorization view
* Inform administrators that more than one supported i18n plugin is enabled

= 1.6.0 =
* Added multisite support

= 1.5.0 =
* Added support for Advanced Custom Fields

= 1.4.0 =
* Added support for Polylang

= 1.3.0 =
* Added authorization independent from standard login page

= 1.2.0 =
* Added support for custom post types

= 1.1.4 =
* Added authorization header validation to environment rest
* Added apache_request_headers as base if possible

= 1.1.3 =
* Changed readme.txt

= 1.1.2 =
* Added readme.txt, ReadmeOSS

= 1.1.1 =
* Fixed hard-coded table prefix

= 1.1.0 =
* Added list of post types that can be edited by current user to environment REST
* Dependency to process request headers

= 1.0.3 - 2020-07-02 =
* Added CHANGELOG.md
* Changed correct link to documentation

= 1.0.2 - 2020-07-02 =
* Fixed authorization secret regeneration

= 1.0.1 - 2020-07-01 =
* Added default locale to environment REST

= 1.0.0 - 2020-06-19 =
* Added plugin code
