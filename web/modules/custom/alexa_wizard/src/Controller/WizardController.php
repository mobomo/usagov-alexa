<?php

namespace Drupal\alexa_wizard\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

/**
 * Controller to handle route theming for the Alexa Wizard.
 */
class WizardController extends ControllerBase {
  
  /**
   * Sets theme and variables needed to generate a wizard page.
   * 
   * @param Node $wizard
   *   The wizard being viewed.
   * 
   * @return array
   */
  public function wizardPage(Node $wizard) : array {
    // Generate the flattened wizard tree
    $wizardTree = \Drupal::service('alexa_wizard.wizard')->buildFlattenedWizardTreeFromNode( $wizard );
    // Determine the Wizard update path.
    // TODO make this absolute instead of relative.
    $wizardUpdatePath = \Drupal\Core\Url::fromRoute('alexa_wizard.wizard_tree.update.v1')->toString();

    // Set the theme to allow for a custom template to be loaded, set JS variables,
    // and attach the necessary library.
    return [
      '#theme' => 'alexa_wizard_wizard',
      '#attached' => [
        'library' => [
          'alexa_wizard/alexa_wizard.react_wizard_viewer',
        ],
        // JS variables go here
        'drupalSettings' => [
          'wizardTree' => $wizardTree,
          'wizardUpdateUrl' => $wizardUpdatePath
        ]
      ]
    ];
  }

  /**
   * Callback function to set the Wizard Page's title.
   * 
   * @param Node $wizard
   *   The wizard being viewed.
   * 
   * @return string
   *   The title of the current webpage.
   */
  public function wizardPageTitle(Node $wizard) : string {
    $title = '';

    if ( $wizard != null ) {
      $title = $wizard->getTitle();
    }

    return $title;
  }

  /**
   * Sets theme and variables needed to create the wizard selection page.
   * 
   * @return array
   */
  public function wizardSelectPage() : array {
    // Load all wizards
    $availableWizards = \Drupal::service('alexa_wizard.wizard')->getAllWizards();
    $reactWizards = [];
    // Format them for the front end
    // TODO change this from default Drupal data format to a more friendly format.
    foreach ($availableWizards as $key => $val) {
      $reactWizards[$key] = $val->toArray();
    }

    // Set the theme, JS variables, and library
    return [
      '#theme' => 'alexa_wizard_wizard_select',
      '#attached' => [
        'library' => [
          'alexa_wizard/alexa_wizard.react_wizard_select',
        ],
        // JS variables go here
        'drupalSettings' => [
          'wizards' => $reactWizards
        ],
        '#wizards' => $availableWizards
      ]
    ];
  }

}