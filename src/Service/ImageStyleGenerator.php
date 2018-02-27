<?php

namespace Drupal\breakgen\Service;

use Drupal\breakpoint\BreakpointManager;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Theme\ThemeManager;
use Drupal\image\Entity\ImageStyle;
use Drupal\image\ImageEffectManager;

class ImageStyleGenerator
{

    const EFFECT_ALTER_HOOK = 'breakgen_image_style_effect_alter';
    const IMAGE_STYLE_ALTER_HOOK = 'breakgen_image_style_alter';
    const PRE_CLEAR_HOOK = 'breakgen_pre_clear_image_styles';
    const POST_SAVE_HOOK = 'breakgen_post_save_image_styles';

    private $breakpointManager;
    private $imageEffectManager;
    private $entityTypeManager;
    private $themeManager;
    private $moduleHandler;

    /**
     * ImageStyleGenerator constructor.
     *
     * @param \Drupal\breakpoint\BreakpointManager $breakpointManager
     * @param \Drupal\image\ImageEffectManager $imageEffectManager
     * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
     * @param \Drupal\Core\Theme\ThemeManager $themeManager
     * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
     */
    public function __construct(
        BreakpointManager $breakpointManager,
        ImageEffectManager $imageEffectManager,
        EntityTypeManager $entityTypeManager,
        ThemeManager $themeManager,
        ModuleHandlerInterface $moduleHandler
    ) {
        $this->breakpointManager = $breakpointManager;
        $this->imageEffectManager = $imageEffectManager;
        $this->entityTypeManager = $entityTypeManager;
        $this->themeManager = $themeManager;
        $this->moduleHandler = $moduleHandler;
    }

    /**
     * Generate image styles and effects from the breakpoint file.
     *
     * @param null $theme
     */
    public function generate($theme = null)
    {
        if ($theme === null) {
            $theme = $this->themeManager->getActiveTheme()->getName();
        }

        $this->clear();
        $breakpoints = $this->breakpointManager->getBreakpointsByGroup($theme);
        foreach ($breakpoints as $key => $breakpoint) {
            $this->generateImagesStyles($key, $breakpoint);
        }
    }

    /**
     * Clear all image styles related to breakgen.
     */
    public function clear()
    {
        $this->moduleHandler->invokeAll(self::PRE_CLEAR_HOOK);

        $imageStyles = $this->entityTypeManager->getStorage('image_style')
            ->getQuery()
            ->condition('name', "breakgen", 'CONTAINS')
            ->execute();

        $imageStyles = $this->entityTypeManager->getStorage('image_style')
            ->loadMultiple($imageStyles);

        $this->entityTypeManager->getStorage('image_style')->delete($imageStyles);
    }

    /**
     * Generate image styles for breakgen.
     *
     * @param $key
     * @param $breakpoint
     */
    private function generateImagesStyles($key, $breakpoint)
    {
        // If this breakpoint has breakgen mapping
        if (isset($breakpoint->getPluginDefinition()['breakgen'])) {
            $breakgen = $breakpoint->getPluginDefinition()['breakgen'];
            foreach ($breakgen as $groupName => $data) {
                $this->generateImageStyle(
                    $key,
                    $breakpoint->getLabel()->__toString(),
                    $groupName,
                    $data['style_effects']
                );

                if (isset($data['percentages'])) {
                    $this->generatePercentageDeviation(
                        $data['percentages'],
                        $key,
                        $groupName,
                        $breakpoint,
                        $data['style_effects']
                    );
                }

                $this->moduleHandler->invokeAll(self::POST_SAVE_HOOK, [$key, &$breakpoint, &$breakgen]);
            }
        }
    }

    /**
     * Generates a percentage deviation of the original image style.
     *
     * @param array $percentages
     * @param $key
     * @param $groupName
     * @param $breakpoint
     * @param $styleEffects
     */
    private function generatePercentageDeviation(
        array $percentages,
        $key,
        $groupName,
        $breakpoint,
        $styleEffects
    ) {
        foreach ($percentages as $percentage) {
            $modifier = str_replace('%', '', $percentage) / 100;
            $percentage = str_replace('%', '', $percentage);

            $this->generateImageStyle(
                $key . ".$percentage",
                $breakpoint->getLabel()->__toString() . " ($percentage%)",
                $groupName,
                $styleEffects,
                $modifier
            );
        }
    }

    /**
     * Generate image style for breakgen.
     *
     * @param $breakpointKey
     * @param $breakpointLabel
     * @param $groupName
     * @param $styleEffects
     * @param null $modifier
     */
    private function generateImageStyle(
        $breakpointKey,
        $breakpointLabel,
        $groupName,
        $styleEffects,
        $modifier = null
    ) {
        // Generate machine name.
        $machineName = $breakpointKey . '_breakgen_' . $groupName;

        // Generate label.
        $label = "$breakpointLabel $groupName";

        // Create a image style entity
        $imageStyle = ImageStyle::create([
            'name' => $machineName,
            'label' => $label
        ]);

        foreach ($styleEffects as $effectConfiguration) {
            // Invoke effect alter hook for altering the effect configuration
            // by 3rd party applications.
            $this->moduleHandler->invokeAll(self::EFFECT_ALTER_HOOK, [&$effectConfiguration]);

            // If there is a modifier modify the width and height.
            if ($modifier !== null && isset($effectConfiguration['data']['width'])) {
                $effectConfiguration['data']['width'] = $effectConfiguration['data']['width'] * $modifier;
            }
            if ($modifier !== null && isset($effectConfiguration['data']['height'])) {
                $effectConfiguration['data']['height'] = $effectConfiguration['data']['height'] * $modifier;
            }

            // Add image effect to style.
            $imageStyle->addImageEffect($effectConfiguration);
        }

        // Invoke pre save hook for module to interact with breakgen.
        $this->moduleHandler->invokeAll(self::IMAGE_STYLE_ALTER_HOOK, [&$imageStyle]);

        // Save the image style.
        $imageStyle->save();
    }
}