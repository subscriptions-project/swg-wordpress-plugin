import { handleSignInClicks, handleSubscribeClicks, handleContributeClicks } from "./buttons";
import { handleRightClicks } from "./clicks";
import { handlePaymentResponse } from "./payments";
import { unlockPageMaybe } from "./unlock";


// Wait for SwG API to become available.
(self.SWG = self.SWG || []).push(async (swg) => {
  // Handle payment response.
  handlePaymentResponse(swg);

  // Handle button clicks.
  handleSignInClicks();
  handleSubscribeClicks(swg);
  handleContributeClicks(swg);
  handleRightClicks();

  // Unlock page if possible.
  return unlockPageMaybe(swg);
});
