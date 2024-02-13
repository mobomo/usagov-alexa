<?php

namespace Drupal\alexa2_demo\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

class WizardController extends ControllerBase {
    
    public function wizardPage(Node $wizard) {
        // return [
        //     "#type" => "markup",
        //     "#markup" => $this->buildWizardTree($wizard),
        // ];
        // For rendering the front-end of the wizard tree, things we need to know are:
        // Title of the current Wizard/Wizard Step
        // Children of the current Wizard/Wizard Step (titles and ids)
        $wizardTree = \Drupal::service('alexa2_demo.wizard_tree')->buildWizardTreeFromNode($wizard, false);
        // $wizardStepForm = \Drupal::formBuilder()->getForm('Drupal\node\Form\SimpleForm');
        error_log(json_encode($wizardTree));

        return [
            '#theme' => 'alexa2_demo_wizard',
            '#attached' => [
                'library' => [
                    'alexa2_demo/alexa2_demo.react_wizard_viewer',
                ],
                // JS variables go here
                'drupalSettings' => [
                    'wizardTree' => $wizardTree
                ]
            ],
            '#wizard_tree' => $wizardTree,
            '#wizard_step_form' => $wizardStepForm
        ];
    }

    public function wizardPageTitle(Node $wizard) {
        $title = '';

        if ( $wizard != null ) {
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
        $markup = "<h3>Sorry, there are no available wizards.</h3><a href='$wizardCreatePath'><button class='btn btn-primary'>Create a New Wizard</button></a>";
        if ( !empty($availableWizards) ) {
            $markup = '<h3>Select the wizard you want to manage.</h3>';
            $markup .= "<ul style='list-style-type: none;'>";
            $availableWizards = Node::loadMultiple($availableWizards);
            $first = true;
            foreach ($availableWizards as $wizard) {
                $selected = $first ? 'checked' : '';
                $optionId = $wizard->id();
                $optionName = $wizard->getTitle();
                $wizardPath = \Drupal\Core\Url::fromRoute('alexa2_demo.wizards.wizard', ['wizard' => $optionId])->toString();
                $markup .= "
                    <li>
                        <label class='container' style='cursor: pointer;'>
                            <input type='radio' name='wizard' id='option_$optionId' wizard-path='$wizardPath' name='radio' style='cursor: pointer;' $selected>
                            &nbsp;$optionName
                        </label>
                    </li>
                ";
            }
            $markup .= "</ul>";
            $markup .= "<button class='btn btn-primary' onclick='selectWizard()' style='margin-right:10px;'>
                            <b>Submit</b>
                        </button><a href='$wizardCreatePath'><button class='btn btn-primary'><b>Create a New Wizard</b></button></a>";
            $markup .= "<script>
                function selectWizard() {
                    let selectedWizardPath = document.querySelector('input[name=\"wizard\"]:checked').getAttribute('wizard-path');
                    window.location.href = selectedWizardPath;
                }
            </script>";
        }
        // return [
        //     "#type" => "markup",
        //     "#markup" => t($markup),
        // ];
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