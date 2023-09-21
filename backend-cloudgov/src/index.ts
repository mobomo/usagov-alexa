import { ErrorHandler, HandlerInput, RequestHandler, Skill, SkillBuilders } from "ask-sdk-core";
import { RequestEnvelope, Response, SessionEndedRequest } from "ask-sdk-model";
import express from "express";
import { ExpressAdapter } from "ask-sdk-express-adapter";

import data from "./data.json";

interface DataType {
  [key: string]: ReqType;
}

interface ReqType {
  res: string;
}

interface CustomHandlerInput {
  requestEnvelope: {
    request?: any;
  };
}

const parsedData: DataType = data;


const DefReqAPIHandler: RequestHandler = {
  canHandle(handlerInput: HandlerInput): boolean {
    const request: any = handlerInput.requestEnvelope.request;
    return request.type === "Dialog.API.Invoked" && request.apiRequest.name === "defReq";
  },
  handle(handlerInput: CustomHandlerInput): Response {
    const apiRequest: {
      [x: string]: any;
      apiRequest?: any;
    } = handlerInput.requestEnvelope.request.apiRequest;

    let req: string = resolveEntity(apiRequest.slots, "req");

    const resEntity: ReqType = {
      res: "",
    };

    if (req !== null) {
      const key = `${req}`;
      const databaseResponse = parsedData[key];

      console.log("Response from mock database ", databaseResponse);

      resEntity.res = databaseResponse.res;
    }

    const response = buildSuccessApiResponse(resEntity);
    return response;
  },
};


/******************************************************************************/
/**
 * ^^^^^^^^^^^^^^^^
 * Default Handlers
 * ,,,,,,,,,,,,,,,,
**/
const buildSuccessApiResponse = (returnEntity: ReqType) => {
  return { apiResponse: returnEntity };
};

const resolveEntity = function (
  resolvedEntity: { [x: string]: { resolutions: { resolutionsPerAuthority: any[] } } },
  slot: string
) {
  //This is built in functionality with SDK Using Alexa's ER
  let erAuthorityResolution = resolvedEntity[slot].resolutions.resolutionsPerAuthority[0];
  let value = null;

  if (erAuthorityResolution.status.code === "ER_SUCCESS_MATCH") {
    value = erAuthorityResolution.values[0].value.name;
  }

  return value;
};

const LaunchRequestHandler: RequestHandler = {
  canHandle(handlerInput: HandlerInput): boolean {
    const request = handlerInput.requestEnvelope.request;
    return request.type === "LaunchRequest";
  },
  handle(handlerInput: HandlerInput): Response {
    const speechText = "Welcome, you can say Hello or Help. Which would you like to try?";

    return handlerInput.responseBuilder
      .speak(speechText)
      .reprompt(speechText)
      .withSimpleCard("Welcome, you can say Hello or Help. Which would you like to try?", speechText)
      .getResponse();
  },
};

const HelpIntentHandler: RequestHandler = {
  canHandle(handlerInput: HandlerInput): boolean {
    const request = handlerInput.requestEnvelope.request;
    return request.type === "IntentRequest" && request.intent.name === "AMAZON.HelpIntent";
  },
  handle(handlerInput: HandlerInput): Response {
    const speechText = "You can ask me the weather!";

    return handlerInput.responseBuilder
      .speak(speechText)
      .reprompt(speechText)
      .withSimpleCard("You can ask me the weather!", speechText)
      .getResponse();
  },
};

const CancelAndStopIntentHandler: RequestHandler = {
  canHandle(handlerInput: HandlerInput): boolean {
    const request = handlerInput.requestEnvelope.request;
    return (
      request.type === "IntentRequest" &&
      (request.intent.name === "AMAZON.CancelIntent" || request.intent.name === "AMAZON.StopIntent")
    );
  },
  handle(handlerInput: HandlerInput): Response {
    const speechText = "Goodbye!";

    return handlerInput.responseBuilder
      .speak(speechText)
      .withSimpleCard("Goodbye!", speechText)
      .withShouldEndSession(true)
      .getResponse();
  },
};

const SessionEndedRequestHandler: RequestHandler = {
  canHandle(handlerInput: HandlerInput): boolean {
    const request = handlerInput.requestEnvelope.request;
    return request.type === "SessionEndedRequest";
  },
  handle(handlerInput: HandlerInput): Response {
    console.log(
      `Session ended with reason: ${(handlerInput.requestEnvelope.request as SessionEndedRequest).reason
      }`
    );

    return handlerInput.responseBuilder.getResponse();
  },
};

const ErrorHandler: ErrorHandler = {
  canHandle(): boolean {
    return true;
  },
  handle(handlerInput: HandlerInput, error: Error): Response {
    console.log(`Error handled: ${error.message}`);

    return handlerInput.responseBuilder
      .speak("Sorry, I don't understand your command. Please say it again.")
      .reprompt("Sorry, I don't understand your command. Please say it again.")
      .getResponse();
  },
};

let skill: Skill;

// handler?
// exports.handler = async (event: RequestEnvelope, context: unknown) => {
//   console.log(`REQUEST++++${JSON.stringify(event)}`);
//   if (!skill) {
//     skill = SkillBuilders.custom()
//       .addRequestHandlers(
//         LaunchRequestHandler,
//         DefReqAPIHandler,
//         HelpIntentHandler,
//         CancelAndStopIntentHandler,
//         SessionEndedRequestHandler
//       )
//       .addErrorHandlers(ErrorHandler)
//       .create();
//   }

//   const response = await skill.invoke(event, context);
//   console.log(`RESPONSE++++${JSON.stringify(response)}`);

//   return response;
// };

const app = express();
skill = SkillBuilders.custom()
  .addRequestHandlers(
    LaunchRequestHandler,
    DefReqAPIHandler,
    HelpIntentHandler,
    CancelAndStopIntentHandler,
    SessionEndedRequestHandler
  )
  .addErrorHandlers(ErrorHandler)
  .create();
const adapter = new ExpressAdapter(skill, true, true);

app.get("/", (req, res) => {
  console.log(JSON.stringify(req.headers))
  res.send("Hello world!");
});

// app.post("/", (req, res) => {
//   console.log(JSON.stringify(req.headers))
//   res.send("Hello world!");
// });

app.post("/", adapter.getRequestHandlers());
app.listen(process.env.PORT || 8080);


// {"host":"cloud-test.app.cloud.gov","user-agent":"PostmanRuntime/7.33.0","content-length":"0","accept":"*/*","accept-encoding":"gzip, deflate, br","b3":"4cdecaaf4782421b41d702be89b6cc58-41d702be89b6cc58","cache-control":"no-cache","postman-token":"23254bc9-b127-4617-9628-e83a31ab2ad6","x-amzn-trace-id":"Root=1-6508b7db-3fc2ae747a76b51164c9e213","x-b3-spanid":"41d702be89b6cc58","x-b3-traceid":"4cdecaaf4782421b41d702be89b6cc58","x-cf-applicationid":"1b1c3a09-c5b2-491b-a4a2-a35155f547ac","x-cf-instanceid":"bedba983-eab6-4626-7bfe-7054","x-cf-instanceindex":"0","x-forwarded-for":"54.86.50.139, 127.0.0.1","x-forwarded-port":"443","x-forwarded-proto":"https","x-request-start":"1695070172543","x-vcap-request-id":"4cdecaaf-4782-421b-41d7-02be89b6cc58"}