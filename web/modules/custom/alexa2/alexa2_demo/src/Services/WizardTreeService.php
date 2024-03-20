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

  public function buildFlattenedWizardTree() {

    $wizardTree = [];
    $wizards = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'wizard')
      ->accessCheck(TRUE)
      ->execute();
    $wizards = Node::loadMultiple($wizards);
    foreach ( $wizards as $wizard ) {
      $wizardTree[$wizard->id()] = $this->buildFlattenedWizardTreeFromNode( $wizard );
    }

    return $wizardTree;

  }

  public function buildFlattenedWizardTreeFromNodeId( $startNodeId ) {
    return $this->buildFlattenedWizardTreeFromNode( Node::load($startNodeId) );
  }

  public function buildFlattenedWizardTreeFromNode( Node $wizard ) {
    $wizardTree = [];
    $ids = [];
    $treeQueue = [];

    $treeQueue[] = [
      'node' => $wizard,
      'parent' => null,
    ];

    while ( !empty($treeQueue) ) {
      $treeNode = array_shift($treeQueue);
      $parent = $treeNode['parent'];
      $treeNode = $treeNode['node'];
      if ( !isset($wizardTree[$treeNode->id()]) ) {
        $ids[] = $treeNode->id();
        $wizardTree[$treeNode->id()] = $this->buildWizardDataFromStep( $treeNode, true );
        if ( $parent !== null ) {
          $wizardTree[$treeNode->id()]['parentStepId'] = $parent->id();
        } else {
          $wizardTree[$treeNode->id()]['parentStepId'] = null;
        }
        $children = $treeNode->get('field_wizard_step')->referencedEntities();
        foreach ( $children as $child ) {
          if ( !isset($wizardTree[$child->id()]) ) {
            $treeQueue[] = [
              'node' => $child,
              'parent' => $treeNode,
            ];
          }
        }
      }
    }

    return [
      'entities' => $wizardTree,
      'ids' => $ids,
      'rootStepId' => $wizard->id()
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
      // Support data structure being wrapped in top-level objects
      // 'entities' and 'ids' - see buildFlattenedWizardTree
      if ( isset($tree['entities'] )) {
        $treeArray = $tree['entities'];
        $tree = [];
        foreach ($treeArray as $treeNode) {
          $tree[$treeNode['id']] = $treeNode;
        }
      }
      // Determine format - nested vs. flattened
      $nested = false;
      foreach ( $tree as $treeNode ) {
        $children = $treeNode['children'];
        foreach ( $children as $child ) {
          if ( !is_numeric($child) ) {
            $nested = true;
          }
          break;
        }
        break;
      }
      if ( $nested ) {
        $this->saveWizardTreeNested($tree);
      } else {
        $this->saveWizardTreeFlattened($tree);
      }
    } else {
      // TODO
    }
  }

  public function validateUserWizardTreePermissions($user = null) {
    // TODO properly load currently logged in user
    $user ??= \Drupal::currentUser();
    // TODO determine correct permissions
    if ( $user->isAuthenticated() ) {//&& $user->hasPermission('')) {
      return true;
    }
    return false;
  }

  protected function saveWizardTreeNested($tree) {
    $this->saveWizardStep($tree);
  }

  protected function saveWizardTreeFlattened($tree) {
    // As a flattened tree, iterate through each item in the tree and update that node.
    // If the item has no parent, it is a wizard. If it has a parent, it is a wizard step.
    foreach ( $tree as $wizardStepId => $wizardStep ) {
      $wizardStep = $tree[$wizardStepId];
      if ( $wizardStep !== null ) {
        // Attempt to load the node. If the ID is null, negative,
        // or a node doesn't exist with that ID, then $node will
        // be null.
        if ( isset($wizardStep['id']) ) {
          $node = Node::load($wizardStep['id']);
        }

        if ( $wizardStep['delete'] == true ) {
          if ( $node !== null ) {
            // If needed, remove this item as a child of the parent.
            // If the parent's data still lives in the tree, then it hasn't yet been
            // saved. If it doesn't, then it has been saved and the parent node will need to
            // be loaded and modified.
            if ( isset($tree[$wizardStep['parentStepId']] )) {
              $parent = $tree[$wizardStep['parentStepId']];
              $index = array_search($wizardStep['id'], $parent['children']);
              if ($index !== false) {
                unset($parent['children'][$index]);
              }
            } else {
              $parentNode = Node::load($wizardStep['parentStepId']);
              if ( $parentNode !== null ) {
                $parentChildSteps = $parentNode->get('field_wizard_step')->referencedEntities();
                $parentNewChildSteps = [];
                foreach ( $parentChildSteps as $parentChildStep ) {
                  if ( $parentChildStep->id() != $wizardStep['id'] ) {
                    $parentNewChildSteps[] = [
                      'target_id' => $parentChildStep->id()
                    ];
                  }
                  $parentNode->set('field_wizard_step', $parentNewChildSteps);
                  $parentNode->save();
                }
              }
            }

            // The parent has been updated, and the current node has been deleted.
            // If a step is deleted, all of its children should also be deleted.
            $toDelete = [];
            $childQueue = [$wizardStep['id']];
            while ( !empty($childQueue) ) {
              $currentNodeId = array_shift($childQueue);
              // If not already, set the current node id to be deleted.
              if ( !in_array($currentNodeId, $toDelete) ) {
                // Add the current node's child items from the tree to the delete queue.
                $toDelete[] = $currentNodeId;
                if ( isset($tree[$currentNodeId]) && isset($tree[$currentNodeId]['children']) ) {
                  foreach ($tree[$currentNodeId]['children'] as $childNodeId ) {
                    if ( !in_array($childNodeId, $toDelete) && !in_array($childNodeId, $childQueue) ) {
                      $childQueue[] = $childNodeId;
                    }
                  }
                }
                // Add the current node's child items from the database to the delete queue.
                $childNode = Node::load($currentNodeId);
                if ( $childNode !== null ) {
                  $childNodeIds = $childNode->get('field_wizard_step')->getValue();
                  foreach ( $childNodeIds as $childNodeId) {
                    $childNodeId = $childNodeId['target_id'];
                    if ( !in_array($childNodeId, $toDelete) && !in_array($childNodeId, $childQueue)) {
                      $childQueue[] = $childNodeId;
                    }
                  }
                }
              }
            }

            // Now that the node and all of its children (from the passed in tree data as well as Drupal)
            // are marked for deletion, delete them all and remove them from the tree data.
            foreach ( $toDelete as $toDeleteId ) {
              // Delete node and unset in tree.
              $toDeleteNode = Node::load($toDeleteId);
              if ( $toDeleteNode !== null ) {
                $toDeleteNode->delete();
              }

              if ( isset($toDeleteId, $tree) ) {
                unset($tree[$toDeleteId]);
              }
            }
          }
        } else {
          $isNewNode = false;
          if ( $node === null ) {
            $isNewNode = true;
            if ( isset($wizardStep['parentStepId']) ) {
              $node = Node::create([
                'type' => 'wizard_step'
              ]);
            } else {
              $node = Node::create([
                'type' => 'wizard'
              ]);
            }
          }

          // TODO handle node creation/updating in separate protected function?
          $node->setTitle($wizardStep['title']);

          // TODO set correct format
          $node->set('body', [
            'value' => $wizardStep['body'],
            'format' => 'full_html'
          ]);

          $node->set('field_wizard_primary_utterance', $wizardStep['primaryUtterance'] ?? '');

          $node->set('field_wizard_aliases', $wizardStep['aliases'] ?? '');

          $node->setOwnerId(\Drupal::currentUser()->id());

          $fieldWizardStep = [];
          foreach ( $wizardStep['children'] as $childId ) {
            if ($childId > 0) {
              $fieldWizardStep[] = [
                'target_id' => $childId
              ];
            }
          }

          // TODO if not new node, compare new child step array with array
          // from node and delete any children that aren't referenced by the new array.

          $node->set('field_wizard_step', $fieldWizardStep);

          // Save the node.
          $node->save();

          // TODO better way to handle this?
          // Currently, an ID of -1 means the step is a new step.
          // Because of this, the node must first be created so an ID is generated,
          // then the parent Node (or tree data) should be updated to point to this ID.

          $newId = $node->id();
          $parentStepId = $wizardStep['parentStepId'];
          if ( $isNewNode ) {
            // TODO insert into correct spot based on weight.
            if ( isset($tree[$parentStepId]) ) {
              // Check to make sure the node ID (e.g. possibly -1) is not already set in parent.
              // If it is, swap it out with the correct ID. If not, add it in.
              $index = array_search($wizardStep['id'], $tree[$parentStepId]['children']);
              if ($index !== false) {
                $tree[$parentStepId]['children'][$index] = $newId;
              } else {
                $tree[$parentStepId]['children'][] = $newId;
              }
            } else {
              // If the parent isn't in the tree, the node must be loaded,
              // the new ID added as a child step, then the node must be saved.
              $parentNode = Node::load($parentStepId);
              if ( $parentNode != null ) {
                $newChildren = [];
                $referencedEntities = $parentNode->get('field_wizard_step')->referencedEntities();
                foreach ( $referencedEntities as $referencedEntity ) {
                  $newChildren[] = [
                    'target_id' => $referencedEntity->id()
                  ];
                }
                $newChildren[] = $newId;
                $parentNode->set('field_wizard_step', $newChildren);
                $parentNode->save();
              }
            }
          }

          unset($tree[$wizardStep['id']]);
        }
      }
    }
  }

  private function saveWizardStep($wizardStep, $parent = null) {
    // TODO validate step
    // TODO validate user permissions

    if ($wizardStep) {
      // If the id is set, try to load it.
      if ($wizardStep['id']) {
        $node = Node::load($wizardStep['id']);
      }
      if ( $wizardStep['delete'] == true ) {
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

        $node->set('field_wizard_step', []);

        // Save the node.
        $node->save();

        foreach ($wizardStep['children'] as $childData) {
          $this->saveWizardStep( $childData, $node );
        }

        if ($parent) {
          // TODO set children here.
          $currentChildren = $parent->get('field_wizard_step')->referencedEntities();
          $currentChildren[] = [
            'target_id' => $node->id()
          ];
          $parent->set('field_wizard_step', $currentChildren);
          $parent->save();
        }
      }
    }

  }

  private function getFieldValue($obj, $fieldName) {
    return $obj?->get($fieldName)?->value ?? '';
  }

}
