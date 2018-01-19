=== Fast User Switching ===
Contributors: kasperta
Tags: authentication, user, switch, switching, admin, impersonate
Donate link: http://www.tikweb.dk/donate/
Requires at least: 4.6
Tested up to: 4.8
Requires PHP: 5.2
Stable tag: 1.0.1

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

= 1.0.1 =
* Fixed the issue for prior to PHP 5.5

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
* Initial release.
