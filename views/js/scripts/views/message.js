/*!
 * Stancer PrestaShop v1.0.0
 * (c) 2023 Iliad 78
 * Released under the MIT License.
 */
window.addEventListener("message",function(event){"use strict";var props;"undefined"!=typeof STANCER&&event.origin===STANCER.origin&&(props={},event.data.width&&(props.width=event.data.width),event.data.height&&320<event.data.height&&(props.height=event.data.height),props&&$(".js-stancer-payment-iframe").animate(props,{duration:200,easing:"linear",queue:!1}),"secure-auth-start"===event.data.status)&&void 0!==event.data.url&&(window.location.href=event.data.url)});