import { baseButtonStyles } from '../../../../brackets/shared/components/ActionButtons'
import stripeLogo from '../../../../assets/images/stripe_logo_64.png'
import { useState } from 'react'
import { bracketApi } from '../../../../brackets/shared'
import { Spinner } from '../../../../brackets/shared/components/Spinner'
import { logger } from '../../../../utils/Logger'

export const SetUpPaymentsButton = (props: {}) => {
  const [processing, setProcessing] = useState(false)
  const handleSetUpPayments = async () => {
    setProcessing(true)
    try {
      const { url } = await bracketApi.getStripePaymentsLink()
      window.location.href = url
    } catch (error) {
      logger.error(error)
      setProcessing(false)
    }
  }
  const styles = [
    'tw-px-16',
    'tw-h-[46px]',
    'tw-rounded-8',
    'tw-font-700',
    'tw-text-16',
    'tw-gap-6',
    'tw-bg-blue/15',
    'tw-text-white',
    'tw-border',
    'tw-border-solid',
    'tw-border-blue',
    'tw-w-full',
    'tw-items-center',
    'hover:tw-text-white/75',
    'disabled:tw-bg-transparent',
    'disabled:tw-border-white/20',
    'disabled:tw-text-white/20',
  ]
  const buttonStyles = [...baseButtonStyles, ...styles]
  return (
    <button
      onClick={handleSetUpPayments}
      className={buttonStyles.join(' ')}
      disabled={processing}
    >
      {processing ? (
        <Spinner fill="white" height={24} width={24} />
      ) : (
        <>
          <span>Set Up Payments with</span>
          <img src={stripeLogo} alt="Stripe" className="tw-h-20" />
        </>
      )}
    </button>
  )
}
