<?php

namespace Drupal\alexa2_demo\Services;

use Drupal\node\Entity\Node;

class WizardTreeService {

  public function buildWizardTree() {
    
    $wizardTree = [];
    $wizards = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'wizard')
      ->accessCheck(TRUE)
      ->execute();
    $wizards = Node::loadMultiple($wizards);
    // $wizardSteps = \Drupal::entityQuery('node')
    //   ->condition('status', 1)
    //   ->condition('type', 'wizard_step')
    //   ->accessCheck(TRUE)
    //   ->execute();
    // $wizardSteps = Node::loadMultiple($wizardSteps);
    foreach ($wizards as $wizard) {
      $wizardTree[$wizard->id()] = $this->buildWizardStep($wizard);
    }

    return $wizardTree;
  }

  public function buildWizardTreeFromNodeId( $startNodeId ) {
    return $this->buildWizardTreeFromNode( Node::load($startNodeId) );
  }

  public function buildWizardTreeFromNode( Node $wizard ) {
    $wizardTree = [];
    $wizardSteps = $wizard->get('field_wizard_step')->referencedEntities();
    foreach ($wizardSteps as $wizardStep) {
      $wizardTree[$wizardStep->id()] = $this->buildWizardStep($wizardStep);
    }

    return $wizardTree;
  }

  protected function buildWizardStep( $wizardStep ) {

    if ( $wizardStep == null ) {
      return null;
    }

    $treeNode = [
      'title' => $wizardStep->getTitle(),
      'id' => $wizardStep->id(),
      'children' => [],
      'original_node' => $wizardStep,
      'original_node_data' => $wizardStep->toArray()
    ];
    $children = $wizardStep->get('field_wizard_step')->referencedEntities();

    foreach ($children as $child) {
      $childId = $child->id();
      $treeNode['children'][$childId] = $this->buildWizardStep( $child );
    }

    return $treeNode;

  }

}