/* *
 * This sample demonstrates handling intents from an Alexa skill using the Alexa Skills Kit SDK (v2).
 * Please visit https://alexa.design/cookbook for additional examples on implementing slots, dialog management,
 * session persistence, api calls, and more.
 * */
const Alexa = require('ask-sdk-core');
const exactResult = require('./scamMatch.json');
const generalDefinition = require('./scamHelper.json');

const LaunchRequestHandler = {
    canHandle(handlerInput) {
        return Alexa.getRequestType(handlerInput.requestEnvelope) === 'LaunchRequest';
    },
    handle(handlerInput) {
        const speakOutput = "<speak><prosody rate='medium'>Welcome to the <prosody rate='x-slow'>USA gov</prosody> scams wizard!<amazon:emotion name='disappointed' intensity='high'> I'm sorry that you are experiencing a scam. I can help you learn where to report it!</amazon:emotion> Please tell me the type of scam you want to report.</prosody></speak>";
        console.log ("I am here");
        return handlerInput.responseBuilder
            .speak(speakOutput)
            .reprompt('It is a financial scam, if your accounts or loans are affected. a moving scam, to report a moving company or movers. Identity theft, if someone used your personal information. or imposter scam, if the scam involves deception or threats, including romance scams, and phishing. say something else if none of these categories apply.')
            .getResponse();
    }
};

const ReportScamIntentHandler = {
    canHandle(handlerInput) {
        return Alexa.getRequestType(handlerInput.requestEnvelope) === 'IntentRequest'
            && Alexa.getIntentName(handlerInput.requestEnvelope) === 'ReportScam';
    },
    handle(handlerInput) {
        console.log("I am in ReportScam");

        const exact = handlerInput.requestEnvelope.request.intent.slots.exact.value;
        console.log("exact" + JSON.stringify(exact));

        if (exact !== null) {
            const key = `${exact}`;
            const databaseResponse = exactResult[key];
            
            console.log("Response from mock database ", key,databaseResponse);

            const speakOutput = databaseResponse.result;

            return handlerInput.responseBuilder
                .speak(speakOutput)
                .reprompt('add a reprompt if you want to keep the session open for the user to respond')
                .getResponse();
        }
    }
};
const GeneralDefinitionIntentHandler = {
    canHandle(handlerInput) {
        return Alexa.getRequestType(handlerInput.requestEnvelope) === 'IntentRequest'
            && Alexa.getIntentName(handlerInput.requestEnvelope) === 'GeneralDefinition';
    },
    handle(handlerInput) {
        console.log("I am in GeneralDefinition");

        // const speakOutput = 'Hello World!';
        
        const financial = handlerInput.requestEnvelope.request.intent.slots.financial.value;
        // var imposter = handlerInput.requestEnvelope.request.intent.slots.imposter.value;
        // var fraud = handlerInput.requestEnvelope.request.intent.slots.fraud.value;
        // var moving = handlerInput.requestEnvelope.request.intent.slots.moving.value;
    
        console.log("financial" + JSON.stringify(financial));
        // console.log("moving" + JSON.stringify(moving));
        // console.log("fraud" + JSON.stringify(fraud));
        // console.log("imposter" + JSON.stringify(imposter));

        if (financial !== null) {
            const key = `${financial}`;
            const databaseResponse = generalDefinition[key];

            console.log("Response from mock database ", key,databaseResponse);
            const speakOutput = databaseResponse.description;
            
             return handlerInput.responseBuilder
                .speak(speakOutput)
                .reprompt('add a reprompt if you want to keep the session open for the user to respond')
                .getResponse();

            
        }
    }
};

const HelpIntentHandler = {
    canHandle(handlerInput) {
        return Alexa.getRequestType(handlerInput.requestEnvelope) === 'IntentRequest'
            && Alexa.getIntentName(handlerInput.requestEnvelope) === 'AMAZON.HelpIntent';
    },
    handle(handlerInput) {
        const speakOutput = "<speak>The <prosody rate='slow'>USA gov</prosody> scams wizard is designed to help you figure out where to report your scam. To speak to a human about reporting your scam, please Call us at <say-as interpret-as= 'digits'> 1844</say-as>USA<say-as interpret-as= 'spell-out'>GOV1</say-as></speak>";

        return handlerInput.responseBuilder
            .speak(speakOutput)
            .reprompt(speakOutput)
            .getResponse();
    }
};

const CancelAndStopIntentHandler = {
    canHandle(handlerInput) {
        return Alexa.getRequestType(handlerInput.requestEnvelope) === 'IntentRequest'
            && (Alexa.getIntentName(handlerInput.requestEnvelope) === 'AMAZON.CancelIntent'
                || Alexa.getIntentName(handlerInput.requestEnvelope) === 'AMAZON.StopIntent');
    },
    handle(handlerInput) {
        const speakOutput = "<speak> <amazon:emotion name= 'disappointed' intensity= 'medium'>I hope you figured out where to report your scam.</amazon:emotion> If you need further assistance from a human, say 'help,' then I will provide you the<prosody rate='slow'> USA gov number</prosody>.</speak>";

        return handlerInput.responseBuilder
            .speak(speakOutput)
            .getResponse();
    }
};
/* *
 * FallbackIntent triggers when a customer says something that doesn’t map to any intents in your skill
 * It must also be defined in the language model (if the locale supports it)
 * This handler can be safely added but will be ingnored in locales that do not support it yet 
 * */
const FallbackIntentHandler = {
    canHandle(handlerInput) {
        return Alexa.getRequestType(handlerInput.requestEnvelope) === 'IntentRequest'
            && Alexa.getIntentName(handlerInput.requestEnvelope) === 'AMAZON.FallbackIntent';
    },
    handle(handlerInput) {
        const speakOutput = 'Sorry, I don\'t know about that. Please try again.';

        return handlerInput.responseBuilder
            .speak(speakOutput)
            .reprompt(speakOutput)
            .getResponse();
    }
};
/* *
 * SessionEndedRequest notifies that a session was ended. This handler will be triggered when a currently open 
 * session is closed for one of the following reasons: 1) The user says "exit" or "quit". 2) The user does not 
 * respond or says something that does not match an intent defined in your voice model. 3) An error occurs 
 * */
const SessionEndedRequestHandler = {
    canHandle(handlerInput) {
        return Alexa.getRequestType(handlerInput.requestEnvelope) === 'SessionEndedRequest';
    },
    handle(handlerInput) {
        console.log(`~~~~ Session ended: ${JSON.stringify(handlerInput.requestEnvelope)}`);
        // Any cleanup logic goes here.
        return handlerInput.responseBuilder.getResponse(); // notice we send an empty response
    }
};
/* *
 * The intent reflector is used for interaction model testing and debugging.
 * It will simply repeat the intent the user said. You can create custom handlers for your intents 
 * by defining them above, then also adding them to the request handler chain below 
 * */
const IntentReflectorHandler = {
    canHandle(handlerInput) {
        return Alexa.getRequestType(handlerInput.requestEnvelope) === 'IntentRequest';
    },
    handle(handlerInput) {
        const intentName = Alexa.getIntentName(handlerInput.requestEnvelope);
        const speakOutput = `You just triggered ${intentName}`;

        return handlerInput.responseBuilder
            .speak(speakOutput)
            //.reprompt('add a reprompt if you want to keep the session open for the user to respond')
            .getResponse();
    }
};
// const LogRequestInterceptor = {
// process(handlerInput) {
// console. log('REQUEST ENVELOPE = ${JSON.stringify(handlerInput.requestEnvelope)}');
// }
// };
// const LogResponseInterceptor = {
// process(handlerInput, response) {
// console.log('RESPONSE = ${JSON.stringify(response)}');
// }
// };
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
        const speakOutput = 'Sorry, I had trouble doing what you asked. Please try again.';
        console.log(`~~~~ Error handled: ${JSON.stringify(error)}`);

        return handlerInput.responseBuilder
            .speak(speakOutput)
            .reprompt(speakOutput)
            .getResponse();
    }
};

/**
 * This handler acts as the entry point for your skill, routing all request and response
 * payloads to the handlers above. Make sure any new handlers or interceptors you've
 * defined are included below. The order matters - they're processed top to bottom 
 * */
exports.handler = Alexa.SkillBuilders.custom()
    .addRequestHandlers(
        LaunchRequestHandler,
        ReportScamIntentHandler,
        GeneralDefinitionIntentHandler,
        HelpIntentHandler,
        CancelAndStopIntentHandler,
        FallbackIntentHandler,
        SessionEndedRequestHandler,
        IntentReflectorHandler)
    // .addRequestInterceptors
//   LogRequestInterceptor
//   .addResponseInterceptors
//   LogResponseInterceptor
    .addErrorHandlers(
        ErrorHandler)
    .withCustomUserAgent('sample/hello-world/v1.2')
    .lambda();