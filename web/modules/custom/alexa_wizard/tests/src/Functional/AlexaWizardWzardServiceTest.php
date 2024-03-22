<?php

namespace Drupal\Tests\alexa_wizard\Functional;

use Drupal\Tests\BrowserTestBase;

// use Drupal\Core\Database\Database;
// use Drupal\Core\Language\LanguageInterface;
// use Drupal\node\Entity\Node;
// use Drupal\Tests\node\Traits\ContentTypeCreationTrait;

/**
 * Tests for the wizard tree service.
 * 
 * @group alexa_wizard
 */
class AlexaWizardWizardServiceTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
      'alexa2',
      'alexa_wizard',
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

    $this->assertEquals($expected, \Drupal::service('alexa_wizard.wizard')->buildWizardTree());
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