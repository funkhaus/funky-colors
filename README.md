# funky-colors
A Wordpress plugin to detect a palette of colors from each image in the media library. Uses [Color Extractor](https://github.com/thephpleague/color-extractor) and provides Wordpress meta fields and UI.

# How
1. Install and activate the plugin, then navigate to `Tools > Funky Colors`.
1. Press `Detect Colors on All Images` to detect and save color palettes on each image.
    1. You can view and edit the primary color any time by going to `Media`, then selecting the image and checking the "Primary Color" field.
1. Use the [convenience functions](#convenience-functions) to work with image colors in your template.

# Metadata
Funky Colors stores its data in a few meta fields: `FIC_color` for the primary color, `FIC_secondary_color` for the manually-set secondary color, and `FIC_palette` for the image's [palette](https://github.com/thephpleague/color-extractor#usage).

It's best to use the `get_second_image_color` function to find an image's secondary color, since it will fall back to the appropriate value if `FIC_secondary_color` is not set.

You can use `$your_attachment->FIC_color` to find the main hex color of an image and `$your_attachment->FIC_palette[1]` to find the secondary hex color. The `FIC_palette` works like a zero-indexed array - substitute `1` with any number between `2` and `4` to try to find the third to fifth most common colors.

# Convenience Functions
* `get_primary_image_color( $attachment_id )` Returns the hex value of an image's primary color, if it has one. Returns an empty string otherwise.
* `get_second_image_color( $attachment_id )` Returns the hex value of an image's secondary color, if it has one. Uses the manually-set value first, then falls back to the secondary color from the palette. Returns an empty string if none found.

--------

__funky-colors__

http://funkhaus.us

Version: 1.2

Requires at least WP 3.8
