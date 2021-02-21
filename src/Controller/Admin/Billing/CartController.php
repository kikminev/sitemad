<?php

namespace App\Controller\Admin\Billing;

use App\Billing\Cart;
use App\Entity\Product;
use App\Entity\Subscription;
use App\Repository\CurrencyRepository;
use App\Repository\ProductRepository;
use Exception;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CartController extends AbstractController
{
    private $session;
    private Cart $cart;

    public function __construct(SessionInterface $session, Cart $cart)
    {
        $this->session = $session;
        $this->cart = $cart;
    }

    public function view(CurrencyRepository $currencyRepository, ProductRepository $productRepository)
    {
        /** @var Cart $cart */
        $cart = $this->session->get('cart');
        if(null == $cart) {
            $cart = new Cart();
        }

        $subscriptions = $cart->getSubscriptions();
        $productsBySubscriptionId = [];
        if(null != $subscriptions) {
            foreach ($subscriptions as $subscriptionId => $productId) {
                $productsBySubscriptionId[$subscriptionId] = $productRepository->find($productId);
            }
        }

        return $this->render(
            'Admin/Billing/cart_view.html.twig',
            [
                'cartCurrency' => $cart->getCurrency()->getSystemCode(),
                'subscriptions' => $subscriptions,
                'productsBySubscriptionId' => $productsBySubscriptionId,
                'taxAmount' => $cart->getTaxes(),
                'totalAmountWithTaxes' => $cart->getTotalWithTaxes()
            ]
        );
    }

    public function success()
    {
        return $this->render(
            'Admin/Billing/success.html.twig'
        );
    }


    public function cancelled()
    {
        return $this->render(
            'Admin/Billing/cancelled.html.twig'
        );
    }

    public function addSubscription(Product $product, Subscription $subscription, CurrencyRepository $currencyRepository)
    {
        /** @var Cart $cart */
        $cart = $this->session->get('cart');
        if(null == $cart) {
            $currency = $currencyRepository->findOneBy(['systemCode'=> 'GBP']);
            $cart = new Cart();
            $cart->setCurrency($currency);
        }
        $cart->attachSubscriptionToNewProduct($subscription, $product);

        $this->session->set('cart', $cart);

        return $this->redirectToRoute('admin_billing_cart_view');
    }

    public function checkout(Request $request): JsonResponse
    {
        $stripeSecretKey = $this->getParameter('stripe_secret_key');
        Stripe::setApiKey($stripeSecretKey);

        $priceId = $request->get('priceId');
        $defaultUKTaxRate = 'txr_1IMbPYEhLqLirlZwOSYtNkHI';

        try {
            $checkout_session = \Stripe\Checkout\Session::create([
                'success_url' => $this->generateUrl('admin_billing_cart_success', ['session_id' => '{CHECKOUT_SESSION_ID}'], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('admin_billing_cart_cancelled', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'payment_method_types' => ['card'],
                'mode' => 'subscription',

                'line_items' => [[
                    'price' => $priceId,
                    'quantity' => 1,
                    'tax_rates' => [$defaultUKTaxRate],
                ]],
            ]);
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => [
                    'message' => $e->getMessage(),
                ],
            ], 400);
        }

        return new JsonResponse(['sessionId' => $checkout_session['id']]);
    }

    public function stripeWebhookEndpoint()
    {
//        $stripeSecretKey = $this->getParameter('stripe_secret_key');
//        \Stripe\Stripe::setApiKey(
//            $stripeSecretKey
//        );
//
//        $app->post('/webhook', function(Request $request, Response $response) {
//            $logger = $this->get('logger');
//            $event = $request->getParsedBody();
//            // Parse the message body and check the signature
//            $webhookSecret = {{'STRIPE_WEBHOOK_SECRET'}};
//    if ($webhookSecret) {
//        try {
//            $event = \Stripe\Webhook::constructEvent(
//                $request->getBody(),
//                $request->getHeaderLine('stripe-signature'),
//                $webhookSecret
//            );
//        } catch (\Exception $e) {
//            return $response->withJson([ 'error' => $e->getMessage() ])->withStatus(403);
//        }
//    } else {
//        $event = $request->getParsedBody();
//    }
//    $type = $event['type'];
//    $object = $event['data']['object'];

//    switch ($type) {
//        case 'checkout.session.completed':
//            // Payment is successful and the subscription is created.
//            // You should provision the subscription.
//            break;
//        case 'invoice.paid':
//            // Continue to provision the subscription as payments continue to be made.
//            // Store the status in your database and check when a user accesses your service.
//            // This approach helps you avoid hitting rate limits.
//            break;
//        case 'invoice.payment_failed':
//            // The payment failed or the customer does not have a valid payment method.
//            // The subscription becomes past_due. Notify your customer and send them to the
//            // customer portal to update their payment information.
//            break;
//        // ... handle other event types
//        default:
//            // Unhandled event type
//    }
//
//    return $response->withJson([ 'status' => 'success' ])->withStatus(200);
//});
    }
}
