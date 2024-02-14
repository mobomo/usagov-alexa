<?php

namespace Drupal\alexa2_demo\Services;

use Drupal\node\Entity\Node;

class WizardTreeService {

  const ALLOWED_SSML_TAGS = '<amazon:domain><amazon:effect><amazon:emotion><audio><break><emphasis><lang><p><phoneme><prosody><s><say-as><sub><voice><w>';

  public function buildWizardTree() {
    
    $wizardTree = [];
    $wizards = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'wizard')
      ->accessCheck(TRUE)
      ->execute();
    $wizards = Node::loadMultiple($wizards);
    foreach ($wizards as $wizard) {
      $wizardTree[$wizard->id()] = $this->buildWizardStep($wizard);
    }

    return $wizardTree;
  }

  public function buildWizardTreeFromNodeId( $startNodeId ) {
    return $this->buildWizardTreeFromNode( Node::load($startNodeId) );
  }

  public function buildWizardTreeFromNode( Node $wizard, $keyedChildren = true ) {

    return $this->buildWizardStep( $wizard, $keyedChildren );
  }

  protected function buildWizardStep( $wizardStep, $keyedChildren = true ) {

    if ( $wizardStep == null ) {
      return null;
    }

    $treeNode = [
      'name' => preg_replace('/[ -]/', '_', strtolower($wizardStep->getTitle() ?? 'wizard_step_' . $wizardStep->id())),
      'title' => $wizardStep->getTitle() ?? '',
      'id' => $wizardStep->id() ?? '',
      'body' => strip_tags(html_entity_decode($this->getFieldValue($wizardStep, 'body')), WizardTreeService::ALLOWED_SSML_TAGS),
      'primaryUtterance' => $this->getFieldValue($wizardStep, 'field_wizard_primary_utterance'),
      'aliases' => $this->getFieldValue($wizardStep, 'field_wizard_aliases'),
      'children' => [],
      // 'original_node' => $wizardStep,
      // 'original_node_data' => $wizardStep->toArray()
    ];
    $children = $wizardStep->get('field_wizard_step')->referencedEntities();

    foreach ($children as $child) {
      $childId = $child->id();
      $childStep = $this->buildWizardStep( $child );
      if ( $keyedChildren ) {
        $treeNode['children'][$childId] = $childStep;
      } else {
        $treeNode['children'][] = $childStep;
      }
    }

    return $treeNode;

  }

  public function saveWizardTree($tree) {



  }

  private function getFieldValue($obj, $fieldName) {
    return $obj?->get($fieldName)?->value ?? '';
  }

}