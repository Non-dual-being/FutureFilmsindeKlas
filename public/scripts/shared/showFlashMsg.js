import { CSSFLASH } from "./cssGlobals.js";

const ATTR = 'data-message-type';

const {
    flashShow,
    flashShowSuccess,
    flashShowError,
    flashHide
} = CSSFLASH;

const ADDED_CLASSES = [flashShow, flashShowSuccess, flashShowError];

function showFlashMessage(flashMsg, duration = 5000, fadeOut=true) {
    if (!flashMsg) return;
    let classType;

    const getClassType = (type) => {
        if (!type) return;
    
        switch (type) {
            case 'error':
                return flashShowError;
            case 'success':
                return flashShowSuccess;
            default:
                return '';   
        }        
    }

    if (flashMsg && flashMsg.textContent.trim() !== '') {
        flashMsg.classList.remove(flashHide);
        flashMsg.classList.add(flashShow);
        const flashATTR = flashMsg.getAttribute(ATTR);
        if (flashATTR) {
            classType = getClassType(flashATTR);
            flashMsg.classList.add(classType);
        }
    }

    flashMsg.scrollIntoView({behavior:"smooth", block: "center"});
    if (fadeOut){
        setTimeout(() => {
            for (const myClass of ADDED_CLASSES){
                if (flashMsg.classList.contains(myClass)){
                    flashMsg.classList.remove(myClass);
                }
            }
            flashMsg.textContent = '';
            flashMsg.classList.add(flashHide);
        }, duration)
        
    }
}


export default showFlashMessage;