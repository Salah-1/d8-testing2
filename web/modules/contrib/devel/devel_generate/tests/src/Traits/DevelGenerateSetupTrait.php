<?php

namespace Drupal\Tests\devel_generate\Traits;

use Drupal\comment\Tests\CommentTestTrait;
use Drupal\Tests\field\Traits\EntityReferenceTestTrait;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Language\Language;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\language\Entity\ConfigurableLanguage;

/**
 * Provides methods to assist Devel Generate testing.
 *
 * Referenced in DevelGenerateBrowserTestBase and DevelGenerateCommandsTest.
 */
trait DevelGenerateSetupTrait {

  use CommentTestTrait;
  use EntityReferenceTestTrait;

  /**
   * Vocabulary for testing.
   *
   * @var \Drupal\taxonomy\VocabularyInterface
   */
  protected $vocabulary;

  /**
   * General set-up for all tests.
   */
  public function setUpData() {
    $entity_type_manager = $this->container->get('entity_type.manager');
    // Create Basic page and Article node types.
    if ($this->profile != 'standard') {
      $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic Page']);
      $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);
      $this->addDefaultCommentField('node', 'article');
    }

    // Enable translation for article content type (but not for page).
    \Drupal::service('content_translation.manager')->setEnabled('node', 'article', TRUE);
    // Create languages for generated translations.
    ConfigurableLanguage::createFromLangcode('ca')->save();
    ConfigurableLanguage::createFromLangcode('de')->save();
    ConfigurableLanguage::createFromLangcode('fr')->save();

    // Creating a vocabulary to associate taxonomy terms generated.
    $this->vocabulary = Vocabulary::create([
      'name' => $this->randomMachineName(),
      'description' => $this->randomMachineName(),
      'vid' => mb_strtolower($this->randomMachineName()),
      'langcode' => Language::LANGCODE_NOT_SPECIFIED,
      'weight' => mt_rand(0, 10),
    ]);
    $this->vocabulary->save();
    // Enable translation for terms in this vocabulary.
    \Drupal::service('content_translation.manager')->setEnabled('taxonomy_term', $this->vocabulary->id(), TRUE);

    // Creates a field of an entity reference field storage on article.
    $field_name = 'taxonomy_' . $this->vocabulary->id();

    $handler_settings = [
      'target_bundles' => [
        $this->vocabulary->id() => $this->vocabulary->id(),
      ],
      'auto_create' => TRUE,
    ];
    $this->createEntityReferenceField('node', 'article', $field_name, NULL, 'taxonomy_term', 'default', $handler_settings, FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);

    $entity_type_manager->getStorage('entity_form_display')
      ->load('node.article.default')
      ->setComponent($field_name, [
        'type' => 'options_select',
      ])
      ->save();

    $entity_type_manager->getStorage('entity_view_display')
      ->load('node.article.default')
      ->setComponent($field_name, [
        'type' => 'entity_reference_label',
      ])
      ->save();
  }

}
