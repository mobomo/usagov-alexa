<?php

namespace Drupal\alexa2_demo\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
// JsonResponse if returning JSON data.

class WizardTreeApi extends ControllerBase {

    /**
     * Accepts a POST request containing the wizard tree data to be modified.
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     *  The HTTP request containing all request data.
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     *  A simple HTTP response containing a status code.
     */
    public function updateWizardtree(Request $request) {
        $response = new Response();

        if ($request->getMethod() !== 'POST') {
            $response->setStatusCode(Response::HTTP_METHOD_NOT_ALLOWED);
            $response->setContent('Method Not Allowed');
            $response->send();
            return $response;
        }
        
        $postData = $request->toArray();

        if ( !$postData || empty($postData) ) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setContent('Bad Request');
            $response->send();
            return $response;
        }

        $status = \Drupal::service('alexa2_demo.wizard_tree')->saveWizardTree( $postData );
        // TODO parse status

        $response->setStatusCode(Response::HTTP_OK);
        // TODO better message? E.g. nodes deleted, created, updated, etc.
        $response->setContent('Wizard Tree Updated Successfully.');
        $response->send();
        return $response;
    }

    /**
     * Checks access for updating wizard tree nodes.
     * 
     * @param \Drupal\Core\Session\AccountInterface $account
     *  Run access check for this account.
     * 
     * @return \Drupal\Core\Access\AccessResultInterface
     *  The result of the access check.
     */
    public function updateWizardTreeAccess() {
        // TODO validate user permission

        // return AccessResult::allowedIf($account->hasPermission('do example things') && $this->someOtherCustomCondition());
        return AccessResult::allowedIf(true);
    }

    // public function ajax(Request $request) {
    //     if ($request->request->get('action') == 'create') {
    //         // If nid is <= 0, then the node doesn't exist
    //         if ($request->request->get('nid') <= 0) {
    //             $node = Node::create([
    //                 'type' => 'wc_scheduled_content',
    //             ]);
    //         } else {
    //             $node = Node::load($request->request->get('nid'));
    //         }
    //         error_log($request->request->get('end'));
    //         if (!is_null($node)) {
    //             $node->setTitle($request->request->get('title'));
    //             $node->set('field_wc_schedule_item', $request->request->get('target_id'));
    //             $node->set('field_wc_schedule_screen', $request->request->get('screen'));
    //             $node->set('field_wc_schedule_end_time', $request->request->get('end'));
    //             $node->set('field_wc_schedule_start_time', $request->request->get('start'));
    //             $node->save();
    //             return new JsonResponse([
    //                 'status' => 'created',
    //                 'id' => $node->id(),
    //                 'title' => $node->get('title')->value,
    //                 'start' => $request->request->get('start'),
    //                 'end' => $request->request->get('end'),
    //                 'scheduled_item' => $request->request->get('target_id'),
    //                 'screen' => $request->request->get('screen')
    //             ]);
    //         }
    //     } else if ($request->request->get('action') == 'delete') {
    //         if ($request->request->get('nid') > 0) {
    //             $node = Node::load($request->request->get('nid'));
    //             if (!is_null($node)) {
    //                 $node->delete();
    //             }
    //             return new JsonResponse(['status' => 'deleted']);
    //         }
    //     }
    //     return new JsonResponse(['status' => 'nothing']);
    // }

}