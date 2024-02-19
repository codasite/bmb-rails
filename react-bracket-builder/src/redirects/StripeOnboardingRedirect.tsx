import { useEffect, useState } from 'react'
import { bracketApi } from '../brackets/shared'

const StripeOnboardingRedirect = (props) => {
  const [displayText, setDisplayText] = useState('Please wait...')
  const fetchStripeOnboardingLink = async () => {
    const res = await bracketApi.getStripeOnboardingLink()

    if (!res.url) {
      setDisplayText('Error fetching onboarding link.')
      throw new Error('No onboarding link found')
    }
    window.location.href = res.url
  }
  useEffect(() => {
    fetchStripeOnboardingLink()
  })

  return (
    <div className="wpbb-reset tw-bg-dd-blue tw-min-h-screen">
      <div className="tw-flex tw-flex-col">
        <div className="tw-flex tw-flex-col md:tw-flex-row-reverse tw-py-60 tw-gap-15 tw-items-center md:tw-justify-between tw-max-w-screen-lg tw-m-auto tw-px-20 lg:tw-px-0">
          <h1 className="tw-text-24 sm:tw-text-36">{displayText}</h1>
        </div>
      </div>
    </div>
  )
}

export default StripeOnboardingRedirect
