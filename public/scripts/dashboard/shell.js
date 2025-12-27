/** 
 * The Listener on the full document is due to many events that lead to the same action
 * For Example: press esc, press outside sidebar, is both close sidebar
 * Because the dashboard works in partials, this is the best way to keep functionality even when doms changes
*/

/**
 * closest bublle up till it founds a ellement wit the given prop
 * its starts with the target event, the element clicked upon
 * 
 */

function setSideBareOpen(open) {
  const shell = document.getElementById("dash-body-container");
  if (!shell) return;

  shell.setAttribute('data-sidebar-open', open ? "true" : "false");
  document.body.classList.toggle("is-scroll-locked", open);

  /**
   * second argument of toggle forces the toggle to add or remove 
   * When true is will be add, when false it will be removed
   * So side bare opem then overflow hidden will be added else it wil be removed
   * 
   */

  const burger = document.querySelector('.dash-burger');
  burger?.setAttribute("aria-expanded", open ? "true" : "false");


}


document.addEventListener("click", (e) => {
  const actionEl = e.target.closest('[data-action="toggle-sidebar"]');
  if (!actionEl) return;

  /**
   * backdrop switch from none to block if sidebar open
   * click on backdrop el triggers a closed sidebar
   */

  const shell = document.getElementById("dash-body-container");
  if (!shell) return;

  const open = shell?.getAttribute("data-sidebar-open") === "true";
  setSideBareOpen(!open);
 
  /**de klik moet het togglen dus omdraaien */
 
});

document.addEventListener("keydown", (e) => {
  if (e.key !== "Escape") return;

  setSideBareOpen(false);
});