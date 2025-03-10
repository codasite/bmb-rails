// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
import { MatchBoxChildProps, TitleComponentProps } from '../../types'
//@ts-ignore
import { BaseTeamSlot } from '../../TeamSlot'
import { ReadonlyTitleComponent } from './ReadonlyTitleComponent'
import { defaultBracketConstants } from '../../../constants'

interface WinnerContainerProps extends MatchBoxChildProps {
  title?: string
  setTitle?: (title: string) => void
  titleFontSize?: number
  titleColor?: string
  titleColorDark?: string
  gap?: number
  TitleComponent?: React.FC<TitleComponentProps>
}

export const WinnerContainer = (props: WinnerContainerProps) => {
  const {
    match,
    matchTree,
    TeamSlotComponent = BaseTeamSlot,
    TitleComponent = ReadonlyTitleComponent,
    title,
    setTitle,
    titleFontSize = 48,
    titleColor = 'dd-blue',
    titleColorDark = 'white',
    gap = 24,
  } = props
  const numRounds = matchTree.rounds.length

  return (
    <div className={`tw-flex tw-flex-col tw-items-center`} style={{ gap: gap }}>
      {title && (
        <TitleComponent
          fontSize={titleFontSize}
          color={titleColor}
          colorDark={titleColorDark}
          width={defaultBracketConstants.winnerContainerTextMaxWidth[numRounds]}
          title={title}
          setTitle={setTitle}
        />
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
