=== SoundNode Sticky Player for laut.fm ===
Contributors: matthesv
Tags: laut.fm, radio, stream, player, webradio
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.5.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A customizable sticky audio player for any laut.fm radio station. Stream live radio directly on your WordPress site.

== Description ==

**SoundNode Sticky Player for laut.fm** adds a modern, customizable audio player that stays visible ("sticky") while users scroll your site. It's built for **laut.fm** stations and designed to be simple to set up while still giving you plenty of visual control.

**Perfect for:**
* Radio stations on laut.fm
* Music blogs, event sites, DJs, clubs
* Anyone who wants background audio without forcing users to stay on one page

= Features =
* **Sticky player** at the top, bottom, left or right of the page
* **Works with any laut.fm station** (enter the station slug)
* **Optional custom stream URL** (if you want to play a different stream endpoint)
* **Playback modes**: inline playback, popup station website or popup stream only
* **Design controls**: accent colors, background color, text color
* **Mobile toggle**: show/hide on mobile devices
* **Optional live clock**
* **Optional collapse/expand toggle**
* **Optional "soundnode.de" link** (can be disabled)

== External services ==

This plugin connects to the external laut.fm API (`https://api.laut.fm`) to retrieve and display public radio station information, such as the currently playing song and recently played tracks. 

When the plugin fetches this data, it sends requests containing the station name (configured by the site administrator) to the laut.fm API. The plugin only loads the configured stream and retrieves publicly available station metadata for display. It does not create user accounts, does not track users, and no personally identifiable information (PII) of the site visitors is sent to the API by the plugin's server-side requests.

This service is provided by laut.ag. You can find their legal policies here:
* Terms of Service: https://laut.fm/pages/terms_and_conditions
* Privacy Policy: https://laut.fm/pages/privacy

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install it via *Plugins → Add New*.
2. Activate **SoundNode Sticky Player for laut.fm**.
3. Go to *Settings → SoundNode Sticky Player*.
4. Enter your **Station Name** (laut.fm station slug, e.g. `youfm`).
5. Customize design and playback options.
6. Visit your site frontend — the player will appear once a station is configured.

== Frequently Asked Questions ==

= What do I enter as "Station Name"? =
Use the **laut.fm station slug**, the part that appears in the station URL.
Example: `https://laut.fm/youfm` → Station Name: `youfm`

= Can I use my own stream URL instead of laut.fm? =
Yes. Set **Custom Stream URL** in the station settings. When a custom URL is set, the player automatically switches to inline playback mode.

= Why doesn't autoplay work? =
Most browsers block autoplay with sound. This is a browser policy, not a plugin bug. Users usually need to press play once.

= Does this plugin work with caching plugins? =
Yes. If your cache plugin caches HTML aggressively, make sure the player assets (CSS/JS) are not blocked. In general it works fine with common caching setups.

= Is the player visible for all visitors? =
Yes, as long as a station is configured. If no station is set, only admins see a small notice on the frontend.

== Changelog ==

= 1.5.1 =
* Improved: volume slider now shows a filled progress track (colored up to the current level) instead of a plain bar
* Confirmed compatibility with WordPress 7.1

= 1.5.0 =
* New: Customizable soundnode.de URL slug in settings
* The URL path for the soundnode.de link can now be individually configured per station
* Default: pre-filled with the laut.fm station name
* Added terms of service notice when using inline playback mode with laut.fm streams

= 1.4.0 =
* Rebranding: consistent use of "SoundNode Sticky Player" throughout the plugin
* New player positions: left and right (in addition to top and bottom)
* New playback mode: popup stream only
* Fixed color picker not initializing in admin settings
* Fixed text domain inconsistency across all files
* Improved playback mode validation with whitelist check
* Custom stream URL now correctly forces inline playback
* Admin slug updated from "laut-fm-sticky-player" to "soundnode-sticky-player"
* Code refactoring: centralized constants in admin settings class

= 1.3.0 =
Maintenance update recommended for best compatibility with current WordPress versions.

= 1.2.3 =
Maintenance update recommended for best compatibility with current WordPress versions.

= 1.2.2 =
Maintenance update recommended for best compatibility with current WordPress versions.

= 1.2.1 =
* Maintenance release: improved WordPress.org compatibility and code quality
* Admin settings: improved i18n/escaping and validation
* General cleanup and stability improvements

= 1.2.0 =
* Feature and settings improvements

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.5.0 =
New feature: the soundnode.de link URL is now customizable in the settings. Added terms of service notice for inline playback.

= 1.4.0 =
Recommended update. Includes rebranding, new player positions (left/right), new popup stream mode, and several bug fixes.