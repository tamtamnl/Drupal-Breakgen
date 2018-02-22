<?php

namespace Drupal\breakgen\Service;

use Drupal\breakpoint\BreakpointManager;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Theme\ThemeManager;
use Drupal\image\Entity\ImageStyle;
use Drupal\image\ImageEffectManager;

class ImageStyleGenerator
{

    private $breakpointManager;
    private $imageEffectManager;
    private $entityTypeManager;
    private $themeManager;

    public function __construct(
        BreakpointManager $breakpointManager,
        ImageEffectManager $imageEffectManager,
        EntityTypeManager $entityTypeManager,
        ThemeManager $themeManager
    ) {
        $this->breakpointManager = $breakpointManager;
        $this->imageEffectManager = $imageEffectManager;
        $this->entityTypeManager = $entityTypeManager;
        $this->themeManager = $themeManager;
    }

    public function generate($theme = null)
    {
        if($theme === null){
            $theme = $this->themeManager->getActiveTheme()->getName();
        }

        $this->clear();
        $breakpoints = $this->breakpointManager->getBreakpointsByGroup($theme);
        foreach ($breakpoints as $key => $breakpoint) {
            $this->generateImagesStyles($key, $breakpoint);
        }
    }

    public function clear()
    {
        $imageStyles = $this->entityTypeManager->getStorage('image_style')
            ->getQuery()
            ->condition('name', "breakgen", 'CONTAINS')
            ->execute();

        $imageStyles = $this->entityTypeManager->getStorage('image_style')
            ->loadMultiple($imageStyles);

        $this->entityTypeManager->getStorage('image_style')->delete($imageStyles);
    }

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

                if(isset($data['percentages'])){
                    foreach ($data['percentages'] as $percentage) {
                        $modifier = str_replace('%', '', $percentage) / 100;
                        $percentage = str_replace('%', '', $percentage);

                        $this->generateImageStyle(
                            $key . ".$percentage",
                            $breakpoint->getLabel()->__toString() . " ($percentage%)",
                            $groupName,
                            $data['style_effects'],
                            $modifier
                        );
                    }
                }
            }
        }
    }

    private function generateImageStyle(
        $breakpointKey,
        $breakpointLabel,
        $groupName,
        $styleEffects,
        $modifier = null
    ) {
        // Generate machine name
        $machineName = sprintf(
            '%s_%s_%s',
            $breakpointKey,
            'breakgen',
            $groupName
        );

        // Generate label
        $label = sprintf(
            '%s %s',
            $breakpointLabel,
            $groupName
        );

        $imageStyle = ImageStyle::create(
            [
                'name' => $machineName,
                'label' => $label
            ]
        );


        foreach ($styleEffects as $styleData) {
            $imageStyle->addImageEffect($styleData);
            if ($modifier !== null && isset($styleData['data']['width'])) {
                $styleData['data']['width'] = $styleData['data']['width'] * $modifier;
            }
            if ($modifier !== null && isset($styleData['data']['height'])) {
                $styleData['data']['height'] = $styleData['data']['height'] * $modifier;
            }
        }

        $imageStyle->save();
    }
}