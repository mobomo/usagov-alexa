<?php

/**
 * @file
 * Creates a block which displays the the RSVPForm contained in RSVPForm.php
 */

namespace Drupal\rsvplist\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;


/**
 * Provides the RSVP main block.
 *
 * @Block(
 *   id = "rsvp_block",
 *   admin_label = @Translation("The RSVP Block")
 * )
 */
class RSVPBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Use this code for exercise 03_10_Adding a Block
    /*return [
      '#type' => 'markup',
      '#markup' => $this->t('My RSVP List Block'),
    ];*/
    // end 03_10_Adding a Block

    // Use this code for exercise 03_11 Setting Block Permissions
    return \Drupal::formBuilder()->getForm('Drupal\rsvplist\Form\RSVPForm');
    // end 03_11

  }

  // Add this method for exercise 03_11

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    // If viewing a node, get the fully loaded node object.
    $node = \Drupal::routeMatch()->getParameter('node');

//    if ( !(is_null($node)) ) {
//      return AccessResult::allowedIfHasPermission($account, 'view rsvplist');
//    }
//
//    return AccessResult::forbidden();


    // Some pages may not be nodes, although we could not display the block using the Block Settings through
    // the Block UI at /admin/structure/block, instead we are programmatically controlling to only display
    // this block on node pages using AccessResult::allowedIfHasPermission($account, 'view rsvplist')

    if ( !(is_null($node)) ) {
      $enabler = \Drupal::service('rsvplist.enabler');
      if ($enabler->isEnabled($node)) {

        return AccessResult::allowedIfHasPermission($account, 'view rsvplist');
      }
    }

    return AccessResult::forbidden();
  }
}
