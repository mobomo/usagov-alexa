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

  public function buildFlattedWizardTreeFromNodeId( $startNodeId ) {
    return $this->buildFlattenedWizardTreeFromNode( Node::load($startNodeId) );
  }

  public function buildFlattenedWizardTreeFromNode( Node $wizard ) {
    $wizardTree = [];
    $ids = [];
    $treeQueue = [];

    $treeQueue[] = $wizard;

    while ( !empty($treeQueue) ) {
      $treeNode = array_shift($treeQueue);
      if ( !isset($wizardTree[$treeNode->id()]) ) {
        $ids[] = $treeNode->id();
        $wizardTree[$treeNode->id()] = $this->buildWizardDataFromStep( $treeNode, true );
        $children = $treeNode->get('field_wizard_step')->referencedEntities();
        foreach ( $children as $child ) {
          if ( !isset($wizardTree[$child->id()]) ) {
            $treeQueue[] = $child;
          }
        }
      }
    }

    return [
      'entities' => $wizardTree,
      'ids' => $ids
    ];
  }

  protected function buildWizardStep( $wizardStep, $keyedChildren = true ) {

    if ( $wizardStep == null ) {
      return null;
    }

    $treeNode = $this->buildWizardDataFromStep( $wizardStep, false );

    // $treeNode = [
    //   'name' => preg_replace('/[ -]/', '_', strtolower($wizardStep->getTitle() ?? 'wizard_step_' . $wizardStep->id())),
    //   'title' => $wizardStep->getTitle() ?? '',
    //   'id' => $wizardStep->id() ?? '',
    //   'body' => strip_tags(html_entity_decode($this->getFieldValue($wizardStep, 'body')), WizardTreeService::ALLOWED_SSML_TAGS),
    //   'primaryUtterance' => $this->getFieldValue($wizardStep, 'field_wizard_primary_utterance'),
    //   'aliases' => $this->getFieldValue($wizardStep, 'field_wizard_aliases'),
    //   'children' => [],
    //   // 'original_node' => $wizardStep,
    //   // 'original_node_data' => $wizardStep->toArray()
    // ];
    $children = $wizardStep->get('field_wizard_step')->referencedEntities();

    foreach ($children as $child) {
      $childId = $child->id();
      $childStep = $this->buildWizardStep( $child, $keyedChildren );
      if ( $keyedChildren ) {
        $treeNode['children'][$childId] = $childStep;
      } else {
        $treeNode['children'][] = $childStep;
      }
    }

    return $treeNode;

  }

  function buildWizardDataFromStep( $wizardStep, $includeChildIds = false ) {
    if ( $wizardStep ) {
      $stepData = [
        'name' => preg_replace('/[ -]/', '_', strtolower($wizardStep->getTitle() ?? 'wizard_step_' . $wizardStep->id())),
        'title' => $wizardStep->getTitle() ?? '',
        'id' => $wizardStep->id() ?? '',
        'body' => strip_tags(html_entity_decode($this->getFieldValue($wizardStep, 'body')), WizardTreeService::ALLOWED_SSML_TAGS),
        'primaryUtterance' => $this->getFieldValue($wizardStep, 'field_wizard_primary_utterance'),
        'aliases' => $this->getFieldValue($wizardStep, 'field_wizard_aliases'),
        'children' => []
      ];

      if ($includeChildIds) {
        $children = $wizardStep->get('field_wizard_step')->referencedEntities();
        foreach ($children as $child) {
          $stepData['children'][] = $child->id();
        }
      }
      
      return $stepData;
    }

    return null;
  }

  // TODO return status of some sort. E.g. nodes deleted, updated, created.
  // Whether it succeeded, etc.
  public function saveWizardTree($tree) {
    // TODO validate tree
    // TODO validate user permissions
    // TODO delete wizard steps if not present in given tree.
    if ($this->validateUserWizardTreePermissions()) {
      $this->saveWizardStep($tree);
    } else {
      // TODO
    }
  }

  public function validateUserWizardTreePermissions() {
    // TODO properly load currently logged in user
    $user = \Drupal::currentUser();
    // TODO determine correct permissions
    if ( $user->isAuthenticated() ) {//&& $user->hasPermission('')) {
      return true;
    }
    return false;
  }

  private function saveWizardStep($wizardStep, $parent = null) {
    // TODO validate step
    // TODO validate user permissions

    if ($wizardStep) {
      // If the id is set, try to load it.
      if ($wizardStep['id']) {
        $node = Node::load($wizardStep['id']);
      }
      if ( $wizardStep['delete'] === true ) {
        foreach ( $wizardStep['children'] as $childData ) {
          $childData['delete'] = true;
          $this->saveWizardStep( $childData, $node );
        }

        if ( $node ) {
          $node->delete();
        }
      } else {
        // If it failed to load or isn't set, then create a new node.
        // TODO type depends on parent? i.e. no parent means 'wizard'
        // type, parent means 'wizard_step' type?
        if (!$node) {
          $node = Node::create([
            'type' => 'wizard_step'
          ]);
        }

        $node->setTitle($wizardStep['title']);

        $node->set('body', [
          'value' => $wizardStep['body'],
          'format' => 'full_html'
        ]);

        $node->set('field_wizard_primary_utterance', $wizardStep['primaryUtterance'] ?? '');

        $node->set('field_wizard_aliases', $wizardStep['aliases'] ?? '');

        $node->setOwnerId(\Drupal::currentUser()->id());
        
        // Save the node.
        $node->save();

        foreach ($wizardStep['children'] as $childData) {
          $this->saveWizardStep( $childData, $node );
        }

        if ($parent) {
          // TODO set children here.
          $currentChildren = $parent->get('field_wizard_step')->referencedEntities();
          $inArray = false;
          foreach ($currentChildren as $childIdtoCheck) {
            if ( $childIdToCheck === $node->id() ) {
              $inArray = true;
              break;
            }
          }
          if ( !$inArray ) {
            $currentChildren[] = [
              'target_id' => $node->id()
            ];
            $parent->set('field_wizard_step', $currentChildren);
            $parent->save();
          }
        }
      }
    }

  }

  private function getFieldValue($obj, $fieldName) {
    return $obj?->get($fieldName)?->value ?? '';
  }

}