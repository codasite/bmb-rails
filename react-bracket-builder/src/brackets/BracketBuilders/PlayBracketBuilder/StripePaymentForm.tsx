import { PaymentElement, useElements, useStripe } from '@stripe/react-stripe-js'
import { Layout, LayoutObject } from '@stripe/stripe-js'
import React, { useEffect, useState } from 'react'
import { ConfirmButton } from '../../../modals/ModalButtons'

export default function StripePaymentForm() {
  const stripe = useStripe()
  const elements = useElements()

  const [message, setMessage] = useState(null)
  const [loading, setLoading] = useState(false)

  const handleSubmit = async (e) => {
    e.preventDefault()

    if (!stripe || !elements) {
      return
    }

    setLoading(true)

    const { error } = await stripe.confirmPayment({
      elements,
      confirmParams: {
        return_url: 'http://localhost:8008/dashboard',
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
      <ConfirmButton disabled={loading || !stripe || !elements}>
        <span>Pay Now</span>
      </ConfirmButton>
    </form>
  )
}
