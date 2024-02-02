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
        return [
            '#theme' => 'alexa2_demo_wizard',
        ];
    }

    public function wizardPageTitle(Node $wizard) {
        $title = '';

        if ( $wizard ) {
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
        return [
            '#theme' => 'alexa2_demo_wizard_select',
            // '#hello' => 'test',
        ];
    }

    protected function buildWizardTree(Node $wizard) {
        $wizardTree = '<p>Invalid Wizard Selection</p>';

        if ( $wizard != null ) {
            // TODO build wizard steps here.
        }

        return t($wizardTree);
    }

}