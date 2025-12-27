const THEME = 'light';
const HTML_EL = document.documentElement;

if (HTML_EL){
    if  (HTML_EL.getAttribute('future-theme') !== THEME){
        HTML_EL.setAttribute('future-theme', THEME);
    }

}


/**light modus afdwingne */

export const CSSUTILS = {
    'displayNone' : 'd-none',
    'displayFlex' : 'd-flex',
    'extra' : 'extra'
}

export const CSSSTATES = {
    'loaderHidden' : 'loading-screen--hidden',
    'checkboxSomeChecked' : 'checkbox-some-checked',
    'checkboxAllChecked' : 'checkbox-all-checked',
    'checkboxNoneChecked' : 'checkbox-none-checked',
}

export const CSSFLASH = {
    'flashShow'     : 'flash-Show',
    'flashHide'     : 'flash-Hide',
    'flashShowSuccess'   : 'flash-show--Success',
    'flashShowError'    : 'flash-show--Error',
    'flashShowSuccessSubtle'   : 'flash-show--Success-subtle',
    'flashShowErrorSubtle'    : 'flash-show--Error-subtle'
}

const CSSVARS = {
    ...CSSUTILS,
    ...CSSSTATES,
    ...CSSFLASH
}


export default CSSVARS;