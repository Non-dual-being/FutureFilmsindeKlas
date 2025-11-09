const THEME = 'light';
const htmEL = document.documentElement;

if (htmEL){
    if  (htmEL.getAttribute('future-theme') !== THEME){
        DOCUMENT_EL.setAttribute('future-theme', THEME);
    }
}


function setVhVar() {
  const vh = window.innerHeight * 0.01;
  if (htmEL)
    htmEL.style.setProperty('--vh', `${vh}px`);
}

setVhVar();
window.addEventListener('resize', setVhVar);
window.addEventListener('orientationchange', setVhVar);

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

const CSSVARS = {
    ...CSSUTILS,
    ...CSSSTATES
}


export default CSSVARS;