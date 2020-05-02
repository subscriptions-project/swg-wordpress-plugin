/**
 * Handles payment response event.
 * @param {*} subscriptions SwG API 
 */
export function handlePaymentResponse(subscriptions) {
  subscriptions.setOnPaymentResponse(paymentResponse => {
    paymentResponse.then(response => {
      // TODO: Handle payment response.
      response.complete().then(() => {
        // TODO: Update page accordingly.
        location.reload();
      });
    });
  });
}
