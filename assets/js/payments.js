/**
 * Handles payment response event.
 * @param {*} swg SwG API 
 */
export function handlePaymentResponse(swg) {
  swg.setOnPaymentResponse(paymentResponse => {
    paymentResponse.then(response => {
      // TODO: Handle payment response.
      response.complete().then(() => {
        // TODO: Update page accordingly.
        location.reload();
      });
    });
  });
}
