=== List Field Character Limit for Gravity Forms ===
Contributors: ovann86
Donate link: http://www.itsupportguides.com/
Tags: Gravity Forms
Requires at least: 4.4
Tested up to: 4.7.0
Stable tag: 1.2.0
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows you to set character limits for list fields

== Description ==

> This plugin is an add-on for the <a href="https://www.e-junkie.com/ecom/gb.php?cl=54585&c=ib&aff=299380" target="_blank">Gravity Forms</a> (affiliate link) plugin. If you don't yet own a license for Gravity Forms - <a href="https://www.e-junkie.com/ecom/gb.php?cl=54585&c=ib&aff=299380" target="_blank">buy one now</a>! (affiliate link)

**What does this plugin do?**

* Adds the ability to set a character limit for list field columns
* Make a list field column a larger textarea field - with or without a character limit

> See a demo of this plugin at [demo.itsupportguides.com/list-field-character-limit-for-gravity-forms/](http://demo.itsupportguides.com/list-field-character-limit-for-gravity-forms/ "demo website")

**Disclaimer**

*Gravity Forms is a trademark of Rocketgenius, Inc.*

*This plugins is provided “as is” without warranty of any kind, expressed or implied. The author shall not be liable for any damages, including but not limited to, direct, indirect, special, incidental or consequential damages or losses that occur out of the use or inability to use the plugin.*

== Installation ==

1. Install plugin from WordPress administration or upload folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in the WordPress administration
1. Open the Gravity Forms 'Forms' menu
1. Open the forms editor for the form you want to change
1. Add or open an existing list field
1. With multiple columns enabled you will see a 'Character limit' option - when ticked choose field type (input or textarea) and enter a character limit

== Screenshots ==

1. Shows the 'Character Limits' option in the forms editor
2. Shows a list field with character limits applied

== Changelog ==

= 1.2.0 =
* Fix: Patch to allow scripts to enqueue when loading Gravity Form through wp-admin. Gravity Forms 2.0.3.5 currently has a limitation that stops the required scripts from loading through the addon framework.
* Maintenance: Add minified JavaScript and CSS
* Maintenance: Confirm working with WordPress 4.6.0 RC1
* Maintenance: Update to improve support for Gravity Flow plugin
* Maintenance: Improve support in entry editor.

= 1.1.2 =
* Fix: Fix styling for single-column character limit enabled fields. 
* Maintenance: Add some styling to the options in the form editor.

= 1.1.1 =
* Fix: Stop character limit settings appearing on non-list field 'appearance' tab

= 1.1.0 =
* Maintenance: Change JavaScript and CSS to load using Gravity Forms addon framework.
* Maintenance: Tested against Gravity Forms 2.0 RC1
* Maintenance: Tested against Gravity PDF 4.0 RC4

= 1.0.2 =
* Maintenance: Switch to using Gravity forms rgar function
* Maintenance: Add blank index.php to reduce the risk of plugin directory being navigated 

= 1.0.1 =
* Fix: Remove 'Apply character limit' option from field appearance tab
* Maintenance: Switch to using Gravity forms rgar function

= 1.0 =
* First public release.

== Upgrade Notice ==

= 1.0 =
First public release.