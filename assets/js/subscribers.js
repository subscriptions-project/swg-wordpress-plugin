import { handleSubscribeButtonClicks, handleContributeButtonClicks } from "./buttons";
import { handlePaymentResponse } from "./payments";
import { getOwnedProducts } from "./products";
import { unlockContent } from "./unlock";


// Wait for SwG API to become available.
(self.SWG = self.SWG || []).push(async (subscriptions) => {
  // Handle payment response.
  handlePaymentResponse(subscriptions);

  // Handle button clicks.
  handleSubscribeButtonClicks(subscriptions);
  handleContributeButtonClicks(subscriptions);

  // Unlock content for owned products.
  await getOwnedProducts(subscriptions).then(unlockContent);
});
