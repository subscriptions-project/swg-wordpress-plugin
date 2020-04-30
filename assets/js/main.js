import { handleSignInClicks, handleSubscribeClicks, handleContributeClicks } from "./buttons";
import { handlePaymentResponse } from "./payments";
import { unlockPageMaybe } from "./unlock";


// Wait for SwG API to become available.
(self.SWG = self.SWG || []).push(async (subscriptions) => {
  // Handle payment response.
  handlePaymentResponse(subscriptions);

  // Handle button clicks.
  handleSignInClicks();
  handleSubscribeClicks(subscriptions);
  handleContributeClicks(subscriptions);

  // Unlock page if possible.
  unlockPageMaybe();
});
