<?php


/**
 * Implements hook_drush_command().
 */
function breakgen_drush_command()
{
    return [
        'breakpoint-generate' => [
            'description' => 'This command generates breakpoints for Drupal 8 out of the theme file.',
            'aliases' => ['bg'],
            'arguments' => [
                'theme' => 'An enabled theme to use for the breakpoint generation'
            ]
        ]
    ];

}

function drush_breakgen_breakpoint_generate($theme = null)
{
    $batchProvider = \Drupal::service('dd_breakgen.generator.breakpoint');
    $batchProvider->generate($theme);
}
