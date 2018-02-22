# dd_breakgen

Check the [dd_breakgen](https://bitbucket.org/tamtam-nl/dept-digital-breakgen) repository for latest versions

## Requirements
- https://www.drupal.org/project/image_widget_crop

## Description

The Breakgen module provides a new drush command (`drush ddbg`) which generate imagestyles based on your theme breakpoints.yml file

## Example dd_breakgen.breakpoint.yml file
Please check for your own theme or directly use the dd_breakgen.breakpoint.yml inside your project. 

## Add the disered crop types
For example add the 16_9 in the cms /admin/config/media/crop

## Howto generate
Run the command `drush ddbg dd_breakgen`   

Please note if you define your own *.breakpoints.yml file please clear **the cache** before running the `drush ddbg` command.
Clearing the cache will read the breakpoints.yml file again after modifications.

## Mapping responsive images
Here is how you get the desired output (no picture element) within Drupal:

1. Skip creating specific breakpoints in your theme, they aren't needed with this approach.
2. Setup your different image styles at admin/config/media/image-styles. Usually something like, Author Small, Author Medium and Author Large.
3. Create a responsive image style at admin/config/media/responsive-image-style. Make sure the Responsive Image module is enabled first. Screenshot of responsive image style creation interface
4. Ensure "Responsive Image" is selected for the "Breakpoint group".
5. Choose a suitable "Fallback image style". Click "Save". The following screen is where the secret sauce is.
6. Under the "1x Viewport Sizing []" fieldset, Select "Select multiple image styles and use the sizes attribute."
7. Select each of Image styles you'd like to use.
8. Adjust the Sizes value as needed. The default here 100vw is hard-coded for a good reason, it's a pretty sane default and works well in most situations. Customize this is you want even finer control. More on Sizes. Screenshot of an example Resposive Image Style
9. Adjust your content type (or other entity) to use your new responsive image style either by altering the display formatter or handling programmatically. Adjust Entity Screenshot
10. Verify results!
For a thorough explanation of how all of the bits of the Responsive Image module work, see the documentation at admin/help/responsive_image with the Help module enabled.
