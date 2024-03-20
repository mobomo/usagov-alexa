<?php

namespace Drupal\alexa2_demo\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

class WizardController extends ControllerBase {

    public function wizardPage(string $wizardId) {
        // $wizardTree = \Drupal::service('alexa2_demo.wizard_tree')->buildWizardTreeFromNode($wizard, false);
        $wizardTree = \Drupal::service('alexa2_demo.wizard_tree')->buildFlattenedWizardTreeFromNodeId( $wizardId );
        $wizardUpdatePath = \Drupal\Core\Url::fromRoute('alexa2_demo.wizard_tree.update.v1')->toString();

        return [
            '#theme' => 'alexa2_demo_wizard',
            '#attached' => [
                'library' => [
                    'alexa2_demo/alexa2_demo.react_wizard_viewer',
                ],
                // JS variables go here
                'drupalSettings' => [
                    'wizardTree' => $wizardTree,
                    'wizardUpdateUrl' => $wizardUpdatePath
                ]
            ],
            '#wizard_tree' => $wizardTree,
        ];
    }

    public function wizardPageTitle(string $wizardId) {
        $wizard = Node::load($wizardId);
        $title = '';

        if ( $wizard != null ) {
            // TODO better page title
            $title = $wizard->getTitle();
        }

        return $title;
    }

    public function wizardSelectPage() {
        $wizardCreatePath = \Drupal\Core\Url::fromRoute('node.add', ['node_type' => 'wizard'])->toString();
        $availableWizards = \Drupal::entityQuery('node')
            ->condition('status', 1)
            ->condition('type', 'wizard')
            ->accessCheck(TRUE)
            ->execute();
        if ( !empty($availableWizards) ) {
            $availableWizards = Node::loadMultiple($availableWizards);
        }
        $reactWizards = [];
        foreach ($availableWizards as $key => $val) {
            $reactWizards[$key] = $val->toArray();
        }

        return [
            '#theme' => 'alexa2_demo_wizard_select',
            '#attached' => [
                'library' => [
                    'alexa2_demo/alexa2_demo.react_wizard_select',
                ],
                // JS variables go here
                'drupalSettings' => [
                    'wizards' => $reactWizards
                ]
            ],
            '#wizards' => $availableWizards
        ];
    }

}
