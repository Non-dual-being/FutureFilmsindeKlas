import flasher from "./../shared/showFlashMsg.js";

const {
    result: flashMsgResultId
} = window.flashTargetIds;

const PREFIX = window.flashPrefix;


const flashMsgResult = document.getElementById(`${PREFIX}${flashMsgResultId}`);



if (flashMsgResult) flasher(flashMsgResult, 7000);
