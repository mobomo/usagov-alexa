/* *
 * This sample demonstrates handling intents from an Alexa skill using the Alexa Skills Kit SDK (v2).
 * Please visit https://alexa.design/cookbook for additional examples on implementing slots, dialog management,
 * session persistence, api calls, and more.
 * */
const Alexa = require("ask-sdk-core");
const data = require("./bankingmatch.json");

const GetRecommendationAPIHandler = {
  canHandle(handlerInput) {
    return (
      Alexa.getRequestType(handlerInput.requestEnvelope) === "Dialog.API.Invoked" &&
      handlerInput.requestEnvelope.request.apiRequest.name === "getRecommendation"
    );
  },
  handle(handlerInput) {
    const apiRequest = handlerInput.requestEnvelope.request.apiRequest;

    const scam = resolveEntity(apiRequest.slots, "BankingScam");

    const recommendationEntity = {};
    if (scam !== null) {
      const key = `banking-${scam}`;
      const databaseResponse = data[key];

      console.log("Response from mock database ", databaseResponse);

      recommendationEntity.name = databaseResponse.result;
      recommendationEntity.BankingScam = scam;
    }

    const response = buildSuccessApiResponse(recommendationEntity);
    
    // const attributes = handlerInput.attributesManager.getSessionAttributes();
    // attributes.lastResult = "This is from the get recommendation handler";
    // handlerInput.attributesManager.getSessionAttributes(attributes);
    
    return response;
  },
};

// *****************************************************************************
// Resolves slot value using Entity Resolution
const resolveEntity = function (resolvedEntity, slot) {
  //This is built in functionality with SDK Using Alexa's ER
  let erAuthorityResolution = resolvedEntity[slot].resolutions.resolutionsPerAuthority[0];
  let value = null;

  if (erAuthorityResolution.status.code === "ER_SUCCESS_MATCH") {
    value = erAuthorityResolution.values[0].value.name;
  }

  return value;
};

const buildSuccessApiResponse = (returnEntity) => {
  return { apiResponse: returnEntity };
};

const LaunchRequestHandler = {
  canHandle(handlerInput) {
    return Alexa.getRequestType(handlerInput.requestEnvelope) === "LaunchRequest";
  },
  handle(handlerInput) {
    const speakOutput = "Welcome, you can say Hello or Help. Which would you like to try?";

    return handlerInput.responseBuilder.speak(speakOutput).reprompt(speakOutput).getResponse();
  },
};

const HelloWorldIntentHandler = {
  canHandle(handlerInput) {
    return (
      Alexa.getRequestType(handlerInput.requestEnvelope) === "IntentRequest" &&
      Alexa.getIntentName(handlerInput.requestEnvelope) === "HelloWorldIntent"
    );
  },
  handle(handlerInput) {
    const speakOutput = "Hello World!";

    return (
      handlerInput.responseBuilder
        .speak(speakOutput)
        //.reprompt('add a reprompt if you want to keep the session open for the user to respond')
        .getResponse()
    );
  },
};

const HelpIntentHandler = {
  canHandle(handlerInput) {
    return (
      Alexa.getRequestType(handlerInput.requestEnvelope) === "IntentRequest" &&
      Alexa.getIntentName(handlerInput.requestEnvelope) === "AMAZON.HelpIntent"
    );
  },
  handle(handlerInput) {
    const speakOutput = "You can say hello to me! How can I help?";

    return handlerInput.responseBuilder.speak(speakOutput).reprompt(speakOutput).getResponse();
  },
};

// const RepeatIntentHandler = {
//   canHandle(handlerInput) {
//     return (
//       Alexa.getRequestType(handlerInput.requestEnvelope) === "IntentRequest" &&
//       Alexa.getIntentName(handlerInput.requestEnvelope) === "AMAZON.RepeatIntent"
//     );
//   },
//   handle(handlerInput) {
//     let speakOutput = "There was nothing to repeat.";
//     const attributes = handlerInput.attributesManager.getSessionAttrbutes
    
//     if(attributes.lastResult){
//         speakOutput = `{attributes.lastResult}. Let me know if you need me to repeat this information, also If you need to speak to a human at USA dot gov, say "help". Thank you.`
//     }

//     return handlerInput.responseBuilder.speak(speakOutput).reprompt(speakOutput).getResponse();
//   },
// };

// const CancelAndStopIntentHandler = {
//   canHandle(handlerInput) {
//     return (
//       Alexa.getRequestType(handlerInput.requestEnvelope) === "IntentRequest" &&
//       (Alexa.getIntentName(handlerInput.requestEnvelope) === "AMAZON.CancelIntent" ||
//         Alexa.getIntentName(handlerInput.requestEnvelope) === "AMAZON.StopIntent")
//     );
//   },
//   handle(handlerInput) {
//     const speakOutput = "Goodbye!";

//     return handlerInput.responseBuilder.speak(speakOutput).getResponse();
//   },
// };

const CancelIntentHandler = {
  canHandle(handlerInput) {
    return (
      Alexa.getRequestType(handlerInput.requestEnvelope) === "IntentRequest" &&
      (Alexa.getIntentName(handlerInput.requestEnvelope) === "AMAZON.CancelIntent")
    );
  },
  handle(handlerInput) {
    const speakOutput = "Cancelled!";

    return handlerInput.responseBuilder.speak(speakOutput).getResponse();
  },
};

const StopIntentHandler = {
  canHandle(handlerInput) {
    return (
      Alexa.getRequestType(handlerInput.requestEnvelope) === "IntentRequest" &&
      (Alexa.getIntentName(handlerInput.requestEnvelope) === "AMAZON.StopIntent")
    );
  },
  handle(handlerInput) {
    const speakOutput = "Stopped!";

    return handlerInput.responseBuilder.speak(speakOutput).getResponse();
  },
};
/* *
 * FallbackIntent triggers when a customer says something that doesnâ€™t map to any intents in your skill
 * It must also be defined in the language model (if the locale supports it)
 * This handler can be safely added but will be ingnored in locales that do not support it yet
 * */
const FallbackIntentHandler = {
  canHandle(handlerInput) {
    return (
      Alexa.getRequestType(handlerInput.requestEnvelope) === "IntentRequest" &&
      Alexa.getIntentName(handlerInput.requestEnvelope) === "AMAZON.FallbackIntent"
    );
  },
  handle(handlerInput) {
    const speakOutput = "Sorry, I don't know about that. Please try again.";

    return handlerInput.responseBuilder.speak(speakOutput).reprompt(speakOutput).getResponse();
  },
};
/* *
 * SessionEndedRequest notifies that a session was ended. This handler will be triggered when a currently open
 * session is closed for one of the following reasons: 1) The user says "exit" or "quit". 2) The user does not
 * respond or says something that does not match an intent defined in your voice model. 3) An error occurs
 * */
const SessionEndedRequestHandler = {
  canHandle(handlerInput) {
    return Alexa.getRequestType(handlerInput.requestEnvelope) === "SessionEndedRequest";
  },
  handle(handlerInput) {
    console.log(`~~~~ Session ended: ${JSON.stringify(handlerInput.requestEnvelope)}`);
    // Any cleanup logic goes here.
    return handlerInput.responseBuilder.getResponse(); // notice we send an empty response
  },
};
/* *
 * The intent reflector is used for interaction model testing and debugging.
 * It will simply repeat the intent the user said. You can create custom handlers for your intents
 * by defining them above, then also adding them to the request handler chain below
 * */
const IntentReflectorHandler = {
  canHandle(handlerInput) {
    return Alexa.getRequestType(handlerInput.requestEnvelope) === "IntentRequest";
  },
  handle(handlerInput) {
    const intentName = Alexa.getIntentName(handlerInput.requestEnvelope);
    const speakOutput = `You just triggered ${intentName}`;

    return (
      handlerInput.responseBuilder
        .speak(speakOutput)
        //.reprompt('add a reprompt if you want to keep the session open for the user to respond')
        .getResponse()
    );
  },
};
/**
 * Generic error handling to capture any syntax or routing errors. If you receive an error
 * stating the request handler chain is not found, you have not implemented a handler for
 * the intent being invoked or included it in the skill builder below
 * */
const ErrorHandler = {
  canHandle() {
    return true;
  },
  handle(handlerInput, error) {
    const speakOutput = "Sorry, I had trouble doing what you asked. Please try again.";
    console.log(`~~~~ Error handled: ${JSON.stringify(error)}`);

    return handlerInput.responseBuilder.speak(speakOutput).reprompt(speakOutput).getResponse();
  },
};

/**
 * This handler acts as the entry point for your skill, routing all request and response
 * payloads to the handlers above. Make sure any new handlers or interceptors you've
 * defined are included below. The order matters - they're processed top to bottom
 * */
exports.handler = Alexa.SkillBuilders.custom()
  .addRequestHandlers(
    GetRecommendationAPIHandler,
    CancelIntentHandler,
    StopIntentHandler,
    LaunchRequestHandler,
    HelloWorldIntentHandler,
    HelpIntentHandler,
    FallbackIntentHandler,
    SessionEndedRequestHandler,
    IntentReflectorHandler
  )
  .addErrorHandlers(ErrorHandler)
  .withCustomUserAgent("sample/hello-world/v1.2")
  .lambda();
