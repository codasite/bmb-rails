import React from 'react'
//@ts-ignore
import { ReactComponent as BracketLogo } from '../../../assets/BMB-ICON-CURRENT.svg'

interface LogoContainerProps {
  topText?: string
  topTextColor?: string
  topTextColorDark?: string
  topTextFontSize?: number
  bottomText?: string
  bottomTextColor?: string
  bottomTextColorDark?: string
  bottomTextFontSize?: number
  logoColor?: string
  logoColorDark?: string
}

export const LogoContainer = (props: LogoContainerProps) => {
  const {
    topText = 'Who You Got?',
    topTextColor = 'dd-blue',
    topTextColorDark = 'white',
    topTextFontSize = 36,
    bottomText = '',
    bottomTextColor = 'dd-blue',
    bottomTextColorDark = 'white',
    bottomTextFontSize = 36,
    logoColor = 'black/25',
    logoColorDark = 'white',
  } = props

  return (
    <div
      className={`tw-flex tw-flex-col tw-gap-20 tw-justify-between tw-items-center tw-font-700 tw-whitespace-nowrap `}
    >
      <span
        className={`tw-text-${topTextFontSize} tw-text-${topTextColor} dark:tw-text-${topTextColorDark}`}
      >
        {topText}
      </span>
      <BracketLogo
        className={`tw-w-[124px] tw-text-${logoColor} dark:tw-text-${logoColorDark}`}
      />
      <span
        className={`tw-min-h-[${bottomTextFontSize}px] tw-text-${bottomTextFontSize} tw-text-${bottomTextColor} dark:tw-text-${bottomTextColorDark}`}
      >
        {bottomText}
      </span>
    </div>
  )
}
