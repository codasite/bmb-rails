import { Elements } from '@stripe/react-stripe-js'
import { Modal } from '../../../modals/Modal'
import { CancelButton } from '../../../modals/ModalButtons'
import { ModalHeader } from '../../../modals/ModalHeader'
import { loadStripe, StripeElementsOptions } from '@stripe/stripe-js'
import StripePaymentForm from './StripePaymentForm'
import { getAppObj, wpbbAppObj } from '../../../wpbbAppObj'
import { camelCaseKeys } from '../../shared/api/bracketApi'

const appObj = getAppObj()

const stripePromise = loadStripe(appObj.stripePublishableKey)

interface StripePaymentModalProps {
  title: string
  show: boolean
  setShow: (show: boolean) => void
  clientSecret: string
  paymentAmount: number
  myPlayHistoryUrl: string
}

export default function StripePaymentModal(props: StripePaymentModalProps) {
  const { title, show, setShow, clientSecret } = props

  const appearance: StripeElementsOptions['appearance'] = {
    theme: 'night',
  }
  const stripeOptions = {
    clientSecret,
    appearance,
  }

  return (
    <>
      {clientSecret && (
        <Elements stripe={stripePromise} options={stripeOptions}>
          <Modal show={show} setShow={setShow}>
            <ModalHeader text={title} />
            <div className="tw-flex tw-flex-col tw-gap-10">
              <StripePaymentForm
                myPlayHistoryUrl={props.myPlayHistoryUrl}
                paymentAmount={props.paymentAmount}
              />
              <CancelButton onClick={() => setShow(false)} />
            </div>
          </Modal>
        </Elements>
      )}
    </>
  )
}
