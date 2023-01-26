/*!
 * Stancer PrestaShop v1.0.0
 * (c) 2023 Iliad 78
 * Released under the MIT License.
 */
document.addEventListener("DOMContentLoaded",()=>{"use strict";const confirm=document.querySelector(".js-stancer-confirm-terms"),frame=document.querySelector(".js-stancer-payment-iframe"),terms=document.getElementById("conditions_to_approve[terms-and-conditions]");if(frame&&terms){const parent=frame.parentNode,observer=new IntersectionObserver(entries=>{entries.forEach(entry=>{0<entry.intersectionRatio&&(terms.checked?(entry.target.setAttribute("src",entry.target.dataset.target),confirm.parentNode&&parent.removeChild(confirm),observer.disconnect()):frame.parentNode&&parent.removeChild(frame))})});observer.observe(frame);var action=()=>{terms.checked?(parent.appendChild(frame),confirm.parentNode&&parent.removeChild(confirm)):(parent.appendChild(confirm),frame.parentNode&&parent.removeChild(frame))};terms.addEventListener("change",action),action()}});