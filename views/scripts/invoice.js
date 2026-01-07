document.addEventListener('DOMContentLoaded',(event)=>{
  const base_url = document.body.dataset.baseUrl;
const refundForm = document.body.querySelector('#stancer_refund_form');
const captureForm=document.body.querySelector('#stancer_capture_form');
const refundButton = refundForm.querySelector('#refund_stancer_refund');
const refundPriceInput = refundForm.querySelector('#refund_stancer_refund_amount_amount');
refundPriceInput.value = refundButton.getAttribute('data_amount');
// const refund = (event) => {
//   console.log('coucou');

//   const token= document.body.dataset.token;
//   const url = document.location.origin + base_url + '/modules/stancer/test?_token=' + token;

//   const data = {
//     price: refundPriceInput.value,
//     paymentId: refundButton.getAttribute('data_payment_id')
//   };
//   console.log(url);
//   router= new document.prestashop.component.router()
//   fetch(generate(

//   )
//     url,
//     {
//       method:'POST',
//       body: FormData(data),
//     }
//   ).then((response)=>{
//     console.log(response);
//   })
// }

refundButton.addEventListener('click', console.log('cliquez'));
refundButton.addEventListener('click', ()=>console.log('addlistener'));
})

