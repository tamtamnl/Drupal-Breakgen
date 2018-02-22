<?php


/**
 * Implements hook_drush_command().
 */
function dd_breakgen_drush_command()
{
    return [
        'dd-breakpoint-generate' => [
            'description' => 'This command generates breakpoints for Drupal 8 out of the theme file.',
            'aliases' => ['ddbg'],
            'arguments' => [
                'theme' => 'An enabled theme to use for the breakpoint generation'
            ]
        ]
    ];

}

function drush_dd_breakgen_dd_breakpoint_generate($theme = null)
{
    $batchProvider = Drupal::service('dd_breakgen.generator.breakpoint');
    $batchProvider->generate($theme);
}
