import {
    HTML_EL,
    setVhVar,
    setVwVar
} from "./resize-vars.js";

if (HTML_EL) {
    let resizeTimeout
    const updateViewVars = () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            setVhVar(HTML_EL);
            setVwVar(HTML_EL);
            

        }, 100);
    };

    updateViewVars();

    ['resize', 'orientationchange'].forEach(evt => {
        window.addEventListener(evt, updateViewVars);
    });
};