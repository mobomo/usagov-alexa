<?php

namespace Drupal\alexa2;

use Symfony\Contracts\EventDispatcher\Event;
use MaxBeckers\AmazonAlexa\Request\Request as AlexaRequest;
use MaxBeckers\AmazonAlexa\Response\Response as AlexaResponse;

/**
 * Implements a new Symfony event.
 *
 * This class implements a new Symfony event called AlexaEvent which will be
 * dispatched when a new Alexa request comes in. Refer to the alexa_demo module
 * for an example of how to implement a new Event Subscriber to handle these
 * events.
 */
class Alexa2Event extends Event {

  /**
   * The associated Alexa request.
   *
   * @var \MaxBeckers\AmazonAlexa\Request\Request
   */
  protected $request;

  /**
   * The Alexa response object to use for the response.
   *
   * @var \MaxBeckers\AmazonAlexa\Response\Response
   */
  protected $response;

  /**
   * Constructor.
   *
   * @param \MaxBeckers\AmazonAlexa\Request\Request $request
   *   The Alexa request.
   * @param \MaxBeckers\AmazonAlexa\Response\Response $response
   *   An Alexa response object to use for any response.
   */
  public function __construct(AlexaRequest $request, AlexaResponse &$response) {
    $this->request = $request;
    $this->response =& $response;
  }

  /**
   * Getter for the request object.
   *
   * @return \MaxBeckers\AmazonAlexa\Request\Request
   *   The associated Alexa request.
   */
  public function getRequest() {
    return $this->request;
  }

  /**
   * Setter for the request object.
   *
   * @param \MaxBeckers\AmazonAlexa\Request\Request $request
   *   The Alexa request to associate with this event.
   */
  public function setRequest(AlexaRequest &$request) {
    $this->request = $request;
  }

  /**
   * Getter for the response object.
   *
   * @return \MaxBeckers\AmazonAlexa\Response\Response
   *   The associated Alexa response.
   */
  public function &getResponse() {
    return $this->response;
  }

  /**
   * Setter for the response object.
   *
   * @param \MaxBeckers\AmazonAlexa\Response\Response $response
   *   The Alexa response to associate with this event.
   */
  public function setResponse(AlexaResponse &$response) {
    $this->response =& $response;
  }

}
