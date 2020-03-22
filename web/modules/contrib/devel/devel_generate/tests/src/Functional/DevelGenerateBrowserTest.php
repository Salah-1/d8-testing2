<?php

namespace Drupal\Tests\devel_generate\Functional;

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;

/**
 * Tests the logic to generate data.
 *
 * @group devel_generate
 */
class DevelGenerateBrowserTest extends DevelGenerateBrowserTestBase {

  use MediaTypeCreationTrait;

  /**
   * Tests generating users.
   */
  public function testDevelGenerateUsers() {
    $edit = [
      'num' => 4,
    ];
    $this->drupalPostForm('admin/config/development/generate/user', $edit, 'Generate');
    $this->assertText('4 users created.');
    $this->assertText('Generate process complete.');

    // Tests that if no content types are selected an error message is shown.
    $edit = [
      'num' => 4,
      'title_length' => 4,
    ];
    $this->drupalPostForm('admin/config/development/generate/content', $edit, 'Generate');
    $this->assertText('Please select at least one content type');
  }

  /**
   * Tests generating content.
   */
  public function testDevelGenerateContent() {
    // First we create a node in order to test the Delete content checkbox.
    $this->drupalCreateNode(['type' => 'article']);

    $edit = [
      'num' => 4,
      'kill' => TRUE,
      'node_types[article]' => TRUE,
      'time_range' => 604800,
      'max_comments' => 3,
      'title_length' => 4,
      'add_alias' => 1,
    ];
    $this->drupalPostForm('admin/config/development/generate/content', $edit, 'Generate');
    $this->assertSession()->pageTextContains('Deleted 1 node');
    $this->assertSession()->pageTextContains('Created 4 nodes');
    $this->assertSession()->pageTextContains('Generate process complete.');
    $this->assertSession()->pageTextNotContains('translations');

    // Tests that nodes have been created in the generation process.
    $nodes = Node::loadMultiple();
    $this->assert(count($nodes) == 4, 'Nodes generated successfully.');

    // Tests url alias for the generated nodes.
    foreach ($nodes as $node) {
      $alias = 'node-' . $node->id() . '-' . $node->bundle();
      $this->drupalGet($alias);
      $this->assertSession()->statusCodeEquals('200');
      $this->assertSession()->pageTextContains($node->getTitle(), 'Generated url alias for the node works.');
    }

    $edit = [
      'num' => 3,
      'kill' => TRUE,
      'node_types[article]' => TRUE,
      'add_language[]' => ['en'],
      'translate_language[]' => ['de', 'ca'],
    ];
    $this->drupalPostForm('admin/config/development/generate/content', $edit, 'Generate');
    $this->assertSession()->pageTextContains('Deleted 4 nodes');
    $this->assertSession()->pageTextContains('Created 3 nodes');
    // Two translations for each node makes six.
    $this->assertSession()->pageTextContains('Created 6 node translations');
    $articles = \Drupal::entityQuery('node')->execute();
    $this->assertCount(3, $articles);
    $node = Node::load(end($articles));
    $this->assertTrue($node->hasTranslation('de'));
    $this->assertTrue($node->hasTranslation('ca'));
    $this->assertFalse($node->hasTranslation('fr'));

    // The 'page' content type is not enabled for translation.
    $edit = [
      'num' => 2,
      'kill' => TRUE,
      'node_types[page]' => TRUE,
      'add_language[]' => ['en'],
      'translate_language[]' => ['fr'],
    ];
    $this->drupalPostForm('admin/config/development/generate/content', $edit, 'Generate');
    $this->assertSession()->pageTextNotContains('Deleted');
    $this->assertSession()->pageTextContains('Created 2 nodes');
    $this->assertSession()->pageTextNotContains('node translations');
    // Check that 'kill' has not deleted the articles.
    $this->assertCount(5, \Drupal::entityQuery('node')->execute());
    $pages = \Drupal::entityQuery('node')->condition('type', 'page')->execute();
    $this->assertCount(2, $pages);
    $node = Node::load(end($pages));
    $this->assertFalse($node->hasTranslation('fr'));

    // Create content with add-content-label option.
    $edit = [
      'num' => 5,
      'kill' => TRUE,
      'node_types[article]' => TRUE,
      'add_type_label' => TRUE,
    ];
    $this->drupalPostForm('admin/config/development/generate/content', $edit, 'Generate');
    $this->assertSession()->pageTextContains('Created 5 nodes');
    $this->assertSession()->pageTextContains('Generate process complete');

    // Count the articles created in the generation process.
    $nodes = \Drupal::entityQuery('node')->condition('type', 'article')->execute();
    $this->assertCount(5, $nodes);

    // Load the final node and verify that the title starts with the label.
    $node = Node::load(end($nodes));
    $this->assertEquals('Article - ', substr($node->title->value, 0, 10));
  }

  /**
   * Tests generating terms.
   */
  public function testDevelGenerateTerms() {
    // Generate terms.
    $edit = [
      'vids[]' => $this->vocabulary->id(),
      'num' => 5,
      'title_length' => 12,
    ];
    $this->drupalPostForm('admin/config/development/generate/term', $edit, 'Generate');
    $this->assertSession()->pageTextContains('Created the following new terms: ');
    $this->assertSession()->pageTextNotContains('translations');
    $this->assertSession()->pageTextContains('Generate process complete.');
    $this->assertCount(5, \Drupal::entityQuery('taxonomy_term')->execute());

    // Generate terms with translations.
    $edit = [
      'vids[]' => $this->vocabulary->id(),
      'num' => 3,
      'add_language[]' => ['en'],
      'translate_language[]' => ['ca'],
    ];
    $this->drupalPostForm('admin/config/development/generate/term', $edit, 'Generate');
    $this->assertSession()->pageTextContains('Created 3 term translations');
    // Not using 'kill' so there should be 8 terms.
    $terms = \Drupal::entityQuery('taxonomy_term')->execute();
    $this->assertCount(8, $terms);
    // Check the translations created (and not created).
    $term = Term::load(end($terms));
    $this->assertTrue($term->hasTranslation('ca'));
    $this->assertFalse($term->hasTranslation('de'));
    $this->assertFalse($term->hasTranslation('fr'));
  }

  /**
   * Tests generating vocabularies.
   */
  public function testDevelGenerateVocabs() {
    $edit = [
      'num' => 5,
      'title_length' => 12,
      'kill' => TRUE,
    ];
    $this->drupalPostForm('admin/config/development/generate/vocabs', $edit, 'Generate');
    $this->assertSession()->pageTextContains('Created the following new vocabularies: ');
    $this->assertSession()->pageTextContains('Generate process complete.');
  }

  /**
   * Tests generating menus.
   */
  public function testDevelGenerateMenus() {
    $edit = [
      'num_menus' => 5,
      'num_links' => 7,
      'title_length' => 12,
      'link_types[node]' => 1,
      'link_types[front]' => 1,
      'link_types[external]' => 1,
      'max_depth' => 4,
      'max_width' => 6,
      'kill' => 1,
    ];
    $this->drupalPostForm('admin/config/development/generate/menu', $edit, 'Generate');
    $this->assertSession()->pageTextContains('Created the following new menus: ');
    $this->assertSession()->pageTextContains('Created 7 new menu links');
    $this->assertSession()->pageTextContains('Generate process complete.');
  }

  /**
   * Tests generating media.
   */
  public function testDevelGenerateMedia() {
    // As the 'media' plugin has a dependency on 'media' module, the plugin is
    // not generating a route to the plugin form.
    $this->drupalGet('admin/config/development/generate/media');
    $this->assertSession()->statusCodeEquals(404);
    // Enable the module and retry.
    \Drupal::service('module_installer')->install(['media']);
    $this->getSession()->reload();
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Generate media');

    // Create two media types.
    $media_type1 = $this->createMediaType('image');
    $media_type2 = $this->createMediaType('audio_file');

    // Creating media items (non-batch mode).
    $edit = [
      'num' => 5,
      'name_length' => 12,
      "media_types[{$media_type1->id()}]" => 1,
      "media_types[{$media_type2->id()}]" => 1,
      'kill' => 1,
    ];
    $this->drupalPostForm('admin/config/development/generate/media', $edit, 'Generate');
    $this->assertSession()->pageTextContains('Finished creating 5 media items.');
    $this->assertSession()->pageTextContains('Generate process complete.');
    $this->assertCount(5, \Drupal::entityQuery('media')->execute());

    // Creating media items (batch mode).
    $edit = [
      'num' => 56,
      'name_length' => 6,
      "media_types[{$media_type1->id()}]" => 1,
      "media_types[{$media_type2->id()}]" => 1,
      'kill' => 1,
    ];
    $this->drupalPostForm('admin/config/development/generate/media', $edit, 'Generate');
    $this->assertSession()->pageTextContains('Finished 56 elements created successfully.');
    $this->assertSession()->pageTextContains('Generate process complete.');
    $this->assertCount(56, \Drupal::entityQuery('media')->execute());
  }

  /**
   * Tests generating content in batch mode.
   */
  public function testDevelGenerateBatchContent() {
    // For 50 or more nodes, the processing will be done via batch.
    $edit = [
      'num' => 55,
      'kill' => TRUE,
      'node_types[article]' => TRUE,
      'node_types[page]' => TRUE,
    ];
    $this->drupalPostForm('admin/config/development/generate/content', $edit, 'Generate');
    $this->assertSession()->pageTextContains('Finished 55 elements created successfully.');
    $this->assertSession()->pageTextContains('Generate process complete.');

    // Tests that the expected number of nodes have been created.
    $count = count(Node::loadMultiple());
    $this->assertEquals(55, $count, sprintf('The expected total number of nodes is %s, found %s', 55, $count));

    // Create nodes with translations via batch.
    $edit = [
      'num' => 52,
      'kill' => TRUE,
      'node_types[article]' => TRUE,
      'node_types[page]' => TRUE,
      'add_language[]' => ['en'],
      'translate_language[]' => ['de', 'ca'],
    ];
    $this->drupalPostForm('admin/config/development/generate/content', $edit, 'Generate');
    $this->assertCount(52, \Drupal::entityQuery('node')->execute());
    // Only aticles will have translations so get that number.
    $articles = \Drupal::entityQuery('node')->condition('type', 'article')->execute();
    $this->assertSession()->pageTextContains(sprintf('Finished 52 elements and %s translations created successfully.', 2 * count($articles)));

    // Generate only articles.
    $edit = [
      'num' => 60,
      'kill' => TRUE,
      'node_types[article]' => TRUE,
      'node_types[page]' => FALSE,
    ];
    $this->drupalPostForm('admin/config/development/generate/content', $edit, 'Generate');

    // Tests that all the created nodes were of the node type selected.
    $nodeStorage = $this->container->get('entity_type.manager')->getStorage('node');
    $type = 'article';
    $count = $nodeStorage->getQuery()
      ->condition('type', $type)
      ->count()
      ->execute();
    $this->assertEquals(60, $count, sprintf('The expected number of %s is %s, found %s', $type, 60, $count));

  }

}
