export const HTML_EL = document.documentElement;

export function setVhVar(window_el) {
  const vh = window.innerHeight * 0.01;
  if (window_el) window_el.style.setProperty('--vh', `${vh}px`);
}

export function setVwVar(window_el) {
    const vw = window.innerWidth * 0.01;
    if (window_el) window_el.style.setProperty('--vw', `${vw}px`);
}

