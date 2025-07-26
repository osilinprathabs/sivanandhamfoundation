const express = require('express');
const Stripe = require('stripe');
const cors = require('cors');

const app = express();
const stripe = Stripe('sk_test_YOUR_STRIPE_SECRET_KEY'); // Replace with your Stripe test secret key

app.use(cors());
app.use(express.json());

app.post('/create-checkout-session', async (req, res) => {
    const { amount, name, email } = req.body;

    try {
        const session = await stripe.checkout.sessions.create({
            payment_method_types: ['card'],
            line_items: [
                {
                    price_data: {
                        currency: 'inr',
                        product_data: {
                            name: 'Donation to Sri Swarna Vaarahi Trust',
                            description: `Donation by ${name}`,
                        },
                        unit_amount: amount * 100, // Amount in paise (INR)
                    },
                    quantity: 1,
                },
            ],
            mode: 'payment',
            success_url: 'http://localhost:8080/donation.html?payment=success', // Update for production
            cancel_url: 'http://localhost:8080/donation.html?payment=cancel', // Update for production
            customer_email: email,
        });

        res.json({ id: session.id });
    } catch (error) {
        console.error('Error creating checkout session:', error);
        res.status(500).json({ error: 'Failed to create checkout session.' });
    }
});

app.listen(3000, () => {
    console.log('Server running on http://localhost:3000');
});