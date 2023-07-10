import {
  getRequestType
} from 'ask-sdk-core';

interface CustomHandlerInput {
  requestEnvelope: {
    request: any,
    version: any,
    context: any,
  }
}

export const util = {
  // check the api request
  isApiRequest: (handlerInput: CustomHandlerInput, apiName: string) => {
    try {
      return (
        getRequestType(handlerInput.requestEnvelope) === "Dialog.API.Invoked" &&
        handlerInput.requestEnvelope.request.apiRequest.name === apiName
      );
    } catch (e) {
      console.log("Error occurred: ", e);
      return false;
    }
  },

  // return the slot resolution
  getApiSlot: function (handlerInput: CustomHandlerInput, slot: string) {
    const filledSlots = handlerInput.requestEnvelope.request.apiRequest.slots;
    let id = null;
    let heardAs = "";
    let resolved = "";
    let confirmationStatus = "";
    let erStatus = "";

    if (filledSlots[slot]) {
      if (filledSlots[slot].resolutions?.resolutionsPerAuthority[0]?.status?.code) {
        switch (filledSlots[slot].resolutions.resolutionsPerAuthority[0].status.code) {
          case "ER_SUCCESS_MATCH":
            id = filledSlots[slot].resolutions.resolutionsPerAuthority[0].values[0].value.id;
            heardAs = filledSlots[slot].value;
            resolved = filledSlots[slot].resolutions.resolutionsPerAuthority[0].values[0].value.name;
            confirmationStatus = filledSlots[slot].confirmationStatus;
            erStatus = "ER_SUCCESS_MATCH";
            break;
          case "ER_SUCCESS_NO_MATCH":
            heardAs = filledSlots[slot].value;
            confirmationStatus = filledSlots[slot].confirmationStatus;
            erStatus = "ER_SUCCESS_NO_MATCH";
            break;
          default:
            heardAs = filledSlots[slot].value;
            confirmationStatus = filledSlots[slot].confirmationStatus;
            break;
        }
      } else {
        heardAs = filledSlots[slot].value;
        resolved = filledSlots[slot].value;
        confirmationStatus = filledSlots[slot].confirmationStatus;
      }
    }
    return {
      id: null,
      heardAs: heardAs,
      resolved: resolved,
      confirmationStatus: confirmationStatus,
      erStatus: erStatus,
    };
  },

  // return resolved value, if not, return filled slot value
  getApiSlotBestValue: function (handlerInput: CustomHandlerInput, slot: string) {
    const slotValues = module.exports.getApiSlot(handlerInput, slot);
    if (slotValues.resolved === "") {
      return slotValues.heardAs;
    } else {
      return slotValues.resolved;
    }
  },

  // return a string with capitalized first letters
  capitalizeFirstLetter: function (str: string) {
    if (typeof str !== "string") {
      return "";
    } else {
      return str
        .toLowerCase()
        .split(" ")
        .map((word) => {
          return word.charAt(0).toUpperCase() + word.slice(1);
        })
        .join(" ");
    }
  },
}