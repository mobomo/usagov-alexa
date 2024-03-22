<?php

namespace Drupal\Tests\alexa2_demo\Functional;

use Drupal\Tests\BrowserTestBase;

// use Drupal\Core\Database\Database;
// use Drupal\Core\Language\LanguageInterface;
// use Drupal\node\Entity\Node;
// use Drupal\Tests\node\Traits\ContentTypeCreationTrait;

/**
 * Tests for the wizard tree service.
 * 
 * @group alexa2_demo
 */
class Alexa2DemoWizardTreeServiceTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
      'alexa2',
      'alexa2_demo',
      'user',
      'system',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    // TODO create content type, wizard test base, etc.
    parent::setUp();

    $web_user = $this->drupalCreateUser([
      'create page content',
      'create wizard_step content',
      'create wizard content',
      'edit own wizard content',
      'delete own wizard content',
      'edit own wizard_step content',
      'delete own wizard_step content',
    ]);
    $this->drupalLogin($web_user);
  }

  /**
   * buildWizardTree
   * buildWizardTreeFromNodeId
   * buildWizardTreeFromNode
   * buildFlattenedWizardTree
   * buildFlattenedWizardTreeFromNodeId
   * buildFlattenedWizardTreeFromNode
   * buildWizardStep
   * buildWizardDataFromStep
   * saveWizardTree
   * validateUserWizardTreePermissions
   * saveWizardTreeNested
   * saveWizardTreeFlattened
   * saveWizardStep
   */

  /**
   * Tests building the entire nested wizard tree.
   */
  public function testBuildNestedWizardTree() {
    $wizard = Node::create([
      'type' => 'wizard',
      'title' => 'Test Wizard',
      'body' => '<prosody>Test wizard body copy</prosody>',
      'field_wizard_primary_utterance' => 'test wizard',
      'field_wizard_aliases' => 'test, wizard test',
      'field_wizard_step' => [],
    ]);
    $wizard2 = Node::create([
      'type' => 'wizard',
      'title' => 'Test Wizard 2',
      'body' => '<prosody>Test wizard 2 body copy</prosody>',
      'field_wizard_primary_utterance' => 'test wizard 2',
      'field_wizard_aliases' => 'test 2, wizard test 2',
      'field_wizard_step' => [],
    ]);
    $wizardSteps = [
      'step_1' => Node::create([
        'type' => 'wizard_step',
        'title' => 'Wizard Step 1',
        'body' => 'Wizard Step 1 body copy',
        'field_wizard_primary_utterance' => 'step 1',
        'field_wizard_aliases' => '1, one',
        'field_wizard_step' => [],
      ]),
      'step_2' => Node::create([
        'type' => 'wizard_step',
        'title' => 'Wizard Step 2',
        'body' => 'Wizard Step 2 body copy',
        'field_wizard_primary_utterance' => 'step 2',
        'field_wizard_aliases' => '2, two',
        'field_wizard_step' => [],
      ]),
      'step_3' => Node::create([
        'type' => 'wizard_step',
        'title' => 'Wizard Step 3',
        'body' => 'Wizard Step 3 body copy',
        'field_wizard_primary_utterance' => 'step 3',
        'field_wizard_aliases' => '3, three',
        'field_wizard_step' => [],
      ]),
      'step_1_substep_1' => Node::create([
        'type' => 'wizard_step',
        'title' => 'Wizard Step 1 Substep 1',
        'body' => 'Wizard Step 1 substep 1 body copy',
        'field_wizard_primary_utterance' => 'step 1 substep 1',
        'field_wizard_aliases' => 'substep 1, substep one',
        'field_wizard_step' => [],
      ]),
      'step_1_substep_2' => Node::create([
        'type' => 'wizard_step',
        'title' => 'Wizard Step 1 Substep 2',
        'body' => 'Wizard Step 1 substep 2 body copy',
        'field_wizard_primary_utterance' => 'step 1 substep 2',
        'field_wizard_aliases' => 'substep 2, substep two',
        'field_wizard_step' => [],
      ]),
      'step_1_substep_1_substep_1' => Node::create([
        'type' => 'wizard_step',
        'title' => 'Wizard Step 1 Substep 1 Substep 1',
        'body' => 'Wizard Step 1 substep 1 substep 1 body copy',
        'field_wizard_primary_utterance' => 'step 1 substep 1 substep 1',
        'field_wizard_aliases' => 'substep 1 substep 1, substep one substep one',
        'field_wizard_step' => [],
      ]),
    ];

    // Save in reverse order (most nested children first) because
    // parents need the child's ID in order to save in the field_wizard_step field.
    $wizardSteps['step_1_substep_1_substep_1']->save();
    $wizardSteps['step_1_substep_1']['field_wizard_step'] = [
      ['target_id' => $wizardSteps['step_1_substep_1_substep_1']->id()],
    ];
    $wizardSteps['step_1_substep_1']->save();
    $wizardSteps['step_1_substep_2']->save();
    $wizardSteps['step_2']->save();
    $wizardSteps['step_3']->save();
    $wizardSteps['step_1']['field_wizard_step'] = [
      ['target_id' => $wizardSteps['step_1_substep_1']->id()],
      ['target_id' => $wizardSteps['step_1_substep_2']->id()],
    ];
    $wizardSteps['step_1']->save();
    $wizard['field_wizard_step'] = [
      ['target_id' => $wizardSteps['step_1']->id()],
      ['target_id' => $wizardSteps['step_2']->id()],
      ['target_id' => $wizardSteps['step_3']->id()]
    ];
    $wizard->save();
    $wizard2->save();

    // TODO wizardTreeService getname function
    // All wizard steps are created. Check that it generates the expected data format.
    $expected = [];
    $expected[$wizard->id()] = $this->wizardNodeToArray($wizard);
    $expected[$wizard2->id()] = $this->wizardNodeToArray($wizard2);
    $expected[$wizard->id()]['children'][$wizardSteps['step_1']->id()] = $this->wizardNodeToArray($wizardSteps['step_1']);
    $expected[$wizard->id()]['children'][$wizardSteps['step_2']->id()] = $this->wizardNodeToArray($wizardSteps['step_2']);
    $expected[$wizard->id()]['children'][$wizardSteps['step_3']->id()] = $this->wizardNodeToArray($wizardSteps['step_3']);
    $expected[$wizard->id()]['children'][$wizardSteps['step_1']->id()]['children'][$wizardSteps['step_1_substep_1']->id()] = $this->wizardNodeToArray($wizardSteps['step_1_substep_1']);
    $expected[$wizard->id()]['children'][$wizardSteps['step_1']->id()]['children'][$wizardSteps['step_1_substep_2']->id()] = $this->wizardNodeToArray($wizardSteps['step_1_substep_2']);
    $expected[$wizard->id()]['children'][$wizardSteps['step_1']->id()]['children'][$wizardSteps['step_1_substep_1']->id()]['children'][$wizardSteps['step_1_substep_1_substep_1']] = $this->wizardNodeToArray($wizardSteps['step_1_substep_1_substep_1']);

    $expected = json_decode(json_encode($expected));

    $this->assertEquals($expected, \Drupal::service('alexa2_demo.wizard_tree')->buildWizardTree());
  }

  private function wizardNodeToArray($wizard) {
    $arr = [];

    if ( $wizard != null ) {
      $arr = [
        'name' => preg_replace('/[ -]/', '_', strtolower($wizard->getTitle() ?? 'wizard_step_' . $wizard->id())),
        'title' => $wizard->getTitle(),
        'id' => $wizard->id(),
        'body' => $wizard->get('body')->getValue(),
        'primaryUtterance' => $wizard->get('field_wizard_primary_utterance')->getValue(),
        'aliases' => $wizard->get('field_wizard_aliases')->getValue(),
        'children' => []
      ];
    }

    return $arr;
  }
}

// class NodeCreationTest extends NodeTestBase {

//   use ContentTypeCreationTrait;


//   /**
//    * Creates a "Basic page" node and verifies its consistency in the database.
//    */
//   public function testNodeCreation() {
//     $node_type_storage = \Drupal::entityTypeManager()->getStorage('node_type');

//     // Test /node/add page with only one content type.
//     $node_type_storage->load('article')->delete();
//     $this->drupalGet('node/add');
//     $this->assertSession()->statusCodeEquals(200);
//     $this->assertSession()->addressEquals('node/add/page');
//     // Create a node.
//     $edit = [];
//     $edit['title[0][value]'] = $this->randomMachineName(8);
//     $edit['body[0][value]'] = $this->randomMachineName(16);
//     $this->drupalGet('node/add/page');
//     $this->submitForm($edit, 'Save');

//     // Check that the Basic page has been created.
//     $this->assertSession()->pageTextContains('Basic page ' . $edit['title[0][value]'] . ' has been created.');

//     // Verify that the creation message contains a link to a node.
//     $this->assertSession()->elementExists('xpath', '//div[@data-drupal-messages]//a[contains(@href, "node/")]');

//     // Check that the node exists in the database.
//     $node = $this->drupalGetNodeByTitle($edit['title[0][value]']);
//     $this->assertNotEmpty($node, 'Node found in database.');

//     // Verify that pages do not show submitted information by default.
//     $this->drupalGet('node/' . $node->id());
//     $this->assertSession()->pageTextNotContains($node->getOwner()->getAccountName());
//     $this->assertSession()->pageTextNotContains($this->container->get('date.formatter')->format($node->getCreatedTime()));

//     // Change the node type setting to show submitted by information.
//     /** @var \Drupal\node\NodeTypeInterface $node_type */
//     $node_type = $node_type_storage->load('page');
//     $node_type->setDisplaySubmitted(TRUE);
//     $node_type->save();

//     $this->drupalGet('node/' . $node->id());
//     $this->assertSession()->pageTextContains($node->getOwner()->getAccountName());
//     $this->assertSession()->pageTextContains($this->container->get('date.formatter')->format($node->getCreatedTime()));

//     // Check if the node revision checkbox is not rendered on node creation form.
//     $admin_user = $this->drupalCreateUser([
//       'administer nodes',
//       'create page content',
//     ]);
//     $this->drupalLogin($admin_user);
//     $this->drupalGet('node/add/page');
//     $this->assertSession()->fieldNotExists('edit-revision', NULL);

//     // Check that a user with administer content types permission is not
//     // allowed to create content.
//     $content_types_admin = $this->drupalCreateUser(['administer content types']);
//     $this->drupalLogin($content_types_admin);
//     $this->drupalGet('node/add/page');
//     $this->assertSession()->statusCodeEquals(403);
//   }

//   /**
//    * Verifies that a transaction rolls back the failed creation.
//    */
//   public function testFailedPageCreation() {
//     // Create a node.
//     $edit = [
//       'uid'      => $this->loggedInUser->id(),
//       'name'     => $this->loggedInUser->name,
//       'type'     => 'page',
//       'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
//       'title'    => 'testing_transaction_exception',
//     ];

//     try {
//       // An exception is generated by node_test_exception_node_insert() if the
//       // title is 'testing_transaction_exception'.
//       Node::create($edit)->save();
//       $this->fail('Expected exception has not been thrown.');
//     }
//     catch (\Exception $e) {
//       // Expected exception; just continue testing.
//     }

//     // Check that the node does not exist in the database.
//     $node = $this->drupalGetNodeByTitle($edit['title']);
//     $this->assertFalse($node);

//     // Check that the rollback error was logged.
//     $records = static::getWatchdogIdsForTestExceptionRollback();
//     // Verify that the rollback explanatory error was logged.
//     $this->assertNotEmpty($records);
//   }

//   /**
//    * Creates an unpublished node and confirms correct redirect behavior.
//    */
//   public function testUnpublishedNodeCreation() {
//     // Set the front page to the test page.
//     $this->config('system.site')->set('page.front', '/test-page')->save();

//     // Set "Basic page" content type to be unpublished by default.
//     $fields = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', 'page');
//     $fields['status']->getConfig('page')
//       ->setDefaultValue(FALSE)
//       ->save();

//     // Create a node.
//     $edit = [];
//     $edit['title[0][value]'] = $this->randomMachineName(8);
//     $edit['body[0][value]'] = $this->randomMachineName(16);
//     $this->drupalGet('node/add/page');
//     $this->submitForm($edit, 'Save');

//     // Check that the user was redirected to the home page.
//     $this->assertSession()->addressEquals('');
//     $this->assertSession()->pageTextContains('Test page text');

//     // Confirm that the node was created.
//     $this->assertSession()->pageTextContains('Basic page ' . $edit['title[0][value]'] . ' has been created.');

//     // Verify that the creation message contains a link to a node.
//     $this->assertSession()->elementExists('xpath', '//div[@data-drupal-messages]//a[contains(@href, "node/")]');
//   }

//   /**
//    * Creates nodes with different authored dates.
//    */
//   public function testAuthoredDate() {
//     $now = \Drupal::time()->getRequestTime();
//     $admin = $this->drupalCreateUser([], NULL, TRUE);
//     $this->drupalLogin($admin);

//     // Create a node with the default creation date.
//     $edit = [
//       'title[0][value]' => $this->randomMachineName(8),
//       'body[0][value]' => $this->randomMachineName(16),
//     ];
//     $this->drupalGet('node/add/page');
//     $this->submitForm($edit, 'Save');
//     $node = $this->drupalGetNodeByTitle($edit['title[0][value]']);
//     $this->assertNotNull($node->getCreatedTime());

//     // Create a node with the custom creation date in the past.
//     $date = $now - 86400;
//     $edit = [
//       'title[0][value]' => $this->randomMachineName(8),
//       'body[0][value]' => $this->randomMachineName(16),
//       'created[0][value][date]' => date('Y-m-d', $date),
//       'created[0][value][time]' => date('H:i:s', $date),
//     ];
//     $this->drupalGet('node/add/page');
//     $this->submitForm($edit, 'Save');
//     $node = $this->drupalGetNodeByTitle($edit['title[0][value]']);
//     $this->assertEquals($date, $node->getCreatedTime());

//     // Create a node with the custom creation date in the future.
//     $date = $now + 86400;
//     $edit = [
//       'title[0][value]' => $this->randomMachineName(8),
//       'body[0][value]' => $this->randomMachineName(16),
//       'created[0][value][date]' => date('Y-m-d', $date),
//       'created[0][value][time]' => date('H:i:s', $date),
//     ];
//     $this->drupalGet('node/add/page');
//     $this->submitForm($edit, 'Save');
//     $node = $this->drupalGetNodeByTitle($edit['title[0][value]']);
//     $this->assertEquals($date, $node->getCreatedTime());

//     // Test an invalid date.
//     $edit = [
//       'title[0][value]' => $this->randomMachineName(8),
//       'body[0][value]' => $this->randomMachineName(16),
//       'created[0][value][date]' => '2013-13-13',
//       'created[0][value][time]' => '11:00:00',
//     ];
//     $this->drupalGet('node/add/page');
//     $this->submitForm($edit, 'Save');
//     $this->assertSession()->pageTextContains('The Authored on date is invalid.');
//     $this->assertFalse($this->drupalGetNodeByTitle($edit['title[0][value]']));

//     // Test an invalid time.
//     $edit = [
//       'title[0][value]' => $this->randomMachineName(8),
//       'body[0][value]' => $this->randomMachineName(16),
//       'created[0][value][date]' => '2012-01-01',
//       'created[0][value][time]' => '30:00:00',
//     ];
//     $this->drupalGet('node/add/page');
//     $this->submitForm($edit, 'Save');
//     $this->assertSession()->pageTextContains('The Authored on date is invalid.');
//     $this->assertFalse($this->drupalGetNodeByTitle($edit['title[0][value]']));
//   }

//   /**
//    * Tests the author autocompletion textfield.
//    */
//   public function testAuthorAutocomplete() {
//     $admin_user = $this->drupalCreateUser([
//       'administer nodes',
//       'create page content',
//     ]);
//     $this->drupalLogin($admin_user);

//     $this->drupalGet('node/add/page');

//     // Verify that no autocompletion exists without access user profiles.
//     $this->assertSession()->elementNotExists('xpath', '//input[@id="edit-uid-0-value" and contains(@data-autocomplete-path, "user/autocomplete")]');

//     $admin_user = $this->drupalCreateUser([
//       'administer nodes',
//       'create page content',
//       'access user profiles',
//     ]);
//     $this->drupalLogin($admin_user);

//     $this->drupalGet('node/add/page');

//     // Ensure that the user does have access to the autocompletion.
//     $this->assertSession()->elementsCount('xpath', '//input[@id="edit-uid-0-target-id" and contains(@data-autocomplete-path, "/entity_reference_autocomplete/user/default")]', 1);
//   }

//   /**
//    * Check node/add when no node types exist.
//    */
//   public function testNodeAddWithoutContentTypes() {
//     $this->drupalGet('node/add');
//     $this->assertSession()->statusCodeEquals(200);
//     $this->assertSession()->linkByHrefNotExists('/admin/structure/types/add');

//     // Test /node/add page without content types.
//     foreach (\Drupal::entityTypeManager()->getStorage('node_type')->loadMultiple() as $entity) {
//       $entity->delete();
//     }

//     $this->drupalGet('node/add');
//     $this->assertSession()->statusCodeEquals(403);

//     $admin_content_types = $this->drupalCreateUser([
//       'administer content types',
//     ]);
//     $this->drupalLogin($admin_content_types);

//     $this->drupalGet('node/add');

//     $this->assertSession()->linkByHrefExists('/admin/structure/types/add');
//   }

//   /**
//    * Gets the watchdog IDs of the records with the rollback exception message.
//    *
//    * @return int[]
//    *   Array containing the IDs of the log records with the rollback exception
//    *   message.
//    */
//   protected static function getWatchdogIdsForTestExceptionRollback() {
//     // PostgreSQL doesn't support bytea LIKE queries, so we need to unserialize
//     // first to check for the rollback exception message.
//     $matches = [];
//     $query = Database::getConnection()->select('watchdog', 'w')
//       ->fields('w', ['wid', 'variables'])
//       ->execute();
//     foreach ($query as $row) {
//       $variables = (array) unserialize($row->variables);
//       if (isset($variables['@message']) && $variables['@message'] === 'Test exception for rollback.') {
//         $matches[] = $row->wid;
//       }
//     }
//     return $matches;
//   }

// }

