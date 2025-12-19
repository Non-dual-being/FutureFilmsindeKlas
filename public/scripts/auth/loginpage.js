import flasher from "./showFlashMsg.js";

const {
    inactive : flashMsgInActiveId,
    inlog_Submit: flashMsgInlogSubmitId,
    general: flashMsgGeneralId
} = window.flashTargetIds;

const PREFIX = window.flashPrefix;

const flashMsgInActive = document.getElementById(`${PREFIX}${flashMsgInActiveId}`);
const flashMsgInlogAttempt = document.getElementById(`${PREFIX}${flashMsgInlogSubmitId}`);
const flashMsgGeneral = document.getElementById(`${PREFIX}${flashMsgGeneralId}`);

if (flashMsgInActive) flasher(flashMsgInActive, 7000);
if (flashMsgInlogAttempt) flasher(flashMsgInlogAttempt, 7000);
if (flashMsgGeneral) flasher(flashMsgInlogAttempt, 7000, false);