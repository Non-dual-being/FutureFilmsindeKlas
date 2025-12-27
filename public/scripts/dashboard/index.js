import flasher from "./../shared/showFlashMsg.js";
import {
    HTML_EL,
    setVhVar,
    setVWVar,
} from "./../shared/resize.js";

const {
    result: flashMsgResultId
} = window.flashTargetIds;

const PREFIX = window.flashPrefix;

const flashMsgResult = document.getElementById(`${PREFIX}${flashMsgResultId}`);

if (flashMsgResult) flasher(flashMsgResult, 7000);

if (HTML_EL) {
    setVWVar(HTML_EL);
    setVhVar(HTML_EL);

    ['resize', ]

    window.addEventListener('resize', () => {
        setVWVar(HTML_EL);
        setVhVar(HTML_EL);
    });

    window.addEventListener('orientationchange', () => {
        setVWVar(HTML_EL);
        setVhVar(HTML_EL);
    })

}