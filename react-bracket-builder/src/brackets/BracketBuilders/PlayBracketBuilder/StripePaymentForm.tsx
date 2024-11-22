import { PaymentElement, useElements, useStripe } from '@stripe/react-stripe-js'
import { Layout, LayoutObject } from '@stripe/stripe-js'
// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React, { useEffect, useState } from 'react'
import { ConfirmButton } from '../../../modals/ModalButtons'

export default function StripePaymentForm(props: {
  myPlayHistoryUrl: string
  paymentAmount: number
}) {
  const stripe = useStripe()
  const elements = useElements()

  const [message, setMessage] = useState(null)
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    if (!stripe) {
      return
    }

    const clientSecret = new URLSearchParams(window.location.search).get(
      'payment_intent_client_secret'
    )

    if (!clientSecret) {
      return
    }

    stripe.retrievePaymentIntent(clientSecret).then(({ paymentIntent }) => {
      switch (paymentIntent.status) {
        case 'succeeded':
          setMessage('Payment succeeded!')
          break
        case 'processing':
          setMessage('Your payment is processing.')
          break
        case 'requires_payment_method':
          setMessage('Your payment was not successful, please try again.')
          break
        default:
          setMessage('Something went wrong.')
          break
      }
    })
  }, [stripe])

  const handleSubmit = async (e) => {
    e.preventDefault()

    if (!stripe || !elements) {
      return
    }

    setLoading(true)

    const { error } = await stripe.confirmPayment({
      elements,
      confirmParams: {
        return_url: props.myPlayHistoryUrl,
      },
    })
    if (error.type === 'card_error' || error.type === 'validation_error') {
      setMessage(error.message)
    } else {
      setMessage('An unexpected error occurred.')
    }

    setLoading(false)
  }

  const paymentElementOptions: { layout: Layout | LayoutObject } = {
    layout: 'tabs',
  }

  return (
    <form className="tw-flex tw-flex-col tw-gap-32" onSubmit={handleSubmit}>
      <PaymentElement id="payment-element" options={paymentElementOptions} />
      {message && (
        <p className={'tw-text-red tw-font-sans tw-my-0'}>{message}</p>
      )}
      <ConfirmButton
        disabled={loading || !stripe || !elements}
        className="tw-gap-4"
      >
        <span>Pay</span>
        <span>${props.paymentAmount / 100}</span>
      </ConfirmButton>
    </form>
  )
}
