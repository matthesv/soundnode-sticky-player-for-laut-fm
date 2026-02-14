=== Laut.fm Sticky Player ===
Contributors: matthesv
Tags: laut.fm, radio, audio, stream, player, sticky, music, webradio
Requires at least: 5.8
Tested up to: 6.9.1
Requires PHP: 7.4
Stable tag: 1.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A customizable sticky audio player for any laut.fm radio station. Stream live radio directly on your WordPress site.

== Description ==

**Laut.fm Sticky Player** adds a modern, customizable audio player that stays visible (“sticky”) while users scroll your site. It’s built for **laut.fm** stations and designed to be simple to set up while still giving you plenty of visual control.

**Perfect for:**
* Radio stations on laut.fm
* Music blogs, event sites, DJs, clubs
* Anyone who wants background audio without forcing users to stay on one page

= Features =
* **Sticky player** at the top or bottom of the page
* **Works with any laut.fm station** (enter the station slug)
* **Optional custom stream URL** (if you want to play a different stream endpoint)
* **Playback modes** (e.g. inline playback or opening the station website)
* **Design controls**: accent colors, background color, text color
* **Mobile toggle**: show/hide on mobile devices
* **Optional live clock**
* **Optional collapse/expand toggle**
* **Optional “soundnode.de” link** (can be disabled)

= Privacy & data =
This plugin does not create user accounts and does not track users. It only loads the configured stream and (optionally) retrieves publicly available station metadata for display.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install it via *Plugins → Add New*.
2. Activate **Laut.fm Sticky Player**.
3. Go to *Settings → Laut.fm Sticky Player*.
4. Enter your **Station Name** (laut.fm station slug, e.g. `youfm`).
5. Customize design and playback options.
6. Visit your site frontend — the player will appear once a station is configured.

== Frequently Asked Questions ==

= What do I enter as “Station Name”? =
Use the **laut.fm station slug**, the part that appears in the station URL.
Example: `https://laut.fm/youfm` → Station Name: `youfm`

= Can I use my own stream URL instead of laut.fm? =
Yes. Set **Custom Stream URL** and choose the corresponding playback option (if available in your settings). This is useful if you have an alternate stream endpoint.

= Why doesn’t autoplay work? =
Most browsers block autoplay with sound. This is a browser policy, not a plugin bug. Users usually need to press play once.

= Does this plugin work with caching plugins? =
Yes. If your cache plugin caches HTML aggressively, make sure the player assets (CSS/JS) are not blocked. In general it works fine with common caching setups.

= Is the player visible for all visitors? =
Yes, as long as a station is configured. If no station is set, only admins see a small notice on the frontend.

== Screenshots ==

1. Sticky player on the frontend (bottom position)
2. Sticky player on the frontend (top position)
3. Settings page: station configuration
4. Settings page: design options (colors, height)
5. Settings page: feature toggles (clock, mobile, collapse)

== Changelog ==

= 1.2.1 =
* Maintenance release: improved WordPress.org compatibility and code quality
* Admin settings: improved i18n/escaping and validation
* General cleanup and stability improvements

= 1.2.0 =
* Feature and settings improvements

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.2.1 =
Recommended update. Includes maintenance improvements and better compatibility with current WordPress versions.
