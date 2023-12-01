import React, { useContext } from 'react'
import { defaultBracketConstants } from '../../../constants'
import { MatchBoxChildProps } from '../../types'
//@ts-ignore
import { BaseTeamSlot } from '../../TeamSlot'
import { BracketMetaContext } from '../../../context/context'

interface WinnerContainerProps extends MatchBoxChildProps {
  topText?: string
  topTextFontSize?: number
  topTextColor?: string
  topTextColorDark?: string
  gap?: number
}

export const WinnerContainer = (props: WinnerContainerProps) => {
  const {
    match,
    matchTree,
    TeamSlotComponent = BaseTeamSlot,
    topText,
    topTextFontSize = 48,
    topTextColor = 'dd-blue',
    topTextColorDark = 'white',
    gap = 24,
  } = props

  return (
    <div className={`tw-flex tw-flex-col tw-gap-${gap} tw-items-center`}>
      {topText && (
        <span
          className={`tw-text-48 sm:tw-text-${topTextFontSize} tw-text-${topTextColor} dark:tw-text-${topTextColorDark} tw-font-700 tw-max-w-[700px] tw-text-center tw-leading-none`}
        >
          {topText}
        </span>
      )}
      <TeamSlotComponent
        match={match}
        matchTree={matchTree}
        team={match.getWinner()}
        teamPosition={'winner'}
        height={52}
        width={297}
        getFontSize={() => 32}
        fontWeight={700}
      />
    </div>
  )
}
