=== Fast User Switching ===
Contributors: kasperta
Tags: authentication, user, switch, switching, admin, impersonate
Donate link: http://www.tikweb.dk/donate/
Requires at least: 4.6
Tested up to: 4.8.2
Requires PHP: 5.2
Stable tag: 1.3.3

Allow only administrators to switch to and impersonate any site user, by selecting "Impersonate" on the users list.

== Description ==

Allow only administrators to impersonate any site user. Choose user to impersonate, by clicking new "Impersonate" link in the user list. To return to your own user, just log out. A log out link is available in the black top menu, top right, profile submenu.

When you impersonate a user, you will be effectively logged in as that user, and acquire the same rights - very convenient for testing rights for users. Also practical for consultants, bureaus and copy-writers who need to create and edit content for customers.

== Installation ==

1. Upload the 'fast-user-switching' folder to the '/wp-content/plugins/' directory
2. Browse to your WordPress admin control panel, and activate the plugin through the 'Plugins' menu
3. Go to the 'Users' list and press Impersonate.

== Frequently Asked Questions ==

= There is no Impersonate link in the Users list =
Only administrators can see the link - or other users who have the "add_users" capability added (only admins by default).

= How do i get back to my own login? =
Log out and you are back, the plugin remembers your original login, and returns you to your usual login.

== Screenshots ==

1. All Users Page
2. Switch back to old user
3. Recent impersonate user list

== Changelog ==

= 1.3.3 - 2017-10-11 =
* Added a new link to the plugin settings below the line “Settings”.
* Added an option to show the login date after the username for the last 5 logins, so by default it becomes: “Firstname Surname (Role - username - date).

= 1.3.2 - 2017-10-11 =
* Fixed settings issue

= 1.3.1 - 2017-10-11 =
* Fixed Fatal error, updated file "fast-user-switching.php" - line 304

= 1.3.0 - 2017-10-11 =
* Fixed User role issue
* Added a new options for setting like "Show first name and surname", "Show role (access level)" and "Show user name". All three options are default enabled when you update this plugin.
* Updated .po and .mp files

= 1.2.3 - 2017-10-09 =
* Fixed search for user role "employee" on the user switch dropdown, before it shows the username but the role didn't show inside ()

= 1.2.2 - 2017-10-02 =
* Fixed front-end style.

= 1.2.1 - 2017-10-02 =
* Updated .po and .mp files

= 1.2.0 - 2017-10-02 =
* Shortened the length of the dropdown, so the length matches ex. 5 latest. If you have searched, then length should match 5 latest and 10 search results.
* The search results included a scroll bar, so you can scroll inside dropdown, and see all results.
* Fixed the input field in the top bar and the button looks a bit strange in Safari.
* Added the users access level to each line for last results and search results, so they are listed as “Firstname Surname (Access level)”
* Changed “Impersonate user” to “Switch user” in all texts.
* Changed input field “Submit” to “Search/Søg”

= 1.0.1 =
* Fixed the issue for prior to PHP 5.5

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= x.0.0 =
* There are nothing else needed, than upgrading from the WordPress plugins screen.
