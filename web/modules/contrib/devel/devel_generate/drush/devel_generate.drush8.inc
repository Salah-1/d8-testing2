<?php

/**
 * @file
 * Integration with Drush8. Drush9 commands are in src/Commands.
 */

/**
 * Implements hook_drush_command().
 */
function devel_generate_drush_command() {
  $items['generate-users'] = [
    'callback' => 'drush_devel_generate',
    'callback arguments' => [
      'plugin_id' => 'user',
    ],
    'description' => 'Create users.',
    'arguments' => [
      'num' => 'Number of users to generate.',
    ],
    'options' => [
      'kill' => 'Delete all users before generating new ones.',
      'roles' => 'A comma delimited list of role IDs which should be granted to the new users. No need to specify authenticated user role.',
      'pass' => 'Specify a password to be set for all generated users.',
    ],
    'aliases' => ['genu'],
  ];
  $items['generate-terms'] = [
    'callback' => 'drush_devel_generate',
    'callback arguments' => [
      'plugin_id' => 'term',
    ],
    'description' => 'Create terms in specified vocabulary.',
    'arguments' => [
      'num' => 'Number of terms to insert. Defaults to 10.',
    ],
    'options' => [
      'kill' => 'Delete all terms in specified vocabulary before generating.',
      'bundles' => 'A comma-delimited list of machine names for the vocabularies where terms will be created.',
      'feedback' => 'An integer representing interval for insertion rate logging. Defaults to 1000',
      'pipe' => 'Returns the list of generated terms, one per line.',
      'languages' => 'A comma-separated list of language codes',
      'translations' => 'A comma-separated list of language codes for translations.',
    ],
    'aliases' => ['gent'],

  ];
  $items['generate-vocabs'] = [
    'callback' => 'drush_devel_generate',
    'callback arguments' => [
      'plugin_id' => 'vocabulary',
    ],
    'description' => 'Create vocabularies.',
    'arguments' => [
      'num' => 'Number of vocabularies to create. Defaults to 1.',
    ],
    'options' => [
      'kill' => 'Delete all vocabularies before generating.',
      'pipe' => 'Returns the list of generated vocabularies, one per line.',
    ],
    'aliases' => ['genv'],
  ];
  $items['generate-content'] = [
    'callback' => 'drush_devel_generate',
    'callback arguments' => [
      'plugin_id' => 'content',
    ],
    'description' => 'Create content.',
    'drupal dependencies' => ['devel_generate'],
    'arguments' => [
      'num' => 'Number of nodes to generate.',
      'max_comments' => 'Maximum number of comments to generate.',
    ],
    'options' => [
      'kill' => 'Delete all content before generating new content.',
      'bundles' => 'A comma delimited list of content types to create. Defaults to page,article.',
      'feedback' => 'An integer representing interval for insertion rate logging. Defaults to 1000',
      'skip-fields' => 'A comma delimited list of fields to omit when generating random values',
      'languages' => 'A comma-separated list of language codes',
      'translations' => 'A comma-separated list of language codes for translations.',
    ],
    'aliases' => ['genc'],
  ];
  $items['generate-menus'] = [
    'callback' => 'drush_devel_generate',
    'callback arguments' => [
      'plugin_id' => 'menu',
    ],
    'description' => 'Create menus and menu items.',
    'drupal dependencies' => ['devel_generate'],
    'arguments' => [
      'number_menus' => 'Number of menus to generate. Defaults to 2.',
      'number_links' => 'Number of links to generate. Defaults to 50.',
      'max_depth' => 'Max link depth. Defaults to 3',
      'max_width' => 'Max width of first level of links. Defaults to 8.',
    ],
    'options' => [
      'kill' => 'Delete all previously generated menus and links before generating new menus and links.',
      'pipe' => 'Returns the list of generated menus, one per line.',
    ],
    'aliases' => ['genm'],
  ];
  $items['generate-media'] = [
    'callback' => 'drush_devel_generate',
    'callback arguments' => [
      'plugin_id' => 'media',
    ],
    'description' => 'Create media items.',
    'drupal dependencies' => ['devel_generate', 'media'],
    'arguments' => [
      'num' => 'Number of media items to generate.',
    ],
    'options' => [
      'kill' => 'Delete all media items before generating new media.',
      'media-types' => 'A comma-delimited list of media types to create.',
      'feedback' => 'An integer representing interval for insertion rate logging. Defaults to 1000',
      'skip-fields' => 'A comma delimited list of fields to omit when generating random values.',
      'languages' => 'A comma-separated list of language codes',
    ],
    'aliases' => ['genmd'],
  ];
  return $items;
}

/**
 * Implements drush_hook_COMMAND_validate() for generate_users.
 */
function drush_devel_generate_generate_users_validate() {

  // Array of "Callback arguments" and "command line args".
  $params = func_get_args();
  _drush_plugin_validate($params);
}

/**
 * Implements drush_hook_COMMAND_validate() for generate_terms.
 */
function drush_devel_generate_generate_terms_validate() {

  // Array of "Callback arguments" and "command line args".
  $params = func_get_args();
  _drush_plugin_validate($params);
}

/**
 * Implements drush_hook_COMMAND_validate() for generate_vocabs.
 */
function drush_devel_generate_generate_vocabs_validate() {

  // Array of "Callback arguments" and "command line args".
  $params = func_get_args();
  _drush_plugin_validate($params);
}

/**
 * Implements drush_hook_COMMAND_validate() for generate_content.
 */
function drush_devel_generate_generate_content_validate() {

  // Array of "Callback arguments" and "command line args".
  $params = func_get_args();
  _drush_plugin_validate($params);
}

/**
 * Implements drush_hook_COMMAND_validate() for generate_menus.
 */
function drush_devel_generate_generate_menus_validate() {

  // Array of "Callback arguments" and "command line args".
  $params = func_get_args();
  _drush_plugin_validate($params);
}

/**
 * Implements drush_hook_COMMAND_validate() for generate_media.
 */
function drush_devel_generate_generate_media_validate() {
  // Array of "Callback arguments" and "command line args".
  $params = func_get_args();
  _drush_plugin_validate($params);
}

/**
 * Get the plugin instance and the validated values.
 *
 * Returns an array with a plugin instance for a given id and the validated
 * values ready to be used by the generate() function of the plugin.
 */
function _drush_plugin_validate($params) {
  $instance_and_values = &drupal_static('drush_devel_generate_generate_validate');
  // Getting plugin_id and leaving the command line args.
  $plugin_id = array_shift($params);

  if (!isset($instance_and_values[$plugin_id])) {

    /* @var DevelGeneratePluginManager $manager */
    $manager = \Drupal::service('plugin.manager.develgenerate');

    /* @var DevelGenerateBaseInterface $instance */
    $instance = $manager->createInstance($plugin_id, []);

    // Plugin instance suit params in order to fit for generateElements.
    $values = $instance->validateDrushParams($params);
    $instance_and_values[$plugin_id]['instance'] = $instance;
    $instance_and_values[$plugin_id]['values'] = $values;
  }

  return $instance_and_values[$plugin_id];
}

/**
 * Command callback. Generate a number of elements.
 */
function drush_devel_generate() {
  $params = func_get_args();
  $plugin_id = array_shift($params);
  $instance_and_values = drupal_static('drush_devel_generate_generate_validate');

  /* @var DevelGenerateBaseInterface $instance */
  $instance = $instance_and_values[$plugin_id]['instance'];
  $values = $instance_and_values[$plugin_id]['values'];
  $instance->generate($values);
}
