<?php

namespace Drupal\alexa2_demo\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
// JsonResponse if returning JSON data.

class WizardTreeApi extends ControllerBase {

    /**
     * Accepts a POST request containing the wizard tree data to be modified.
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     *  The HTTP request containing all request data.
     * 
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *  A simple HTTP response containing a status code and JSON data.
     */
    public function updateWizardtree(Request $request) {
        $response = new JsonResponse();

        if ($request->getMethod() !== 'POST') {
            $response->setStatusCode(Response::HTTP_METHOD_NOT_ALLOWED);
            $response->setContent('{"error": "Method Not Allowed"}');
            return $response;
        }
        
        $postData = $request->toArray();

        if ( !$postData || empty($postData) ) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setContent('{"error": "Bad Request"}');
            return $response;
        }

        $status = \Drupal::service('alexa2_demo.wizard_tree')->saveWizardTree( $postData );
        // TODO parse status

        $rootStepId = $postData['rootStepId'];

        if ( $rootStepId === null ) {
            // If step ID wasn't sent, see if the data is in nested format
            // and if so, grab the top level id from that.
            $rootStepId = $postData['id'];
        }

        $response->setStatusCode(Response::HTTP_OK);
        if ( $rootStepId !== null ) {
            $response->setContent(json_encode(\Drupal::service('alexa2_demo.wizard_tree')->buildFlattenedWizardTreeFromNodeId($rootStepId)));
        } else {
            $response->setContent(json_encode(\Drupal::service('alexa2_demo.wizard_tree')->buildFlattenedWizardTree()));
        }
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
    public function updateWizardTreeAccess(\Drupal\Core\Session\AccountInterface $account) {
        // TODO validate user permission

        // return AccessResult::allowedIf($account->hasPermission('do example things') && $this->someOtherCustomCondition());
        return AccessResult::allowedIf(\Drupal::service('alexa2_demo.wizard_tree')->validateUserWizardTreePermissions($account));
    }

    /**
     * Checks access for getting wizard tree data.
     * 
     * @param \Drupal\Core\Session\AccountInterface $account
     *  Run access check for this account.
     * 
     * @return \Drupal\Core\Access\AccessResultInterface
     *  The result of the access check.
     */
    public function getWizardTreeAccess(\Drupal\Core\Session\AccountInterface $account) {
        // TODO validate user permission

        return AccessResult::allowedIf(\Drupal::service('alexa2_demo.wizard_tree')->validateUserWizardTreePermissions($account));
    }

    /**
     * Generates the flattened JSON structure for the wizard tree.
     * Optionally generate using a provided node id as the root node.
     * 
     * @param int|null $rootId
     *   ID of the node to act as the root. null to generate the whole tree for all wizards.
     * 
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *  A simple HTTP response containing a status code and JSON data.
     */
    public function getFlattenedWizardTree(Request $request, int|null $rootId = null) : JsonResponse {
        $response = new JsonResponse();

        if ($request->getMethod() !== 'GET') {
            $response->setStatusCode(Response::HTTP_METHOD_NOT_ALLOWED);
            $response->setContent('{"error": "Method Not Allowed"}');
            return $response;
        }

        if ( $rootId !== null ) {
            $tree = \Drupal::service('alexa2_demo.wizard_tree')->buildFlattenedWizardTreeFromNodeId( $rootId );
        } else {
            $tree = \Drupal::service('alexa2_demo.wizard_tree')->buildFlattenedWizardTree();
        }
        // TODO parse status

        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent(json_encode($tree));
        return $response;
    }

}