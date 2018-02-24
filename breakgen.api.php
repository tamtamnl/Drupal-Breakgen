<?php

/**
 *  Hook for altering the image style before breakgen saves it.
 *
 * @param \Drupal\image\Entity\ImageStyle $imageStyle
 */
function hook_breakgen_image_style_alter(
    \Drupal\image\Entity\ImageStyle &$imageStyle
) {
    // E.G: change properties within the image style
}

/**
 * Hook for altering a image style effect before breakgen add it to the
 * image style.
 * related to breakgen.
 */
function hook_breakgen_image_style_effect_alter(array &$effectConfiguration)
{
    // E.G: modify effect before it gets added
}

/**
 * Hook that fires before breakgen clears all image styles
 * related to breakgen.
 */
function breakgen_pre_clear_image_styles()
{
    // E.G: clear any entities related to breakgen image styles
}