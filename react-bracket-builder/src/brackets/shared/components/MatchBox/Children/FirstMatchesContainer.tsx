import React from 'react'
import { MatchBoxProps, MatchBoxChildProps } from '../../types'
import { MatchNode } from '../../../models/operations/MatchNode'
import { MatchTree } from '../../../models/MatchTree'
import { defaultBracketConstants } from '../../../constants'

interface FirstMatchesContainerProps {
  matches: MatchNode[]
  matchTree: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  MatchBoxComponent?: React.FC<MatchBoxProps>
  TeamSlotComponent?: React.FC<any>
  MatchBoxChildComponent?: React.FC<MatchBoxChildProps>
  onTeamClick?: (match: MatchNode, position: string) => void
}

export const FirstMatchesContainer: React.FC<FirstMatchesContainerProps> = ({
  matches,
  matchTree,
  setMatchTree,
  MatchBoxComponent,
  TeamSlotComponent,
  MatchBoxChildComponent,
  onTeamClick,
}) => {
  if (!matches.length || !MatchBoxComponent) {
    return null
  }

  const numRounds = matchTree.rounds.length
  const teamHeight = defaultBracketConstants.teamHeights[numRounds]
  const teamWidth = defaultBracketConstants.teamWidths[numRounds]
  const teamGap = defaultBracketConstants.teamGaps[0]
  const matchGap = defaultBracketConstants.firstRoundsMatchGaps[numRounds]

  return (
    <div className="tw-flex tw-flex-row tw-gap-8 tw-justify-center tw-items-center">
      {matches.map((match, index) => (
        <MatchBoxComponent
          key={`first-match-${index}`}
          match={match}
          matchPosition="first"
          matchTree={matchTree}
          setMatchTree={setMatchTree}
          TeamSlotComponent={TeamSlotComponent}
          MatchBoxChildComponent={MatchBoxChildComponent}
          onTeamClick={onTeamClick}
          teamHeight={teamHeight}
          teamWidth={teamWidth}
          teamGap={teamGap}
        />
      ))}
    </div>
  )
}
