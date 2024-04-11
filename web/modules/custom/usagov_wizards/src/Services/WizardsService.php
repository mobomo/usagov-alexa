<?php

namespace Drupal\usagov_wizards\Services;

use Drupal\node\Entity\Node;

class WizardsService {

  const FIELD_DATA = [
    '#shared' => [
      'body' => [
        'name' => 'body',
        'type' => 'value',
      ],
      'field_footer_html' => [
        'name' => 'footerHTML',
        'type' => 'value',
      ],
      'field_header_html' => [
        'name' => 'headerHTML',
        'type' => 'value',
      ],
      'field_meta_description' => [
        'name' => 'metaDescription',
        'type' => 'value',
      ],
      // 'language' => [
      //   'name' => 'language',
      //   'type' => 'value',
      // ],
      // 'wizardStep' => [
      //   'name' => 'children',
      //   'type' => 'value',
      // ]
    ],
    'wizard' => [
      'field_language_toggle' => [
        'name' => 'languageToggle',
        'type' => 'value',
      ],
      'field_page_intro' => [
        'name' => 'pageIntro',
        'type' => 'value',
      ],
      'field_hide_page_intro' => [
        'name' => 'hidePageIntro',
        'type' => 'value',
      ],
      'field_short_description' => [
        'name' => 'shortDescription',
        'type' => 'value',
      ],
      'field_css_icon' => [
        'name' => 'cssIcon',
        'type' => 'value',
      ],
    ],
    'wizard_step' => [
      'field_option_name' => [
        'name' => 'optionName',
        'type' => 'value',
      ],
    ],
  ];
  
  // ---------------------------------------------- Currently Used ---------------------------------------------- //
  // Title                          (node.title)
  // Body                           (node.body)
  // Footer HTML (field)            (node.field_footer_html)
  // Header HTML                    (node.field_header_html)
  // Meta Description               (node.field_meta_description)
  // Language                       (Node->language()->getName() (or ->getId()))
  // Language Toggle                (node.field_language_toggle)
  // Option Name                    (node.field_option_name)
  // Wizard Step                    (node.field_wizard_step)
  // Page Intro                     (node.field_page_intro)
  // Hide Page Intro                (node.field_hide_page_intro)
  // Short Description              (node.field_short_description)
  // CSS Icon                       (node.field_css_icon)
  
  // ------------------------------------- Exist in CMS but not Used - These fields are marked as not being used in the React frontend ------------------------------------- //
  // FAQ                            (node.field_faq_page)  -- FAQ appears to be a paragraphs thing. Wondering if it's needed since we know these are all wizards.
  // Custom Twig Content            (node.field_custom_twig_content)
  // For contact center use only    (node.field_for_contact_center_only)
  // Exclude from contact center    (node.field_exclude_from_contact_cente)

  // ---------------------------------------------- In the doc, listed to use on frontend, but not added here yet - TODO ---------------------------------------------- //
  // Page Type                      (node.field_page_type) -- This is a taxonomy reference. Wondering if it's needed since we know these are all wizards.

  // -------------------------------------------- Not Fields - TODO --------------------------------------------- //
  // Text Format                    TODO Not a field - this is related to body - TODO how to get/display/set this option programmatically
  // Create Revision                TODO - checkbox
  // Revision log message           TODO
  // Menu Settings                  TODO this contains several settings/options - autogen?
  // Provide menu link              TODO - checkbox
  // Menu link title                TODO
  // Description                    TODO - menu item description
  // Parent Link                    TODO - menu item parent
  // Weight                         TODO - menu item weight
  // Simple XML Sitemap             TODO - needed?
  // URL Redirects                  TODO - needed?
  // URL alias                      TODO - needed?
  // Authoring information          TODO - allow this to be edited, or should it be autogen?
  // Promotion options              TODO - is this needed?
  // Change [publishing state] to   TODO - dropdown selection



  /**
   * Constructs an array containing wizard tree data with nested children.
   * Builds the entire wizard tree.
   * 
   * @param bool $keyedChildren
   *   Whether the 'children' array should be associative (true)
   *   (keyed by child ID) or sequential (false). Default is true.
   * 
   * @return array
   *   Wizard tree data represented as an array.
   */
  public function buildWizardTree( bool $keyedChildren = true ) : array {
    $wizardTree = [];
    // Load all Wizards (top level entries in the wizard tree) that the user has access to.
    $wizards = $this->getAllWizards();
    // For each wizard, recursively generate its tree.
    foreach ($wizards as $wizard) {
      $wizardTree[$wizard->id()] = $this->buildWizardStep( $wizard, $keyedChildren );
    }

    return $wizardTree;
  }

  /**
   * Constructs an array containing wizard tree data with nested children.
   * Builds the wizard tree starting at the Node with the provided ID.
   * 
   * @param int $startNodeId
   *   The ID of the node to act as the root of the tree.
   * @param bool $keyedChildren
   *   Whether the 'children' array should be associative (true)
   *   (keyed by child ID) or sequential (false). Default is true.
   * 
   * @return array
   *   Wizard tree data represented as an array.
   */
  public function buildWizardTreeFromNodeId( int $startNodeId, bool $keyedChildren = true ) : array {
    $node = Node::load($startNodeId);
    if ( $this->isValidTreeNode($node) ) {
      return $this->buildWizardTreeFromNode( $node, $keyedChildren );
    }
    // TODO If the node doesn't exist, what do we return? Empty tree? The entire tree?
    return [];
  }

  /**
   * Constructs an array containing wizard tree data with nested children.
   * Builds the wizard tree starting at the provided Node.
   * 
   * @param Node|null $wizard
   *   The Node to act as the root of the tree.
   * @param bool $keyedChildren
   *   Whether the 'children' array should be associative (true)
   *   (keyed by child ID) or sequential (false). Default is true.
   * 
   * @return array
   *   Wizard tree data represented as an array.
   */
  public function buildWizardTreeFromNode( Node|null $wizard, bool $keyedChildren = true ) : array {
    if ( $this->isValidTreeNode($wizard) ) {
      return $this->buildWizardStep( $wizard, $keyedChildren );
    }
    // TODO If the node doesn't exist, what do we return? Empty tree? The entire tree?
    return [];
  }

  /**
   * Constructs an array containing flattened wizard tree data.
   * All node data is at the top level. Builds the entire wizard tree
   * for all wizards.
   * 
   * @return array
   *   Wizard tree data represented as a flattened array.
   */
  public function buildFlattenedWizardTree() {
    $wizardTree = [];
    $wizards = $this->getAllWizards();

    foreach ( $wizards as $wizard ) {
      $wizardTree[$wizard->id()] = $this->buildFlattenedWizardTreeFromNode( $wizard );
    }

    return $wizardTree;
  }

  /**
   * Constructs an array containing flattened wizard tree data. All node data
   * is at the top level. Builds the wizard tree starting at the Node with
   * the provided ID.
   * 
   * @param int $startNodeId
   *   The ID of the node to act as the root of the tree.
   * 
   * @return array
   *   Wizard tree data represented as a flattened array.
   */
  public function buildFlattenedWizardTreeFromNodeId( int $startNodeId ) : array {
    $node = Node::load($startNodeId);
    if ( $this->isValidTreeNode($node) ) {
      return $this->buildFlattenedWizardTreeFromNode( Node::load($startNodeId) );
    }
    // TODO If the node doesn't exist, what do we return? Empty tree? The entire tree?
    return [];
  }

  /**
   * Constructs an array containing flattened wizard tree data. All node data
   * is at the top level. Builds the wizard tree starting at the provided Node.
   * Essentially a breadth-first algorithm.
   * 
   * @param Node|null $wizard
   *   The Node to act as the root of the tree.
   * 
   * @return array
   *   Wizard tree data represented as a flattened array.
   */
  public function buildFlattenedWizardTreeFromNode( Node|null $wizard ) : array {
    $wizardTree = [];
    $ids = [];
    $treeQueue = [];
    
    if ( $this->isValidTreeNode($wizard) ) {
      // Create a queue of nodes to add to the return array and add the initial
      // Node to it.
      if ( $wizard != null ) {
        $treeQueue[] = [
          'node' => $wizard,
          'parent' => null,
        ];
      }

      // Continue processing until all Nodes have been handled.
      while ( !empty($treeQueue) ) {
        // Grab the first item from the queue.
        $treeNode = array_shift($treeQueue);
        $parent = $treeNode['parent'];
        $treeNode = $treeNode['node'];
        // Only handle the node if it hasn't already been handled.
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
    }

    return [
      'entities' => $wizardTree,
      'ids' => $ids,
      'rootStepId' => $wizard?->id(),
      'availableLanguages' => $this->getAvailableLanguages()
    ];
  }

  /**
   * Recursively builds a nested array representing the wizard tree.
   * 
   * @param Node $wizardStep
   *   The Node acting as the root of the current tree.
   * @param bool $keyedChildren
   *   Whether the 'children' array should be associative (true)
   *   (keyed by child ID) or sequential (false). Default is true.
   * @param array $visited
   *   Keeps a list of visited node ids to prevent infinite recursion
   * 
   * @return array
   *   An array representing the wizard tree.
   */
  protected function buildWizardStep( Node $wizardStep, bool $keyedChildren = true, array &$visited = [] ) : array {
    // TODO infinite loop prevention. Maintain array of 'visited' nodes. While the React front-end won't
    // allow for this, Drupal technically does so we should watch for it.
    // Base case - the current step is null, so we return null.
    if ( $wizardStep == null ) {
      return null;
    }

    $visited[] = $wizardStep->id();

    $treeNode = $this->buildWizardDataFromStep( $wizardStep, false );

    $children = $wizardStep->get('field_wizard_step')->referencedEntities();

    $weight = 0;
    foreach ($children as $child) {
      $childId = $child->id();
      if ( !in_array($childId, $visited) ) {
        $childStep = $this->buildWizardStep( $child, $keyedChildren, $visited );
        $childStep['weight'] = $weight;
        $weight++;
        if ( $keyedChildren ) {
          $treeNode['children'][$childId] = $childStep;
        } else {
          $treeNode['children'][] = $childStep;
        }
      }
    }

    return $treeNode;
  }

  /**
   * Extracts necessary data from a Node into an array.
   * 
   * @param Node $wizardStep
   *   The node to pull data from.
   * @param bool $includeChildIds
   *   Whether children should be an empty array or an array of Node ids.
   *   Default is false (empty array).
   * 
   * @return array
   *   An array representing this step of the wizard tree.
   */
  function buildWizardDataFromStep( Node $wizardStep, bool $includeChildIds = false ) : array {
    $stepData = [];

    if ( $wizardStep ) {
      // TODO strip tags from text fields
      // TODO separate functions for getting wizard and wizard step field values - they have different fields available.      
      $stepData = [
        'nodeType' => $wizardStep->bundle(),
        'name' => preg_replace('/[ -]/', '_', strtolower($wizardStep->getTitle() ?? 'wizard_step_' . $wizardStep->id())),
        'title' => $wizardStep->getTitle() ?? '',
        'id' => $wizardStep->id() ?? '',
        'language' => $wizardStep->language()->getName(),
        'children' => []
      ];

      foreach ( static::FIELD_DATA['#shared'] as $fieldKey => $fieldInfo ) {
        $stepData[$fieldInfo['name']] = $this->getFieldValue($wizardStep, $fieldKey, $fieldInfo['type']);
      }

      if ( !empty(static::FIELD_DATA[$wizardStep->bundle()]) ) {
        foreach ( static::FIELD_DATA[$wizardStep->bundle()] as $fieldKey => $fieldInfo ) {
          $stepData[$fieldInfo['name']] = $this->getFieldValue($wizardStep, $fieldKey, $fieldInfo['type']);
        }
      }

      if ($includeChildIds) {
        $children = $wizardStep->get('field_wizard_step')->referencedEntities();
        $weight = 0;
        foreach ($children as $child) {
          $stepData['children'][] = [
            'id' => $child->id(),
            'weight' => $weight,
          ];
          $weight++;
        }
      }
    }

    return $stepData;
  }

  /**
   * Saves the wizard tree or portion of a wizard tree provided.
   * Note this can create, delete, or edit nodes provided
   * the user is logged in and has permission to do so.
   * 
   * @param array
   *   Array containing tree data to be saved.
   */
  // TODO return status of some sort. E.g. nodes deleted, updated, created.
  // Whether it succeeded, etc.
  public function saveWizardTree( array $tree ) : void {
    // TODO validate tree
    // TODO validate user permissions
    // TODO delete wizard steps if not present in given tree.
    
    if ($this->validateUserWizardTreePermissions()) {
      // Support data structure being wrapped in top-level objects
      // 'entities' and 'ids' - see buildFlattenedWizardTree
      if ( isset($tree['entities']) ) {
        // Convert to associative array keyed by id
        $treeArray = $tree['entities'];
        $tree = [];
        foreach ( $treeArray as $treeNode ) {
          $tree[$treeNode['id']] = $treeNode;
        }
      }
      // Determine format - nested vs. flattened
      // TODO make sure this is correct
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

  /**
   * Validates whether the user has necessary permissions to modify the wizard tree.
   * 
   * @return bool
   */
  public function validateUserWizardTreePermissions() : bool {
    // TODO properly load currently logged in user
    $user = \Drupal::currentUser();
    // TODO determine correct permissions
    if ( $user->isAuthenticated() ) {//&& $user->hasPermission('')) {
      return true;
    }
    return false;
  }

  /**
   * Helper function to save wizard tree data in nested format.
   * 
   * @param array $tree
   *   The nested wizard tree data.
   */
  protected function saveWizardTreeNested(array $tree) : void {
    $this->saveWizardStep($tree);
  }

  /**
   * Helper function to save wizard tree data in flattened format.
   * 
   * @param array $tree
   *   The flattened wizard tree data.
   */
  protected function saveWizardTreeFlattened(array $tree) : void {
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
              $index = false;
              for ( $i = 0; $i < count($parent['children']); $i++ ) {
                if ( $parent['children'][$i]['id'] == $wizardStep['id'] ) {
                  $index = $i;
                  break;
                }
              }
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
                  foreach ($tree[$currentNodeId]['children'] as $childNodeInfo ) {
                    $childNodeId = $childNodeInfo['id'];
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

          // TODO language
          foreach ( static::FIELD_DATA['#shared'] as $fieldKey => $fieldInfo ) {
            if ( !empty($newData = ($wizardStep[$fieldInfo['name']]) ?? $wizardStep[$fieldKey]) ) {
              if ($fieldKey == 'body') {
                // body is treated a bit different because it's formatted text. This should be expanded to allow
                // different field types to be treated different
                // TODO set correct format
                $node->set($fieldKey, [
                  'value' => $newData,
                  'format' => 'full_html'
                ]);
              } else {
                $node->set($fieldKey, $newData);
              }
            }
          }

          $node->setOwnerId(\Drupal::currentUser()->id());

          $fieldWizardStep = [];
          if ( !empty($wizardStep['children']) ) {
            usort($wizardStep['children'], function ($a, $b) {
              return $a['weight'] <=> $b['weight'];
            });
            foreach ( $wizardStep['children'] as $childInfo ) {
              $childId = $childInfo['id'];
              if ($childId > 0) {
                $fieldWizardStep[] = [
                  'target_id' => $childId
                ];
              }
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
              $index = false;
              for ( $i = 0; $i < count($tree[$parentStepId]['children']); $i++ ) {
                if ( $wizardStep['id'] == $tree[$parentStepId]['children'][$i] ) {
                  $index = $i;
                  break;
                }
              }
              if ($index !== false) {
                $tree[$parentStepId]['children'][$index]['id'] = $newId;
              } else {
                $newWeight = 0;
                if ( count($tree[$parentStepId]['children']) > 0 ) {
                  $newWeight = $tree[$parentStepId]['children'][count($tree[$parentStepId]['children']) - 1];
                  $newWeight++;
                }
                $tree[$parentStepId]['children'][] = [
                  'id' => $newId,
                  'weight' => $newWeight,
                ];
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

  /**
   * Recursively saves each step of the wizard tree provided.
   * 
   * @param array $wizardStep
   *   The data for the current wizard step that needs to be saved. 
   * @param Node $parent
   *   The parent Node of the current wizard step being saved.
   */
  private function saveWizardStep( array $wizardStep, Node $parent = null ) : void {
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

  /**
   * Get all Wizard content as Nodes.
   * 
   * @return array
   */
  public function getAllWizards() : array {
    $availableWizards = \Drupal::entityQuery('node')
        ->condition('type', 'wizard')
        ->accessCheck(TRUE)
        ->execute();
    if ( !empty($availableWizards) ) {
        return Node::loadMultiple($availableWizards);
    }
    return [];
  }

  /**
   * Helper function to return a value for a node.
   * 
   * @param Node $obj
   *   The Node to load the value from.
   * @param string $fieldName
   *   The field to try to load.
   * @param string $fieldType
   *   The type of field to load data from. E.g. value, reference, etc.
   *   Default is "value"
   * 
   * @return mixed
   *   The field's value if it exists, otherwise an empty string.
   */
  private function getFieldValue( Node $obj, string $fieldName, string $fieldType = 'value' ) : mixed {
    switch ($fieldType) {
      case 'reference':
        return $obj->hasField($fieldName) ? ($obj->get($fieldName)?->target_id ?? '') : '';
        break;
      case 'value':
      default:
        return $obj->hasField($fieldName) ? ($obj->get($fieldName)?->value ?? '') : '';
        break;
    }
  }

  /**
   * Determines whether a given node is a valid wizard tree node.
   * E.g. is it a Wizard or Wizard Step node.
   * 
   * @param Node|null $node
   *   The node to be checked.
   * 
   * @return bool
   *   true if this node can be part of a wizard tree, otherwise false
   */
  private function isValidTreeNode( Node|null $node ) : bool {
    if ( $node !== null ) {
      if ( $node->bundle() === "wizard" || $node->bundle() === "wizard_step" ) {
        return true;
      }
    }

    return false;
  }

  private function getAvailableLanguages() {
    $availableLanguages = array_map(function($el) {
      return [
        'name' => $el->getName(),
        'id' => $el->getId(),
        'weight' => $el->getWeight(),
      ];
    }, \Drupal::languageManager()->getNativeLanguages());
    return $availableLanguages;
  }

}